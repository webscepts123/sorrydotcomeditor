<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Character;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CharacterController extends Controller
{
    public function generateDetails(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|in:protagonist,antagonist,supporting,mentor,comic_relief,love_interest,anti_hero',
        ]);

        $apiKey = env('OPENAI_API_KEY');

        if (!$apiKey) {
            return response()->json([
                'message' => 'Add your OpenAI API key in Settings before using AI generation.',
            ], 422);
        }

        try {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4.1-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a professional cinematic character designer. Return only valid JSON with exactly these string keys: description, personality, dialogue_style. Make each value vivid, specific, production-ready, and concise (2-3 sentences). Do not use markdown.',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Create character details for {$validated['name']}, whose role/archetype is {$validated['role']}.",
                        ],
                    ],
                ]);

            if ($response->failed()) {
                return response()->json([
                    'message' => $response->json('error.message') ?: 'OpenAI could not generate character details.',
                ], $response->status() >= 400 && $response->status() < 500 ? 422 : 502);
            }

            $details = json_decode($response->json('choices.0.message.content', ''), true);

            if (!is_array($details) || !isset($details['description'], $details['personality'], $details['dialogue_style'])) {
                return response()->json(['message' => 'OpenAI returned an invalid response. Please try again.'], 502);
            }

            return response()->json([
                'description' => (string) $details['description'],
                'personality' => (string) $details['personality'],
                'dialogue_style' => (string) $details['dialogue_style'],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'AI generation failed. Please try again.'], 500);
        }
    }

    public function index()
    {
        $characters = Character::with('project')->latest()->get();
        $projects = Auth::user()->projects()->orderBy('title')->get();

        return view('characters.index', compact('characters', 'projects'));
    }

    public function create()
    {
        $characters = Character::latest()->get();
        $projects = Auth::user()->projects()->orderBy('title')->get();
        return view('characters.create', compact('characters', 'projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'ai_tag' => 'nullable|string|max:100',
            'role' => 'required|string|max:100',
            'description' => 'nullable|string',
            'personality' => 'nullable|string',
            'dialogue_style' => 'nullable|string',
            'prompt' => 'nullable|string',
            'reference_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        Auth::user()->projects()->findOrFail($validated['project_id']);

        unset($validated['reference_image']);

        if ($request->hasFile('reference_image')) {
            $validated['image_path'] = $request->file('reference_image')->store('characters', 'public');
        }

        Character::create($validated);

        return redirect()->route('characters.index')
            ->with('success', 'Character identity has been locked into the system.');
    }

    public function show(Character $character)
    {
        $character->load(['project', 'scenes']);
        $projects = Auth::user()
            ->projects()
            ->whereKeyNot($character->project_id)
            ->orderBy('title')
            ->get();

        return view('characters.show', compact('character', 'projects'));
    }

    public function edit(Character $character)
    {
        $projects = Auth::user()->projects()->orderBy('title')->get();
        return view('characters.edit', compact('character', 'projects'));
    }

    public function update(Request $request, Character $character)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'ai_tag' => 'nullable|string|max:100',
            'role' => 'required|string|max:100',
            'description' => 'nullable|string',
            'personality' => 'nullable|string',
            'dialogue_style' => 'nullable|string',
            'prompt' => 'nullable|string',
            'reference_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        Auth::user()->projects()->findOrFail($validated['project_id']);

        unset($validated['reference_image']);

        if ($request->hasFile('reference_image')) {
            if ($character->image_path && Storage::disk('public')->exists($character->image_path)) {
                Storage::disk('public')->delete($character->image_path);
            }

            $validated['image_path'] = $request->file('reference_image')->store('characters', 'public');
        }

        $character->update($validated);

        return redirect()->route('characters.show', $character)
            ->with('success', 'Character identity has been updated.');
    }

    public function destroy(Character $character)
    {
        if ($character->image_path && Storage::disk('public')->exists($character->image_path)) {
            Storage::disk('public')->delete($character->image_path);
        }

        $character->delete();

        return redirect()->route('characters.index')
            ->with('success', 'Character identity has been terminated.');
    }

    public function transfer(Request $request, Character $character)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
        ]);

        $targetProject = Auth::user()
            ->projects()
            ->whereKey($validated['project_id'])
            ->firstOrFail();

        if ((int) $character->project_id === (int) $targetProject->id) {
            return back()->with('error', 'Character is already assigned to that project.');
        }

        $detachedSceneIds = $character->scenes()
            ->where('scenes.project_id', '!=', $targetProject->id)
            ->pluck('scenes.id');

        if ($detachedSceneIds->isNotEmpty()) {
            $character->scenes()->detach($detachedSceneIds);
        }

        $character->update([
            'project_id' => $targetProject->id,
        ]);

        return back()->with('success', "Character transferred to {$targetProject->title}.");
    }

    public function generateImage(Character $character)
    {
        set_time_limit(300);
        ini_set('max_execution_time', '300');

        // ✅ ALWAYS FROM DATABASE (show.blade.php)
        $prompt = $character->prompt;

        if (!$prompt) {
            return back()->with('error', 'Please generate or enter AI Prompt first.');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])
            ->timeout(300)
            ->post('https://api.openai.com/v1/images/generations', [
                'model' => 'gpt-image-1',
                'prompt' => $prompt,
                'size' => '1024x1024',
            ]);

            if ($response->failed()) {
                return back()->with('error', $response->body());
            }

            $data = $response->json();

            if (!isset($data['data'][0]['b64_json'])) {
                return back()->with('error', 'No image returned');
            }

            $image = base64_decode($data['data'][0]['b64_json']);

            $fileName = 'characters/' . uniqid() . '.png';

            Storage::disk('public')->put($fileName, $image);

            $character->update([
                'image_path' => $fileName
            ]);

            return back()->with('success', 'Character generated!');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function export(Character $character)
    {
        $filename = Str::slug($character->name) . '-character.json';

        return response()->json($this->toExportArray($character), 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ], JSON_PRETTY_PRINT);
    }

    public function exportAll()
    {
        $characters = Character::latest()->get();

        $payload = $characters->map(fn (Character $character) => $this->toExportArray($character))->all();

        $filename = 'characters-export-' . now()->format('Y-m-d') . '.json';

        return response()->json($payload, 200, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ], JSON_PRETTY_PRINT);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer',
            'import_file' => 'required|file|max:10240',
        ]);

        $project = Auth::user()->projects()->findOrFail($validated['project_id']);

        $decoded = json_decode($request->file('import_file')->get(), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return back()->with('error', 'That file is not a valid character export.');
        }

        $records = array_is_list($decoded) ? $decoded : [$decoded];
        $imported = 0;

        foreach ($records as $record) {
            if (!is_array($record) || empty($record['name'])) {
                continue;
            }

            $imagePath = null;

            if (!empty($record['image']['base64'])) {
                $binary = base64_decode($record['image']['base64'], true);

                if ($binary !== false) {
                    $extension = pathinfo($record['image']['filename'] ?? '', PATHINFO_EXTENSION) ?: 'png';
                    $imagePath = 'characters/' . uniqid() . '.' . $extension;
                    Storage::disk('public')->put($imagePath, $binary);
                }
            }

            Character::create([
                'project_id' => $project->id,
                'name' => (string) $record['name'],
                'ai_tag' => $record['ai_tag'] ?? null,
                'role' => $record['role'] ?? 'supporting',
                'description' => $record['description'] ?? null,
                'personality' => $record['personality'] ?? null,
                'dialogue_style' => $record['dialogue_style'] ?? null,
                'prompt' => $record['prompt'] ?? null,
                'video_prompt' => $record['video_prompt'] ?? null,
                'image_path' => $imagePath,
            ]);

            $imported++;
        }

        if ($imported === 0) {
            return back()->with('error', 'No valid characters were found in that file.');
        }

        return redirect()->route('characters.index')
            ->with('success', "Imported {$imported} character" . ($imported === 1 ? '' : 's') . " into {$project->title}.");
    }

    protected function toExportArray(Character $character): array
    {
        $image = null;

        if ($character->image_path && Storage::disk('public')->exists($character->image_path)) {
            $image = [
                'filename' => basename($character->image_path),
                'mime' => Storage::disk('public')->mimeType($character->image_path),
                'base64' => base64_encode(Storage::disk('public')->get($character->image_path)),
            ];
        }

        return [
            'name' => $character->name,
            'ai_tag' => $character->ai_tag,
            'role' => $character->role,
            'description' => $character->description,
            'personality' => $character->personality,
            'dialogue_style' => $character->dialogue_style,
            'prompt' => $character->prompt,
            'video_prompt' => $character->video_prompt,
            'image' => $image,
        ];
    }

    public function sendToSyncFace(Character $character)
    {
        set_time_limit(300);
        ini_set('max_execution_time', '300');
        ini_set('memory_limit', '512M');

        if (!$character->image_path || !Storage::disk('public')->exists($character->image_path)) {
            return back()->with('error', 'No character image found. Generate or upload an image first.');
        }

        // Get prompt directly from DB
        $videoPrompt = $character->video_prompt ?: $character->prompt;

        if (!$videoPrompt) {
            return back()->with('error', 'Please add video prompt or AI prompt first.');
        }

        $character->update([
            'sync_face_status' => 'video_ready',
            'video_prompt' => $videoPrompt,
        ]);

        return back()->with('success', 'Character prepared for real-to-video cutscene and trailer generation.');
    }
}
