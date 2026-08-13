<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soundtrack extends Model
{
    protected $fillable = [
        'movie_id',
        'title',
        'composer',
        'type',
        'notes',
        'file_path',
    ];
}
