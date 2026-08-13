<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scene extends Model
{
    protected $fillable = ['project_id', 'order_index', 'script_segment', 'video_path', 'status', 'generation_job_id', 'generation_error'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function characters()
    {
        return $this->belongsToMany(Character::class);
    }

    public function videoClips(): HasMany
    {
        return $this->hasMany(VideoClip::class);
    }
}
