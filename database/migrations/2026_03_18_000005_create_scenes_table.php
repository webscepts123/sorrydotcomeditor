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
        Schema::create('scenes', function (Blueprint $table) {
            $table->id();
            
            // This line is what caused the error because 'projects' wasn't ready yet
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            
            $table->integer('order_index')->default(0);
            $table->text('script_segment')->nullable();
            $table->enum('status', ['pending', 'generating', 'ready', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scenes');
    }
};
