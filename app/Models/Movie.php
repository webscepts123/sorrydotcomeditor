<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    public function editors(): BelongsToMany
    {
        return $this->belongsToMany(Editor::class)
                    ->withPivot('assigned_task')
                    ->withTimestamps();
    }
}
