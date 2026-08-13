<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class PosterController extends Controller
{
    public function index()
    {
        $projects = Auth::user()->projects()->latest('updated_at')->get();

        return view('posters.index', compact('projects'));
    }

    public function generateIdeas(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer',
        ]);

        $project = Auth::user()->projects()->findOrFail($validated['project_id']);
        $apiKey = env('OPENAI_API_KEY');

        if (!$apiKey) {
            return response()->json(['message' => 'Add your OpenAI API key in Settings before generating ideas.'], 422);
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
                            'content' => 'You are an award-winning movie poster creative director. Return only valid JSON with exactly two string keys: tagline and direction. The tagline must be memorable and under 12 words. The direction must be one rich, production-ready paragraph describing the main subject, environment, composition, color palette, lighting, atmosphere, typography placement, and visual symbolism. Do not use markdown.',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Movie: {$project->title}\nStory: {$project->description}\nStyle: " . str_replace(['_', '-'], ' ', (string) $project->style_preset) . "\nAspect ratio: {$project->aspect_ratio}",
                        ],
                    ],
                ]);

            if ($response->failed()) {
                return response()->json([
                    'message' => $response->json('error.message') ?: 'OpenAI could not generate poster ideas.',
                ], $response->status() >= 400 && $response->status() < 500 ? 422 : 502);
            }

            $ideas = json_decode($response->json('choices.0.message.content', ''), true);

            if (!is_array($ideas) || !isset($ideas['tagline'], $ideas['direction'])) {
                return response()->json(['message' => 'OpenAI returned invalid poster ideas. Please try again.'], 502);
            }

            return response()->json([
                'tagline' => (string) $ideas['tagline'],
                'direction' => (string) $ideas['direction'],
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Poster idea generation failed. Please try again.'], 500);
        }
    }

    public function generate(Request $request)
    {
        set_time_limit(300);
        ini_set('max_execution_time', '300');

        $validated = $request->validate([
            'project_id' => 'required|integer',
            'tagline' => 'nullable|string|max:180',
            'direction' => 'nullable|string|max:2000',
        ]);

        $project = Auth::user()->projects()->findOrFail($validated['project_id']);
        $apiKey = env('OPENAI_API_KEY');

        if (!$apiKey) {
            return back()->withInput()->with('error', 'Add your OpenAI API key in Settings before generating a poster.');
        }

        $tagline = trim($validated['tagline'] ?? '');
        $direction = trim($validated['direction'] ?? '');
        $story = trim((string) $project->description);
        $style = str_replace('_', ' ', (string) $project->style_preset);

        $prompt = <<<PROMPT
Create a premium vertical theatrical movie poster in a 2:3 composition.

Movie title: {$project->title}
Tagline: {$tagline}
Story: {$story}
Visual style: {$style}
Creative direction: {$direction}

Make it cinematic, photorealistic, dramatic, richly detailed, and suitable for a major theatrical release. Use a strong central composition, professional color grading, atmospheric depth, and intentional negative space. Display the movie title clearly and accurately. Include the tagline only when supplied. Do not add invented studio logos, billing blocks, watermarks, or unrelated text.
PROMPT;

        try {
            $response = Http::withToken($apiKey)
                ->timeout(300)
                ->post('https://api.openai.com/v1/images/generations', [
                    'model' => 'gpt-image-1',
                    'prompt' => $prompt,
                    'size' => '1024x1536',
                    'quality' => 'high',
                ]);

            if ($response->failed()) {
                return back()->withInput()->with(
                    'error',
                    $response->json('error.message') ?: 'OpenAI could not generate the poster.'
                );
            }

            $encodedImage = $response->json('data.0.b64_json');

            if (!$encodedImage || ($image = base64_decode($encodedImage, true)) === false) {
                return back()->withInput()->with('error', 'No valid poster image was returned.');
            }

            $posterPath = 'posters/project-' . $project->id . '-' . uniqid() . '.png';
            Storage::disk('public')->put($posterPath, $image);

            $oldPoster = $project->poster_image;
            $project->update(['poster_image' => $posterPath]);

            if ($oldPoster && $oldPoster !== $posterPath && Storage::disk('public')->exists($oldPoster)) {
                Storage::disk('public')->delete($oldPoster);
            }

            return redirect()->route('posters.index')->with('success', "Poster generated for {$project->title}.");
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Poster generation failed. Please try again.');
        }
    }
}
