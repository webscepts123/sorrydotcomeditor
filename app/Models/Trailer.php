<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Trailer extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',          // E.g., "Official Teaser", "Final Trailer"
        'description',    // Notes on the cut
        'video_path',     // The rendered output file
        'duration',       // Length in seconds or format like "02:15"
        'status'          // 'Draft', 'Processing', 'Ready', 'Published'
    ];

    /**
     * Relationship: A trailer belongs to a specific Production/Project.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
