<?php

use App\Models\Project;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo Project::with(['scenes:id,project_id,order_index,status,video_path'])
    ->get(['id', 'title', 'user_id'])
    ->toJson(JSON_PRETTY_PRINT) . PHP_EOL;
