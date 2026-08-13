<?php

use App\Services\ComfyVideoService;
use Illuminate\Contracts\Console\Kernel;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$path = $app->make(ComfyVideoService::class)
    ->downloadCompletedVideo($argv[1] ?? '');

if (!$path) {
    fwrite(STDERR, "The ComfyUI job is not complete.\n");
    exit(1);
}

echo $path . PHP_EOL;
