<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Character extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'project_id',
        'name',
        'ai_tag',
        'role',          // ADDED: From your new dropdown
        'description', 
        'personality',
        'dialogue_style',
        'prompt',
        'video_prompt',
        'image_path'  // ADDED: From your textarea
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The Scenes that this character appears in.
     */
    public function scenes()
    {
        return $this->belongsToMany(Scene::class);
    }
}
