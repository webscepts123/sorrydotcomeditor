<?php

namespace App\Http\Controllers;
use App\Models\Scene;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Character;

class SceneController extends Controller
{
    public function index()
    {
        // Fetch scenes ordered by their position in the movie timeline
        $scenes = Scene::with('project')
            ->orderBy('project_id')
            ->orderBy('order_index')
            ->paginate(15); // Pagination is vital for a 2.5-hour movie

        return view('scenes.index', compact('scenes'));
    }

    public function create()
    {
        // Fetch active productions and cast members for the assignment dropdowns
        $projects = Project::latest()->get();
        $characters = Character::orderBy('name')->get();
        
        return view('scenes.create', compact('projects', 'characters'));
    }

    /**
     * Store the scene script and lock it into the timeline.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'order_index' => 'required|integer',
            'script_segment' => 'required|string',
            'characters' => 'array|nullable',
            'characters.*' => 'exists:characters,id'
        ]);

        // 1. Initialize the new timeline block
        $scene = Scene::create([
            'project_id' => $validated['project_id'],
            'order_index' => $validated['order_index'],
            'script_segment' => $validated['script_segment'],
            'status' => 'Draft',
        ]);

        // 2. Attach Cast Members to the scene for AI seed consistency
        if (!empty($validated['characters'])) {
            $scene->characters()->attach($validated['characters']);
        }

        // 3. Route directly to the AI Web Editor to begin generation
        return redirect()->route('projects.editor', $validated['project_id'])
            ->with('success', 'SEQUENCE INITIALIZED. Ready for prompt engineering.');
    }
}
