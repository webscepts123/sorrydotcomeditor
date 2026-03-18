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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g., "Void Shadow"
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            
            // AI Production Settings
            $table->string('aspect_ratio')->default('16:9');
            $table->string('resolution')->default('4K');
            $table->string('style_preset')->default('cinematic_dark_thriller');
            
            // Assets
            $table->string('poster_image')->nullable();
            $table->string('teaser_video')->nullable();
            $table->string('final_movie_path')->nullable();
            
            // Status Tracking (Crucial for 2.5-hour render)
            $table->integer('total_scenes')->default(0);
            $table->integer('completed_clips')->default(0);
            $table->enum('status', ['draft', 'generating', 'stitching', 'completed', 'failed'])->default('draft');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
