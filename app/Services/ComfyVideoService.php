<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ComfyVideoService
{
    public function submit(string $prompt): string
    {
        $workflowPath = base_path(config('services.comfyui.workflow'));

        if (!is_file($workflowPath)) {
            throw new RuntimeException("ComfyUI workflow not found at {$workflowPath}. Export a Wan API workflow to this file.");
        }

        $workflow = json_decode(file_get_contents($workflowPath), true);
        if (!is_array($workflow)) {
            throw new RuntimeException('The ComfyUI workflow is not valid JSON. Export it using ComfyUI Save (API Format).');
        }

        $workflow = $this->replaceTokens($workflow, [
            '{{PROMPT}}' => $prompt,
            '{{NEGATIVE_PROMPT}}' => 'cartoon, anime, illustration, low quality, blurry, distorted faces, extra limbs, watermark, text',
        ]);

        $response = Http::timeout(20)->post($this->url('/prompt'), [
            'prompt' => $workflow,
            'client_id' => 'sorrydotcomeditor-' . bin2hex(random_bytes(8)),
        ]);

        if ($response->failed() || !$response->json('prompt_id')) {
            throw new RuntimeException($response->json('error.message') ?: $response->body() ?: 'ComfyUI rejected the workflow.');
        }

        return $response->json('prompt_id');
    }

    public function downloadCompletedVideo(string $promptId): ?string
    {
        $response = Http::timeout(20)->get($this->url('/history/' . rawurlencode($promptId)));
        if ($response->failed()) {
            throw new RuntimeException('Could not read the ComfyUI job status.');
        }

        $job = $response->json($promptId);
        if (!$job) return null;

        foreach (($job['outputs'] ?? []) as $output) {
            foreach (['videos', 'gifs', 'images'] as $type) {
                foreach (($output[$type] ?? []) as $file) {
                    $filename = $file['filename'] ?? null;
                    if (!$filename) continue;

                    $download = Http::timeout(120)->get($this->url('/view'), [
                        'filename' => $filename,
                        'subfolder' => $file['subfolder'] ?? '',
                        'type' => $file['type'] ?? 'output',
                    ]);
                    if ($download->failed()) continue;

                    $extension = pathinfo($filename, PATHINFO_EXTENSION) ?: 'mp4';
                    $path = 'scenes/comfy-' . $promptId . '.' . $extension;
                    Storage::disk('public')->put($path, $download->body());
                    return $path;
                }
            }
        }

        if (($job['status']['status_str'] ?? null) === 'error') {
            throw new RuntimeException('ComfyUI reported that video generation failed. Check its console for details.');
        }

        return null;
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.comfyui.url'), '/') . $path;
    }

    private function replaceTokens(array $value, array $tokens): array
    {
        array_walk_recursive($value, function (&$item) use ($tokens) {
            if (is_string($item)) $item = strtr($item, $tokens);
        });
        return $value;
    }
}
