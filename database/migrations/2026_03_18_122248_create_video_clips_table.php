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
        Schema::create('video_clips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scene_id')->constrained()->onDelete('cascade');
            $table->string('prompt');
            $table->string('video_path')->nullable();
            $table->string('last_frame_path')->nullable(); // Crucial for @Extension
            $table->enum('status', ['queued', 'generating', 'completed', 'failed']);
            $table->integer('duration')->default(15); // Seconds
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('video_clips');
    }
};
