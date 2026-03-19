<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trailer;

class TrailerController extends Controller
{
    /**
     * Display a listing of the trailers.
     */
    public function index()
    {
        // Fetch all trailers, eager load the project to prevent N+1 queries
        $trailers = Trailer::with('project')->latest()->paginate(10);
        
        return view('trailers.index', compact('trailers'));
    }

    /**
     * Show the form for creating a new trailer cut.
     */
    public function create()
    {
        $projects = Project::orderBy('title')->get();
        return view('trailers.create', compact('projects'));
    }

    /**
     * Store a newly initialized trailer cut in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration'    => 'nullable|string|max:10',
            'status'      => 'required|in:Draft,Processing,Ready,Published'
        ]);

        Trailer::create($validated);

        return redirect()->route('trailers.index')
            ->with('success', 'PROMOTIONAL ASSET INITIALIZED.');
    }

    /**
     * Display the specific trailer (Dossier / Player view).
     */
    public function show(Trailer $trailer)
    {
        return view('trailers.show', compact('trailer'));
    }

    /**
     * Show the form for editing the trailer settings.
     */
    public function edit(Trailer $trailer)
    {
        $projects = Project::orderBy('title')->get();
        return view('trailers.edit', compact('trailer', 'projects'));
    }

    /**
     * Update the trailer cut in storage.
     */
    public function update(Request $request, Trailer $trailer)
    {
        $validated = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration'    => 'nullable|string|max:10',
            'status'      => 'required|in:Draft,Processing,Ready,Published'
        ]);

        $trailer->update($validated);

        return redirect()->route('trailers.index')
            ->with('success', 'TRAILER CONFIGURATION UPDATED.');
    }

    /**
     * Remove the specified trailer from the system.
     */
    public function destroy(Trailer $trailer)
    {
        $trailer->delete();

        return redirect()->route('trailers.index')
            ->with('success', 'ASSET PURGED FROM SYSTEM.');
    }
}
