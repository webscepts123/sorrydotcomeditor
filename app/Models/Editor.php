<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Editor extends Model
{
    protected $fillable = ['name', 'email', 'role', 'avatar_path', 'is_active'];

    /**
     * The movies that the editor is working on.
     */
    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class)
                    ->withPivot('assigned_task')
                    ->withTimestamps();
    }
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }
}
