<?php

use App\Models\Scene;
use App\Services\ComfyVideoService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Storage;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$scene = Scene::findOrFail((int) ($argv[1] ?? 0));
$path = $app->make(ComfyVideoService::class)->downloadCompletedVideo($scene->generation_job_id);

if (!$path) {
    fwrite(STDERR, "Render is not complete.\n");
    exit(1);
}

if ($scene->video_path && $scene->video_path !== $path && Storage::disk('public')->exists($scene->video_path)) {
    Storage::disk('public')->delete($scene->video_path);
}

$scene->update(['video_path' => $path, 'status' => 'Ready', 'generation_error' => null]);
echo $path . PHP_EOL;
