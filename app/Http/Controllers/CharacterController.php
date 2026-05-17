<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Character;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CharacterController extends Controller
{
    public function index()
    {
        $characters = Character::latest()->get();
        return view('characters.index', compact('characters'));
    }

    public function create()
    {
        $characters = Character::latest()->get();
        return view('characters.create', compact('characters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ai_tag' => 'nullable|string|max:100',
            'role' => 'required|string|max:100',
            'description' => 'nullable|string',
            'personality' => 'nullable|string',
            'dialogue_style' => 'nullable|string',
            'prompt' => 'nullable|string',
            'reference_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

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
        $character->load('scenes');
        return view('characters.show', compact('character'));
    }

    public function edit(Character $character)
    {
        return view('characters.edit', compact('character'));
    }

    public function update(Request $request, Character $character)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ai_tag' => 'nullable|string|max:100',
            'role' => 'required|string|max:100',
            'description' => 'nullable|string',
            'personality' => 'nullable|string',
            'dialogue_style' => 'nullable|string',
            'prompt' => 'nullable|string',
            'reference_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

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