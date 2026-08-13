<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('production_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->onDelete('cascade');
            $table->string('logline')->nullable();
            $table->longText('full_story')->nullable();
            $table->longText('script_outline')->nullable();
            $table->longText('scene_breakdown')->nullable();
            $table->date('schedule_start_date')->nullable();
            $table->date('schedule_end_date')->nullable();
            $table->unsignedInteger('shooting_days')->nullable();
            $table->time('daily_call_time')->nullable();
            $table->date('render_deadline')->nullable();
            $table->date('release_target')->nullable();
            $table->longText('production_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plans');
    }
};
