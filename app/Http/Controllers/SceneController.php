<?php

namespace App\Http\Controllers;
use App\Models\Scene;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Character;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Services\ComfyVideoService;

class SceneController extends Controller
{
    public function index(Request $request)
    {
        $projects = Auth::user()->projects()->orderBy('title')->get();
        $selectedProject = $request->filled('project_id')
            ? $projects->firstWhere('id', (int) $request->query('project_id'))
            : $projects->sortByDesc('updated_at')->first();

        $scenes = Scene::with('project')
            ->with('characters')
            ->withCount('videoClips')
            ->whereHas('project', fn ($query) => $query->where('user_id', Auth::id()))
            ->when($selectedProject, fn ($query) => $query->where('project_id', $selectedProject->id))
            ->orderBy('order_index')
            ->paginate(12)
            ->withQueryString();

        return view('scenes.index', compact('scenes', 'projects', 'selectedProject'));
    }

    public function create(Request $request)
    {
        $projects = Auth::user()->projects()->latest()->get();
        $selectedProjectId = (int) ($request->query('project') ?: $projects->first()?->id);
        $projectIds = $projects->pluck('id');
        $characters = Character::where(function ($query) use ($projectIds) {
            $query->whereIn('project_id', $projectIds)->orWhereNull('project_id');
        })->orderBy('name')->get();
        
        return view('scenes.create', compact('projects', 'characters', 'selectedProjectId'));
    }

    public function show(Scene $scene)
    {
        $this->ensureSceneOwnership($scene);

        return redirect()->route('projects.videoeditor', [
            'project' => $scene->project_id,
            'active_scene' => $scene->id,
        ]);
    }

