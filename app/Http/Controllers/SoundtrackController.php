<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Soundtrack; // Ensure you include the model

class SoundtrackController extends Controller
{
    public function index()
    {
        // Fetch all tracks from the database, newest first
        $tracks = Soundtrack::latest()->get(); 
        
        return view('soundtracks.index', compact('tracks'));
    }

    public function create()
    {
        return view('soundtracks.create');
    }

    /**
     * Store the uploaded audio file.
     */
    public function store(Request $request)
    {
        // We will build the actual file upload logic here next.
        // For now, it just redirects back to the index.
        return redirect()->route('soundtracks.index')
            ->with('success', 'AUDIO STEM UPLOADED TO VOID SYSTEM.');
    }
}
