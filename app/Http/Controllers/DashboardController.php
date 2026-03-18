<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Editor;
use App\Models\VideoClip;

class DashboardController extends Controller
{
    /**
     * Display the Production Command Center.
     */
    public function index()
    {
        // 1. Get the current active project for the logged-in user
        // We assume 'Void Shadow' is the primary project for now
        $project = Auth::user()->projects()
            ->with(['editors', 'scenes'])
            ->latest()
            ->first();

        // 2. Fallback if no project exists yet (Prevents errors)
        if (!$project) {
            return redirect()->route('projects.create')
                ->with('info', 'Please create your first movie project to access the dashboard.');
        }

        // 3. Calculate Movie Stats
        // Total minutes targeted: 150 (2.5 hours)
        $totalMinutesTarget = 150;
        
        // Count completed 15-second clips
        $completedClipsCount = VideoClip::whereHas('scene', function($query) use ($project) {
            $query->where('project_id', $project->id);
        })->where('status', 'completed')->count();

        // Convert clips to minutes (4 clips = 1 minute)
        $currentMinutes = $completedClipsCount / 4;
        $progressPercent = ($currentMinutes / $totalMinutesTarget) * 100;

        // 4. Fetch the Editors specifically assigned to this project
        $editors = $project->editors;

        // 5. Simulate Server/VDS Data (In 2026, you'd pull this via API or shell_exec)
        $serverStats = [
            'storage_used' => '420GB',
            'storage_total' => '800GB',
            'storage_percent' => 52,
            'node' => 'Contabo-SL-01'
        ];

        return view('dashboard', compact(
            'project', 
            'editors', 
            'progressPercent', 
            'currentMinutes', 
            'serverStats'
        ));
    }
}
