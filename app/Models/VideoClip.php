<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoClip extends Model
{
    // Ensure these fields are fillable for your Seedance 2.0 metadata
    protected $fillable = [
        'scene_id', 
        'file_path', 
        'duration', 
        'status', 
        'seedance_tag'
    ];

    /**
     * Get the scene that owns the video clip.
     */
    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }
}
