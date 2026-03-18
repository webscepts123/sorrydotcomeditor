<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'aspect_ratio', 
        'style_preset', 'poster_image', 'status'
    ];

    // Get all AI characters for this movie
    public function characters(): HasMany {
        return $this->hasMany(Character::class);
    }

    // Get the sequence of scenes (the 150-minute timeline)
    public function scenes(): HasMany {
        return $this->hasMany(Scene::class)->orderBy('order_index');
    }

    // Get all audio/music tracks
    public function soundtracks(): HasMany {
        return $this->hasMany(Soundtrack::class);
    }

    // Get the team of human or AI editors
    public function editors(): BelongsToMany
    {
        // Remove 'editors' from here. 
        // Laravel will now automatically look for 'editor_project'.
        return $this->belongsToMany(Editor::class)
                    ->withPivot('assigned_task')
                    ->withTimestamps();
    }

    /**
     * Helper: Calculate production progress %
     */
    public function getProgressAttribute() {
        if ($this->total_scenes === 0) return 0;
        return ($this->completed_clips / ($this->total_scenes * 10)) * 100; 
    }
}
