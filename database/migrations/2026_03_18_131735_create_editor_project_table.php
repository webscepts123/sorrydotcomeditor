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
        Schema::create('editor_project', function (Blueprint $table) {
            $table->id();
            
            // 1. Link to the Editor (Must exist in 'editors' table)
            $table->foreignId('editor_id')
                  ->constrained()
                  ->onDelete('cascade');

            // 2. Link to the Project (Must exist in 'projects' table)
            $table->foreignId('project_id')
                  ->constrained()
                  ->onDelete('cascade');

            // 3. Custom field for your 2.5hr movie tasks
            $table->string('assigned_task')->nullable(); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('editor_project');
    }
};
