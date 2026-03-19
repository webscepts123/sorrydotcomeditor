<?php

namespace App\Http\Controllers;
use App\Models\Scene;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Character;

class SceneController extends Controller
{
    public function index()
    {
        // Fetch scenes ordered by their position in the movie timeline
        $scenes = Scene::with('project')
            ->orderBy('project_id')
            ->orderBy('order_index')
            ->paginate(15); // Pagination is vital for a 2.5-hour movie

        return view('scenes.index', compact('scenes'));
    }

    public function create()
    {
        $projects = Project::latest()->get();
        // Updated to get characters for the selection sidebar
        $characters = Character::orderBy('name')->get();
        
        return view('scenes.create', compact('projects', 'characters'));
    }

    /**
     * Trigger the AI generation process (Regenerate Seed).
     */
    public function render(Scene $scene)
    {
        // 1. Update the status so the UI knows it's working
        $scene->update(['status' => 'Processing']);

        // 2. [Future Implementation] Dispatch a Job to your Seedance API
        // \App\Jobs\GenerateSceneVideo::dispatch($scene);

        // 3. Redirect back to the timeline or editor with a status message
        return back()->with('success', 'AI ENGINE ENGAGED. RE-RENDERING SEQUENCE ID: ' . $scene->id);
    }
    /**
     * Show the form for editing the scene script and sequence.
     */
    public function edit(Scene $scene)
    {
        $projects = Project::latest()->get();
        $characters = Character::orderBy('name')->get();
        
        // Load existing character IDs so we can check the boxes in the UI
        $selectedCharacters = $scene->characters->pluck('id')->toArray();

        return view('scenes.edit', compact('scene', 'projects', 'characters', 'selectedCharacters'));
    }

    /**
     * Update the scene in storage.
     */
    public function update(Request $request, Scene $scene)
    {
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
        if ($request->has('characters')) {
            $scene->characters()->sync($validated['characters']);
        } else {
            $scene->characters()->detach();
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
        // 1. Get the project ID before deleting so we can redirect back
        $projectId = $scene->project_id;

        // 2. Detach all assigned characters from the pivot table first
        $scene->characters()->detach();

        // 3. Delete the actual scene record
        $scene->delete();

        // 4. Redirect back to the timeline with a status message
        return redirect()->route('projects.timeline', $projectId)
            ->with('success', 'SEQUENCE PURGED FROM TIMELINE.');
    }
}
