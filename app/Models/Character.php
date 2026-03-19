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
        'name',
        'ai_tag',
        'role',          // ADDED: From your new dropdown
        'description',   // ADDED: From your textarea
    ];

    /**
     * The Scenes that this character appears in.
     */
    public function scenes()
    {
        return $this->belongsToMany(Scene::class);
    }
}
