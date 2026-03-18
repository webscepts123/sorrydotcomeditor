<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;
use App\Models\Editor;
use App\Models\Scene;
use App\Models\VideoClip;

class DashboardController extends Controller
{
    /**
     * Display the Production Command Center.
     */
    public function index()
    {
        // 1. Get the Active Production (Most recently updated project)
        $activeProject = Project::withCount('scenes')->latest('updated_at')->first();
        
        // Calculate minutes based on 15 seconds per scene (Seedance standard)
        $totalMinutes = $activeProject ? ($activeProject->scenes_count * 15) / 60 : 0;
        $progressPercent = min(100, ($totalMinutes / 150) * 100); // 150 mins = 2.5 hours

        // 2. Get AI Render Queue Status
        $renderingScene = Scene::whereIn('status', ['Rendering', 'Processing'])->latest()->first();
        $nextScene = Scene::whereIn('status', ['Draft', 'Queued'])->oldest('order_index')->first();

        // 3. Get Actual Server NVMe Storage
        $diskPath = storage_path('app/public');
        $totalSpace = function_exists('disk_total_space') ? @disk_total_space($diskPath) : 1;
        $freeSpace = function_exists('disk_free_space') ? @disk_free_space($diskPath) : 1;
        
        $usedSpace = $totalSpace - $freeSpace;
        $storagePercent = ($totalSpace > 0) ? ($usedSpace / $totalSpace) * 100 : 0;

        // Convert to GB for the UI
        $usedGB = round($usedSpace / 1073741824, 2);
        $totalGB = round($totalSpace / 1073741824, 2);

        // 4. Load Team
        $editors = Editor::latest()->take(5)->get(); // Adjust model name if needed

        return view('dashboard', compact(
            'activeProject', 'totalMinutes', 'progressPercent', 
            'renderingScene', 'nextScene', 
            'usedGB', 'totalGB', 'storagePercent', 
            'editors'
        ));
    }
}
