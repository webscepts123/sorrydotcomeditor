<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class RunPodStatusService
{
    public function get(): array
    {
        $url = rtrim((string) config('services.comfyui.url'), '/');
        $status = [
            'online' => false,
            'pod_id' => config('services.runpod.pod_id'),
            'pod_name' => config('services.runpod.pod_name'),
            'hourly_cost' => config('services.runpod.hourly_cost'),
            'gpu_name' => config('services.runpod.gpu_name'),
            'comfyui_version' => null,
            'ram_percent' => null,
            'vram_percent' => null,
            'ram_used_gb' => null,
            'ram_total_gb' => null,
            'vram_used_gb' => null,
            'vram_total_gb' => null,
            'running_jobs' => 0,
            'pending_jobs' => 0,
            'error' => null,
        ];

        try {
            $systemResponse = Http::acceptJson()->connectTimeout(3)->timeout(8)->get($url.'/system_stats');
            $queueResponse = Http::acceptJson()->connectTimeout(3)->timeout(8)->get($url.'/queue');

            if (! $systemResponse->successful()) {
                throw new \RuntimeException('ComfyUI returned HTTP '.$systemResponse->status());
            }

            $payload = $systemResponse->json();
            $system = data_get($payload, 'system', []);
            $device = data_get($payload, 'devices.0', []);

            $ramTotal = (float) ($system['ram_total'] ?? 0);
            $ramFree = (float) ($system['ram_free'] ?? 0);
            $vramTotal = (float) ($device['vram_total'] ?? 0);
            $vramFree = (float) ($device['vram_free'] ?? 0);

            $status['online'] = true;
            $status['comfyui_version'] = $system['comfyui_version'] ?? null;
            $status['gpu_name'] = $device['name'] ?? $status['gpu_name'];
            $status['ram_percent'] = $this->percentage($ramTotal - $ramFree, $ramTotal);
            $status['vram_percent'] = $this->percentage($vramTotal - $vramFree, $vramTotal);
            $status['ram_used_gb'] = $this->gigabytes($ramTotal - $ramFree);
            $status['ram_total_gb'] = $this->gigabytes($ramTotal);
            $status['vram_used_gb'] = $this->gigabytes($vramTotal - $vramFree);
            $status['vram_total_gb'] = $this->gigabytes($vramTotal);

            if ($queueResponse->successful()) {
                $queue = $queueResponse->json();
                $status['running_jobs'] = count((array) ($queue['queue_running'] ?? []));
                $status['pending_jobs'] = count((array) ($queue['queue_pending'] ?? []));
            }
        } catch (Throwable $exception) {
            report($exception);
            $status['error'] = 'RunPod ComfyUI is unreachable';
        }

        return $status;
    }

    private function percentage(float $used, float $total): ?float
    {
        return $total > 0 ? round(max(0, min(100, ($used / $total) * 100)), 1) : null;
    }

    private function gigabytes(float $bytes): ?float
    {
        return $bytes > 0 ? round($bytes / 1073741824, 1) : null;
    }
}
