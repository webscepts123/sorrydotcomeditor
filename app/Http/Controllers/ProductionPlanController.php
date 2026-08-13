<?php

namespace App\Http\Controllers;

use App\Models\ProductionPlan;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ProductionPlanController extends Controller
{
    public function generateStory(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer',
            'logline' => 'nullable|string|max:255',
        ]);

        $project = Auth::user()->projects()->findOrFail($validated['project_id']);
        $apiKey = env('OPENAI_API_KEY');

        if (!$apiKey) {
            return response()->json(['message' => 'Add your OpenAI API key in Settings before generating story content.'], 422);
        }

        $brief = trim((string) $project->description);
        $style = str_replace(['_', '-'], ' ', (string) $project->style_preset);
        $logline = trim($validated['logline'] ?? '');

        try {
            $response = Http::withToken($apiKey)
                ->timeout(180)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4.1-mini',
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an expert feature-film screenwriter and production planner. Return only valid JSON with exactly these string keys: logline, full_story, script_outline, scene_breakdown. Write original, coherent, production-ready material. full_story must cover characters, conflict, major turns, climax, ending, and emotional arc. script_outline must clearly structure Act 1, Act 2, and Act 3 with inciting incident, turning points, midpoint, low point, climax, and resolution. scene_breakdown must be a useful numbered sequence of 20-30 scenes, each including location/time, characters, action, story purpose, and visual/render notes. Do not use markdown code fences.',
                        ],
                        [
                            'role' => 'user',
                            'content' => "Movie title: {$project->title}\nExisting logline or idea: {$logline}\nProject brief: {$brief}\nVisual style: {$style}\nAspect ratio: {$project->aspect_ratio}\nCreate all four planning fields so they are consistent with one another.",
                        ],
                    ],
                ]);

            if ($response->failed()) {
                return response()->json([
                    'message' => $response->json('error.message') ?: 'OpenAI could not generate the story plan.',
                ], $response->status() >= 400 && $response->status() < 500 ? 422 : 502);
            }

            $content = json_decode($response->json('choices.0.message.content', ''), true);
            $keys = ['logline', 'full_story', 'script_outline', 'scene_breakdown'];

            if (!is_array($content) || array_diff($keys, array_keys($content))) {
                return response()->json(['message' => 'OpenAI returned an incomplete story plan. Please try again.'], 502);
            }

            return response()->json(array_map('strval', array_intersect_key($content, array_flip($keys))));
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Story generation failed. Please try again.'], 500);
        }
    }

    public function index(Request $request)
    {
        $projects = Auth::user()
            ->projects()
            ->orderBy('title')
            ->get();

        $project = $request->filled('project_id')
            ? $projects->firstWhere('id', (int) $request->query('project_id'))
            : $projects->sortByDesc('updated_at')->first();

        $plan = $project
            ? ProductionPlan::firstOrCreate(['project_id' => $project->id])
            : null;

        return view('planning.index', compact('projects', 'project', 'plan'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'logline' => 'nullable|string|max:255',
            'full_story' => 'nullable|string',
            'script_outline' => 'nullable|string',
            'scene_breakdown' => 'nullable|string',
            'schedule_start_date' => 'nullable|date',
            'schedule_end_date' => 'nullable|date|after_or_equal:schedule_start_date',
            'shooting_days' => 'nullable|integer|min:0|max:10000',
            'daily_call_time' => 'nullable|date_format:H:i',
            'render_deadline' => 'nullable|date',
            'release_target' => 'nullable|date',
            'production_notes' => 'nullable|string',
        ]);

        $project = Auth::user()
            ->projects()
            ->whereKey($validated['project_id'])
            ->firstOrFail();

        ProductionPlan::updateOrCreate(
            ['project_id' => $project->id],
            $validated
        );

        return redirect()
            ->route('planning.index', ['project_id' => $project->id])
            ->with('success', 'Production planning saved.');
    }
}
