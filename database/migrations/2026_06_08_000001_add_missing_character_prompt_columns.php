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
        Schema::table('characters', function (Blueprint $table) {
            if (!Schema::hasColumn('characters', 'video_prompt')) {
                $table->text('video_prompt')->nullable()->after('prompt');
            }

            if (!Schema::hasColumn('characters', 'sync_face_status')) {
                $table->string('sync_face_status')->nullable()->after('image_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            if (Schema::hasColumn('characters', 'video_prompt')) {
                $table->dropColumn('video_prompt');
            }

            if (Schema::hasColumn('characters', 'sync_face_status')) {
                $table->dropColumn('sync_face_status');
            }
        });
    }
};
