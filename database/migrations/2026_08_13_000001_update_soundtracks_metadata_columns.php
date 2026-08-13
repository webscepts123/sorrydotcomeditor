<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soundtracks', function (Blueprint $table) {
            $table->foreignId('movie_id')->nullable()->change();
            $table->string('composer')->nullable()->after('title');
            $table->text('notes')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('soundtracks', function (Blueprint $table) {
            $table->dropColumn(['composer', 'notes']);
            $table->foreignId('movie_id')->nullable(false)->change();
        });
    }
};
