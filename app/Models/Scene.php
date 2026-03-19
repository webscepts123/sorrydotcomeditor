<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Scene extends Model
{
    protected $fillable = ['project_id', 'order_index', 'script_segment', 'status'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function characters()
    {
        return $this->belongsToMany(Character::class);
    }
}
