<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProductionPlan;
use App\Models\Scene;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ToolController extends Controller
{
    public function syncFace()
    {
        return view('tools.sync-face');
    }

    public function genScore()
    {
        return view('tools.gen-score');
    }

    public function script(Request $request): View
    {
        $projects = Project::with(['productionPlan', 'characters', 'scenes'])
            ->latest('updated_at')
            ->get();

        $selectedProject = $projects->firstWhere('id', (int) $request->query('project_id')) ?? $projects->first();
        $scriptText = $selectedProject ? $this->scriptTextForProject($selectedProject) : '';
        $parsedScenes = $this->parseScenesFromText($scriptText);
        $sceneSource = count($parsedScenes) > 0 ? collect($parsedScenes) : $this->scenesAsParsedBlocks($selectedProject?->scenes ?? collect());

        $wordCount = str_word_count(strip_tags($scriptText));
        $detectedSceneCount = $sceneSource->count();
        $estimatedSeconds = $wordCount > 0
            ? max(60, (int) ceil(($wordCount / 130) * 60))
            : max(0, $detectedSceneCount * 15);

        return view('tools.script', [
            'projects' => $projects,
            'selectedProject' => $selectedProject,
            'scriptText' => $scriptText,
            'wordCount' => $wordCount,
            'detectedSceneCount' => $detectedSceneCount,
            'estimatedScreenTime' => $this->formatDuration($estimatedSeconds),
            'detectedCast' => $this->detectedCast($selectedProject, $scriptText),
            'detectedLocations' => $this->detectedLocations($sceneSource, $scriptText),
            'clipPrompts' => $this->clipPrompts($selectedProject, $sceneSource),
        ]);
    }

    public function parseScript(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'script_text' => ['nullable', 'string'],
        ]);

        $project = Project::with('characters')->findOrFail($validated['project_id']);
        $scriptText = trim((string) ($validated['script_text'] ?? ''));
        $parsedScenes = $this->parseScenesFromText($scriptText);

        if ($scriptText === '') {
            return redirect()
                ->route('tools.script', ['project_id' => $project->id])
                ->with('error', 'Add script text before parsing scenes.');
        }

        if (count($parsedScenes) === 0) {
            $parsedScenes = [[
                'heading' => 'SCENE 001',
                'body' => $scriptText,
                'text' => $scriptText,
            ]];
        }

        ProductionPlan::updateOrCreate(
            ['project_id' => $project->id],
            [
                'full_story' => $scriptText,
                'scene_breakdown' => collect($parsedScenes)
                    ->map(fn (array $scene, int $index) => 'Scene '.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT).': '.$scene['heading'])
                    ->implode("\n"),
            ]
        );

        foreach ($parsedScenes as $index => $sceneBlock) {
            $scene = Scene::updateOrCreate(
                [
                    'project_id' => $project->id,
                    'order_index' => $index + 1,
                ],
                [
                    'script_segment' => trim($sceneBlock['text']),
                    'status' => 'Draft',
                ]
            );

            $matchingCharacterIds = $project->characters
                ->filter(fn ($character) => $character->name && stripos($sceneBlock['text'], $character->name) !== false)
                ->pluck('id')
                ->all();

            if ($matchingCharacterIds !== []) {
                $scene->characters()->sync($matchingCharacterIds);
            }
        }

        return redirect()
            ->route('tools.script', ['project_id' => $project->id])
            ->with('success', count($parsedScenes).' scene(s) parsed into the timeline.');
    }

    private function scriptTextForProject(Project $project): string
    {
        $plan = $project->productionPlan;
        $parts = [];

        if ($plan?->full_story) {
            $parts[] = $plan->full_story;
        }

        if ($parts === [] && $plan?->script_outline) {
            $parts[] = $plan->script_outline;
        }

        if ($parts === [] && $plan?->scene_breakdown) {
            $parts[] = $plan->scene_breakdown;
        }

        if ($parts === [] && $project->scenes->isNotEmpty()) {
            $parts[] = $project->scenes
                ->map(fn (Scene $scene) => 'SCENE '.str_pad((string) $scene->order_index, 3, '0', STR_PAD_LEFT)."\n".trim((string) $scene->script_segment))
                ->implode("\n\n");
        }

        return trim(implode("\n\n", array_filter($parts)));
    }

    private function parseScenesFromText(string $text): array
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", $text));

        if ($text === '') {
            return [];
        }

        $lines = explode("\n", $text);
        $scenes = [];
        $current = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            $isHeading = preg_match('/^(?:\[?\s*SCENE\s+\d+[^\]]*\]?|(?:INT|EXT|INT\/EXT|I\/E)\.\s+.+)$/i', $trimmed) === 1;

            if ($isHeading) {
                if ($current !== null) {
                    $scenes[] = $current;
                }

                $current = [
                    'heading' => trim($trimmed, "[] \t\n\r\0\x0B"),
                    'body' => [],
                ];

                continue;
            }

            if ($current !== null) {
                $current['body'][] = $line;
            }
        }

        if ($current !== null) {
            $scenes[] = $current;
        }

        if ($scenes === []) {
            $paragraphs = preg_split('/\n\s*\n/', $text) ?: [];

            return collect($paragraphs)
                ->filter(fn (string $paragraph) => trim($paragraph) !== '')
                ->values()
                ->map(fn (string $paragraph, int $index) => [
                    'heading' => 'SCENE '.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    'body' => trim($paragraph),
                    'text' => trim($paragraph),
                ])
                ->all();
        }

        return collect($scenes)
            ->map(function (array $scene) {
                $body = trim(implode("\n", $scene['body']));
                $text = trim($scene['heading'].($body !== '' ? "\n".$body : ''));

                return [
                    'heading' => $scene['heading'],
                    'body' => $body,
                    'text' => $text,
                ];
            })
            ->all();
    }

    private function scenesAsParsedBlocks(Collection $scenes): Collection
    {
        return $scenes->map(fn (Scene $scene) => [
            'heading' => 'SCENE '.str_pad((string) $scene->order_index, 3, '0', STR_PAD_LEFT),
            'body' => trim((string) $scene->script_segment),
            'text' => trim((string) $scene->script_segment),
            'model' => $scene,
        ]);
    }

    private function detectedCast(?Project $project, string $scriptText): Collection
    {
        if (! $project) {
            return collect();
        }

        return $project->characters
            ->filter(fn ($character) => $character->name && ($scriptText === '' || stripos($scriptText, $character->name) !== false))
            ->values();
    }

    private function detectedLocations(Collection $sceneSource, string $scriptText): Collection
    {
        $locations = collect($sceneSource)
            ->pluck('heading')
            ->filter(fn ($heading) => preg_match('/^(INT|EXT|INT\/EXT|I\/E)\./i', (string) $heading) === 1)
            ->map(fn ($heading) => preg_replace('/\s+-\s+.*$/', '', (string) $heading))
            ->filter()
            ->unique()
            ->values();

        if ($locations->isNotEmpty()) {
            return $locations;
        }

        preg_match_all('/^(INT|EXT|INT\/EXT|I\/E)\.\s+(.+)$/mi', $scriptText, $matches);

        return collect($matches[0] ?? [])
            ->map(fn ($heading) => preg_replace('/\s+-\s+.*$/', '', trim($heading)))
            ->filter()
            ->unique()
            ->values();
    }

    private function clipPrompts(?Project $project, Collection $sceneSource): Collection
    {
        if (! $project) {
            return collect();
        }

        if ($project->scenes->isNotEmpty()) {
            return $project->scenes->map(fn (Scene $scene) => [
                'label' => 'CLIP '.str_pad((string) $scene->order_index, 3, '0', STR_PAD_LEFT),
                'duration' => '15s',
                'text' => trim((string) $scene->script_segment),
                'status' => $scene->status,
            ]);
        }

        return $sceneSource->take(20)->values()->map(fn (array $scene, int $index) => [
            'label' => 'CLIP '.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            'duration' => '15s',
            'text' => $scene['text'],
            'status' => 'Draft',
        ]);
    }

    private function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $remainingSeconds);
    }
}