    /**
     * Trigger the AI generation process (Regenerate Seed).
     */
    public function render(Scene $scene, ComfyVideoService $comfy)
    {
        $this->ensureSceneOwnership($scene);

        try {
            $jobId = $comfy->submitScene($scene);
            $scene->update(['status' => 'Processing', 'generation_job_id' => $jobId, 'generation_error' => null]);
            return back()->with('success', 'Local Wan/ComfyUI generation queued. Use Check Render Status to import the finished video.');
        } catch (\Throwable $e) {
            $scene->update(['status' => 'failed', 'generation_error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function syncRender(Scene $scene, ComfyVideoService $comfy)
    {
        $this->ensureSceneOwnership($scene);
        if (!$scene->generation_job_id) return back()->with('error', 'This scene has no local generation job.');

        try {
            $path = $comfy->downloadCompletedVideo($scene->generation_job_id);
            if (!$path) return back()->with('error', 'The local video is still generating. Try again shortly.');

            if ($scene->video_path && Storage::disk('public')->exists($scene->video_path)) {
                Storage::disk('public')->delete($scene->video_path);
            }
            $scene->update(['video_path' => $path, 'status' => 'Ready', 'generation_error' => null]);
            return back()->with('success', 'Generated cutscene imported into the timeline.');
        } catch (\Throwable $e) {
            $scene->update(['status' => 'failed', 'generation_error' => $e->getMessage()]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function attachVideo(Request $request, Scene $scene)
    {
        $validated = $request->validate([
            'video_file' => 'required|file|mimes:mp4,mov,webm,m4v|max:512000',
        ]);

        if ($scene->video_path && Storage::disk('public')->exists($scene->video_path)) {
            Storage::disk('public')->delete($scene->video_path);
        }

        $path = $validated['video_file']->store('scenes', 'public');

        $scene->update([
            'video_path' => $path,
            'status' => 'Ready',
        ]);

        return redirect()
            ->route('projects.videoeditor', [
                'project' => $scene->project_id,
                'active_scene' => $scene->id,
            ])
            ->with('success', 'Video attached to sequence.');
    }
    /**
     * Show the form for editing the scene script and sequence.
     */
    public function edit(Scene $scene)
    {
        $this->ensureSceneOwnership($scene);
        $projects = Auth::user()->projects()->latest()->get();
        $characters = Character::where('project_id', $scene->project_id)->orderBy('name')->get();
        
        // Load existing character IDs so we can check the boxes in the UI
        $selectedCharacters = $scene->characters->pluck('id')->toArray();

        return view('scenes.edit', compact('scene', 'projects', 'characters', 'selectedCharacters'));
    }

    /**
     * Update the scene in storage.
     */
    public function update(Request $request, Scene $scene, ComfyVideoService $comfy)
    {
        $this->ensureSceneOwnership($scene);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'order_index' => 'required|integer|min:1',
            'script_segment' => 'required|string',
            'status' => 'required|string',
            'characters' => 'array|nullable',
            'characters.*' => 'exists:characters,id'
        ]);

        // Update the main scene details
        $scene->update($validated);

        // Sync the characters (this adds new ones and removes unselected ones)
        if ($request->boolean('preserve_characters')) {
            // The video editor updates scene metadata without showing cast controls.
        } elseif ($request->has('characters')) {
            $scene->characters()->sync($validated['characters']);
        } else {
            $scene->characters()->detach();
        }

        if ($request->boolean('regenerate_video')) {
            try {
                $scene->refresh()->load('characters');
                $jobId = $comfy->submitScene($scene);
                $scene->update([
                    'status' => 'Processing',
                    'generation_job_id' => $jobId,
                    'generation_error' => null,
                ]);

                return redirect()
                    ->route('projects.timeline', $scene->project_id)
                    ->with('success', 'Updated scene saved and replacement video queued using the new script and cast.');
            } catch (\Throwable $e) {
                $scene->update(['status' => 'failed', 'generation_error' => $e->getMessage()]);

                return back()->withInput()->with('error', $e->getMessage());
            }
        }

        if ($request->boolean('return_to_videoeditor')) {
            return redirect()
                ->route('projects.videoeditor', [
                    'project' => $scene->project_id,
                    'active_scene' => $scene->id,
                ])
                ->with('success', 'SEQUENCE UPDATED SUCCESSFULLY.');
        }

        return redirect()->route('projects.timeline', $scene->project_id)
            ->with('success', 'SEQUENCE UPDATED SUCCESSFULLY.');
    }

    /**
     * Store the scene script and lock it into the timeline.
     */
    public function store(Request $request)
    {
        // FIX: Strip leading zeros from '0001' to make it a clean integer '1'
        // This prevents the "must be an integer" validation error
        $request->merge([
            'order_index' => (int) $request->order_index,
        ]);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'order_index' => 'required|integer|min:1',
            'script_segment' => 'required|string',
            'characters' => 'array|nullable',
            'characters.*' => 'exists:characters,id'
        ]);

        Auth::user()->projects()->findOrFail($validated['project_id']);

        if (!empty($validated['characters'])) {
            $validCharacterCount = Character::where(function ($query) use ($validated) {
                    $query->where('project_id', $validated['project_id'])->orWhereNull('project_id');
                })
                ->whereIn('id', $validated['characters'])
                ->count();

            abort_unless($validCharacterCount === count($validated['characters']), 422, 'Selected cast must belong to this project.');

            Character::whereNull('project_id')
                ->whereIn('id', $validated['characters'])
                ->update(['project_id' => $validated['project_id']]);
        }

        // 1. Initialize the new timeline block
        $scene = Scene::create([
            'project_id' => $validated['project_id'],
            'order_index' => $validated['order_index'],
            'script_segment' => $validated['script_segment'],
            'status' => 'Draft',
        ]);

        // 2. Attach Cast Members to the pivot table (character_scene)
        // This ensures the AI Seeds are linked to this specific shot
        if ($request->has('characters')) {
            $scene->characters()->sync($validated['characters']);
        }

        // 3. Route to the Timeline or Editor
        // I updated this to point to the timeline you just built!
        return redirect()->route('projects.timeline', $validated['project_id'])
            ->with('success', 'SEQUENCE #' . $scene->order_index . ' LOCKED INTO TIMELINE.');
    }
    public function destroy(Scene $scene)
    {
        $this->ensureSceneOwnership($scene);

        // 1. Get the project ID before deleting so we can redirect back
        $projectId = $scene->project_id;

        if ($scene->video_path && Storage::disk('public')->exists($scene->video_path)) {
            Storage::disk('public')->delete($scene->video_path);
        }

        // 2. Detach all assigned characters from the pivot table first
        $scene->characters()->detach();

        // 3. Delete the actual scene record
        $scene->delete();

        // 4. Redirect back to the timeline with a status message
        return redirect()->route('scenes.index')
            ->with('success', 'SEQUENCE PURGED FROM TIMELINE.');
    }

    private function ensureSceneOwnership(Scene $scene): void
    {
        abort_unless((int) $scene->project()->value('user_id') === (int) Auth::id(), 404);
    }

    public function generateSceneVideo(Request $request)
    {
        set_time_limit(300);
        ini_set('max_execution_time', '300');
        ini_set('memory_limit', '512M');

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'script_segment' => 'required|string',
            'order_index' => 'nullable|integer',
            'characters' => 'required|array',
            'characters.*' => 'exists:characters,id',
        ]);

        $characters = Character::whereIn('id', $validated['characters'])->get();

        if ($characters->isEmpty()) {
            return back()->with('error', 'Please select at least one character.');
        }

        $castPrompt = '';

        foreach ($characters as $character) {
            $castPrompt .= "
    CHARACTER NAME: {$character->name}
    ROLE: {$character->role}
    AI TAG: {$character->ai_tag}
    CHARACTER PROMPT:
    {$character->prompt}

    IMAGE REFERENCE:
    " . ($character->image_path ? asset('storage/' . $character->image_path) : 'No image reference') . "

    ";
        }

        $scenePrompt = "
    SEEDANCE 2.0 CINEMATIC SCENE GENERATION

    SCENE ACTION:
    {$validated['script_segment']}

    CAST:
    {$castPrompt}

    VIDEO STYLE:
    Ultra realistic live-action cinematic Sri Lankan movie scene.
    Real human actors, natural motion, emotional acting, realistic faces, film-grade lighting, cinematic camera movement, 24fps movie look.

    CAMERA DIRECTION:
    Start with a cinematic establishing shot, then move into a dramatic close-up. Smooth camera motion, shallow depth of field, professional lens look.

    STRICT RULES:
    NO anime.
    NO cartoon.
    NO 3D render.
    NO illustration.
    NO stylized game character.
    Real human live-action movie only.

    OUTPUT:
    5 to 8 second cinematic cutscene suitable for a movie trailer.
    ";

        try {
            $response = Http::timeout(300)
                ->connectTimeout(60)
                ->post(env('SEEDANCE_BASE_URL') . '/text2video', [
                    'key' => env('SEEDANCE_API_KEY'),
                    'prompt' => $scenePrompt,
                    'width' => 1024,
                    'height' => 576,
                    'duration' => 6,
                    'guidance_scale' => 7.5,
                ]);

            if ($response->failed()) {
                return back()->with('error', 'Seedance generation failed: ' . $response->body());
            }

            $data = $response->json();

            $scene = Scene::create([
                'project_id' => $validated['project_id'],
                'order_index' => $validated['order_index'] ?? 1,
                'script_segment' => $validated['script_segment'],
                'prompt' => $scenePrompt,
                'video_url' => $data['output'][0] ?? null,
                'seedance_job_id' => $data['id'] ?? $data['job_id'] ?? null,
                'status' => 'Processing',
            ]);

            $scene->characters()->sync($validated['characters']);

            return redirect()
                ->route('scenes.show', $scene)
                ->with('success', 'Seedance 2.0 scene generation started.');

        } catch (\Throwable $e) {
            return back()->with('error', 'Seedance error: ' . $e->getMessage());
        }
    }
}
