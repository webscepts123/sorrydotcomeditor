<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soundtracks', function (Blueprint $table) {
            $table->enum('type', ['cinematic', 'ambient', 'foley', 'dialogue'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('soundtracks', function (Blueprint $table) {
            $table->enum('type', ['background', 'dialogue', 'sfx'])->change();
        });
    }
};
