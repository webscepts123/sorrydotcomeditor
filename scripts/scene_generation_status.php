<?php

use App\Models\Scene;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo Scene::query()
    ->select(['id', 'project_id', 'order_index', 'status', 'generation_job_id', 'generation_error', 'video_path', 'updated_at'])
    ->orderBy('id')
    ->get()
    ->toJson(JSON_PRETTY_PRINT) . PHP_EOL;
