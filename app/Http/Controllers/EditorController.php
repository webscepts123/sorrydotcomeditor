<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Editor;
use App\Models\Movie;

class EditorController extends Controller
{
    public function assignToMovie(Request $request)
    {
        $editor = Editor::find($request->editor_id);
        
        // Attach the editor to "Void Shadow" with a specific task
        $editor->movies()->attach($request->movie_id, [
            'assigned_task' => $request->task // e.g., "Upscaling AI Clips to 4K"
        ]);

        return response()->json(['message' => 'Editor assigned to production!']);
    }

    public function index()
    {
        // Fetch all editors and the number of projects they are assigned to
        $editors = Editor::withCount('projects')->latest()->get();

        return view('editors.index', compact('editors'));
    }

    public function create()
    {
        return view('editors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'required|string',
            'bio' => 'nullable|string',
            // 'access_level' => 'required' // Uncomment if you added this to your DB
        ]);

        Editor::create($validated);

        return redirect()->route('editors.index')->with('success', 'Staff member added to the roster.');
    }

    /**
     * Show the edit form for the staff member.
     */
    public function edit(Editor $editor)
    {
        return view('editors.edit', compact('editor'));
    }

    /**
     * Update the staff member's credentials.
     */
    public function update(Request $request, Editor $editor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:editors,email,' . $editor->id,
            'role' => 'required|string',
        ]);

        $editor->update($validated);

        return redirect()->route('editors.index')
            ->with('success', 'EDITOR IDENTITY UPDATED.');
    }

    /**
     * Terminate the editor record.
     */
    public function destroy(Editor $editor)
    {
        // Detach from projects before deleting
        $editor->projects()->detach();
        $editor->delete();

        return redirect()->route('editors.index')
            ->with('success', 'EDITOR RECORD PURGED FROM SYSTEM.');
    }

    public function show(Editor $editor)
    {
        // Load the projects associated with this editor via the pivot table
        $editor->load('projects');

        return view('editors.show', compact('editor'));
    }
}
