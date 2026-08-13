<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function index()
    {
        $dbStatus = 'Disconnected';
        $dbOnline = false;

        try {
            DB::connection()->getPdo();
            $dbOnline = true;
            $dbStatus = sprintf(
                '%s-%s-connected (%s:%s)',
                strtoupper(config('database.default')),
                config('database.connections.' . config('database.default') . '.database'),
                config('database.connections.' . config('database.default') . '.host'),
                config('database.connections.' . config('database.default') . '.port')
            );
        } catch (\Throwable $e) {
            $dbStatus = 'Database offline: ' . $e->getMessage();
        }

        $storagePath = storage_path('app/public');
        $totalSpace = function_exists('disk_total_space') ? @disk_total_space($storagePath) : 0;
        $freeSpace = function_exists('disk_free_space') ? @disk_free_space($storagePath) : 0;
        $usedSpace = max(0, (int) $totalSpace - (int) $freeSpace);

        $settings = [
            'seedance_api_key' => env('SEEDANCE_API_KEY', ''),
            'seedance_base_url' => env('SEEDANCE_BASE_URL', ''),
            'openai_api_key' => env('OPENAI_API_KEY', ''),
            'comfyui_url' => env('COMFYUI_URL', 'http://127.0.0.1:8188'),
            'comfyui_video_workflow' => env('COMFYUI_VIDEO_WORKFLOW', 'storage/app/comfyui/wan_t2v_api.json'),
            'default_render_resolution' => env('DEFAULT_RENDER_RESOLUTION', '4k'),
            'db_status' => $dbStatus,
            'db_online' => $dbOnline,
            'workstation' => php_uname('s') . ' ' . php_uname('r') . ' / PHP ' . PHP_VERSION,
            'server_software' => request()->server('SERVER_SOFTWARE', 'Laravel development server'),
            'storage_used_gb' => round($usedSpace / 1073741824, 2),
            'storage_total_gb' => round(((int) $totalSpace) / 1073741824, 2),
            'storage_percent' => $totalSpace > 0 ? min(100, round(($usedSpace / $totalSpace) * 100, 1)) : 0,
            'queue_connection' => config('queue.default'),
            'cache_store' => config('cache.default'),
            'filesystem_disk' => config('filesystems.default'),
            'ffmpeg_status' => trim((string) shell_exec('where ffmpeg 2>NUL')) !== '' ? 'Available' : 'Not installed',
        ];

        return view('settings.index', compact('settings'));
    }

    /**
     * Update system settings (API keys, project defaults).
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'seedance_api_key' => 'nullable|string',
            'seedance_base_url' => 'nullable|url',
            'openai_api_key' => 'nullable|string',
            'comfyui_url' => 'required|url',
            'comfyui_video_workflow' => 'required|string|max:500',
            'default_render_resolution' => 'required|in:720p,1080p,2k,4k,8k',
        ]);

        $this->setEnvValues([
            'SEEDANCE_API_KEY' => $validated['seedance_api_key'] ?? '',
            'SEEDANCE_BASE_URL' => $validated['seedance_base_url'] ?? '',
            'OPENAI_API_KEY' => $validated['openai_api_key'] ?? '',
            'COMFYUI_URL' => $validated['comfyui_url'],
            'COMFYUI_VIDEO_WORKFLOW' => $validated['comfyui_video_workflow'],
            'DEFAULT_RENDER_RESOLUTION' => $validated['default_render_resolution'],
        ]);

        Artisan::call('config:clear');

        return back()->with('success', 'System configuration updated successfully.');
    }

    public function refreshApiKey()
    {
        $newKey = 'sk_void_' . Str::random(40);

        $this->setEnvValues([
            'SEEDANCE_API_KEY' => $newKey,
        ]);

        Artisan::call('config:clear');

        return back()->with('success', 'Seedance API key placeholder regenerated.');
    }

    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');

        return back()->with('success', 'Application cache cleared.');
    }

    private function setEnvValues(array $values): void
    {
        $envPath = base_path('.env');
        $env = file_exists($envPath) ? file_get_contents($envPath) : '';

        foreach ($values as $key => $value) {
            $escapedValue = $this->formatEnvValue((string) $value);
            $pattern = "/^{$key}=.*$/m";

            if (preg_match($pattern, $env)) {
                $env = preg_replace($pattern, "{$key}={$escapedValue}", $env);
            } else {
                $env .= PHP_EOL . "{$key}={$escapedValue}";
            }
        }

        file_put_contents($envPath, trim($env) . PHP_EOL);
    }

    private function formatEnvValue(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (preg_match('/\s|#|"|\'/', $value)) {
            return '"' . str_replace('"', '\"', $value) . '"';
        }

        return $value;
    }
}
