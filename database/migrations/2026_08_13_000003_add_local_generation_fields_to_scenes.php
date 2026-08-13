<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scenes', function (Blueprint $table) {
            $table->string('generation_job_id')->nullable()->after('video_path');
            $table->text('generation_error')->nullable()->after('generation_job_id');
        });
    }

    public function down(): void
    {
        Schema::table('scenes', fn (Blueprint $table) => $table->dropColumn(['generation_job_id', 'generation_error']));
    }
};
