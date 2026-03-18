<?php

namespace App\Http\Controllers;

use App\Models\Soundtrack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SoundtrackController extends Controller
{
    public function index()
    {
        $tracks = Soundtrack::latest()->get(); 
        return view('soundtracks.index', compact('tracks'));
    }

    public function create()
    {
        return view('soundtracks.create');
    }

    /**
     * 1. STORE: Handle the upload and save to Database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'composer' => 'required|string|max:255',
            'type' => 'required|string',
            'notes' => 'nullable|string',
            'audio_file' => 'required|file|mimes:mp3,wav|max:51200', // 50MB Max
        ]);

        // Handle the physical file upload
        if ($request->hasFile('audio_file')) {
            // Stores in storage/app/public/soundtracks
            $filePath = $request->file('audio_file')->store('soundtracks', 'public');
            
            // Create Database Record
            Soundtrack::create([
                'title' => $validated['title'],
                'composer' => $validated['composer'],
                'type' => $validated['type'],
                'notes' => $validated['notes'],
                'file_path' => $filePath,
            ]);
        }

        return redirect()->route('soundtracks.index')
            ->with('success', 'AUDIO STEM UPLOADED TO VOID SYSTEM.');
    }

    /**
     * 2. EDIT: Show the config screen
     */
    public function edit(Soundtrack $soundtrack)
    {
        return view('soundtracks.edit', compact('soundtrack'));
    }

    /**
     * 3. UPDATE: Save changes and optionally replace the file
     */
    public function update(Request $request, Soundtrack $soundtrack)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'composer' => 'required|string|max:255',
            'type' => 'required|string',
            'notes' => 'nullable|string',
            'audio_file' => 'nullable|file|mimes:mp3,wav|max:51200', // Optional on update
        ]);

        $updateData = [
            'title' => $validated['title'],
            'composer' => $validated['composer'],
            'type' => $validated['type'],
            'notes' => $validated['notes'],
        ];

        // If you uploaded a replacement file...
        if ($request->hasFile('audio_file')) {
            // Delete the old file from the server
            if (Storage::disk('public')->exists($soundtrack->file_path)) {
                Storage::disk('public')->delete($soundtrack->file_path);
            }
            // Upload the new one
            $updateData['file_path'] = $request->file('audio_file')->store('soundtracks', 'public');
        }

        $soundtrack->update($updateData);

        return redirect()->route('soundtracks.index')
            ->with('success', 'AUDIO STEM CONFIGURATION UPDATED.');
    }

    /**
     * 4. DESTROY: Purge the file and record (to make your delete button work)
     */
    public function destroy(Soundtrack $soundtrack)
    {
        if (Storage::disk('public')->exists($soundtrack->file_path)) {
            Storage::disk('public')->delete($soundtrack->file_path);
        }
        
        $soundtrack->delete();

        return redirect()->route('soundtracks.index')
            ->with('success', 'AUDIO STEM PURGED FROM SYSTEM.');
    }
}