<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Project;

class ProjectController extends Controller
{
    public function assignEditor(Request $request, Project $project) {
        $project->editors()->attach($request->editor_id, [
            'assigned_task' => 'Seedance 2.0 Prompt Engineering for Scene 5'
        ]);
        
        return back()->with('success', 'Editor assigned to project.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // Create the project under the LOGGED-IN user
        $project = Auth::user()->projects()->create([
            'title' => $request->title,
            'slug' => \Str::slug($request->title),
            'description' => $request->description,
            'status' => 'draft'
        ]);

        return redirect()->route('projects.show', $project->id);
    }

    public function index()
    {
        // Only show projects belonging to the logged-in user
        $projects = Auth::user()->projects;
        return view('projects.index', compact('projects'));
    }
    public function show(Project $project)
    {
        // Eager load scenes and their video clips to prevent slow loading
        $project->load(['scenes' => function($query) {
            $query->orderBy('order_index', 'asc');
        }, 'scenes.videoClips']);
    
        // Calculate total clips for the 2.5-hour target
        $totalClips = $project->scenes->flatMap->videoClips->count();
        
        return view('projects.show', compact('project', 'totalClips'));
    }

    public function timeline(Project $project)
    {
        // Load scenes strictly ordered by their timeline index
        $project->load(['scenes' => function ($query) {
            $query->orderBy('order_index');
        }]);

        return view('projects.timeline', compact('project'));
    }

    public function destroy(Project $project)
    {
        // If you have scene files/videos on your Contabo server, delete them here first
        // Storage::deleteDirectory('projects/' . $project->id);

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project purged from Void System.');
    }
    public function editor(Project $project)
    {
        // Eager load scenes and characters for the editor view
        $project->load(['scenes', 'characters']);
        
        return view('projects.editor', compact('project'));
    }
    public function create()
    {
        // This looks for resources/views/projects/create.blade.php
        return view('projects.create');
    }

}
