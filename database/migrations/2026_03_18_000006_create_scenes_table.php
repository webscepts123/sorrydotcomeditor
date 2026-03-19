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
        // 1. Create Scenes first
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->integer('order_index')->default(0);
            $table->text('script_segment')->nullable();
            $table->string('video_path')->nullable();
            $table->enum('status', ['Draft', 'Ready', 'Rendering', 'Processing', 'failed'])->default('Draft');
            $table->timestamps();
        });

        // 2. Create the Pivot ONLY if characters table exists
        // If this fails, it means your 'create_characters_table' file 
        // needs to have an earlier timestamp than this file.
        Schema::create('character_scene', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('characters')->onDelete('cascade');
            $table->foreignId('scene_id')->constrained('scenes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_scene');
        Schema::dropIfExists('scenes');
    }
};