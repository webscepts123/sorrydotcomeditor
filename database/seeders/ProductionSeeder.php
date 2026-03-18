<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Editor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create the Main Director (You)
        $admin = User::create([
            'name' => 'Amila Director',
            'email' => 'admin@voidshadoweditor.com',
            'password' => Hash::make('void2026'), // Your Encryption Key
            'email_verified_at' => now(),
        ]);

        // 2. Create the "Void Shadow" Project
        $project = Project::create([
            'user_id' => $admin->id,
            'title' => 'Void Shadow',
            'slug' => Str::slug('Void Shadow'),
            'description' => 'A 2.5-hour cinematic dark thriller set in Sri Lanka.',
            'aspect_ratio' => '2.39:1', // Anamorphic Widescreen
            'style_preset' => 'high-contrast-noir',
            'status' => 'draft',
        ]);

        // 3. Create System Editors (AI & Human)
        $editors = [
            ['name' => 'Seedance Engine v2', 'role' => 'vfx_artist', 'email' => 'ai-engine@voidshadoweditor.com'],
            ['name' => 'Lead Sound Designer', 'role' => 'sound_engineer', 'email' => 'audio@voidshadoweditor.com'],
        ];

        foreach ($editors as $editorData) {
            $editor = Editor::create($editorData);
            
            // Assign them to the project
            $project->editors()->attach($editor->id, [
                'assigned_task' => $editorData['role'] === 'vfx_artist' 
                                   ? 'AI Clip Generation & Stitching' 
                                   : 'Background Score Composition'
            ]);
        }
    }
}
