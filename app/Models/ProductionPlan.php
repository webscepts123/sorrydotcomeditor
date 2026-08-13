<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionPlan extends Model
{
    protected $fillable = [
        'project_id',
        'logline',
        'full_story',
        'script_outline',
        'scene_breakdown',
        'schedule_start_date',
        'schedule_end_date',
        'shooting_days',
        'daily_call_time',
        'render_deadline',
        'release_target',
        'production_notes',
    ];

    protected $casts = [
        'schedule_start_date' => 'date',
        'schedule_end_date' => 'date',
        'render_deadline' => 'date',
        'release_target' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
