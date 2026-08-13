<?php

namespace App\Http\Controllers;

use App\Models\ProductionPlan;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionPlanController extends Controller
{
    public function index(Request $request)
    {
        $projects = Auth::user()
            ->projects()
            ->orderBy('title')
            ->get();

        $project = $request->filled('project_id')
            ? $projects->firstWhere('id', (int) $request->query('project_id'))
            : $projects->sortByDesc('updated_at')->first();

        $plan = $project
            ? ProductionPlan::firstOrCreate(['project_id' => $project->id])
            : null;

        return view('planning.index', compact('projects', 'project', 'plan'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'logline' => 'nullable|string|max:255',
            'full_story' => 'nullable|string',
            'script_outline' => 'nullable|string',
            'scene_breakdown' => 'nullable|string',
            'schedule_start_date' => 'nullable|date',
            'schedule_end_date' => 'nullable|date|after_or_equal:schedule_start_date',
            'shooting_days' => 'nullable|integer|min:0|max:10000',
            'daily_call_time' => 'nullable|date_format:H:i',
            'render_deadline' => 'nullable|date',
            'release_target' => 'nullable|date',
            'production_notes' => 'nullable|string',
        ]);

        $project = Auth::user()
            ->projects()
            ->whereKey($validated['project_id'])
            ->firstOrFail();

        ProductionPlan::updateOrCreate(
            ['project_id' => $project->id],
            $validated
        );

        return redirect()
            ->route('planning.index', ['project_id' => $project->id])
            ->with('success', 'Production planning saved.');
    }
}
