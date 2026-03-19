<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Character;

class CharacterController extends Controller
{
    public function index()
    {
        // Use plural '$characters' to match your @foreach($characters as $character)
        $characters = Character::latest()->get();

        return view('characters.index', compact('characters'));
    }

    public function create()
    {
        $characters = Character::latest()->get(); // Add this
        return view('characters.create', compact('characters')); 
    }
    public function show(Character $character)
    {
        // Load the scenes this character appears in so we can list them
        $character->load('scenes');
        
        return view('characters.show', compact('character'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ai_tag' => 'nullable|string|max:100',
            'role' => 'required|string',
            'description' => 'nullable|string',
            
        ]);

        // Handle the Image Upload for the Reference Portrait
        if ($request->hasFile('reference_image')) {
            $path = $request->file('reference_image')->store('characters', 'public');
            $validated['image_path'] = $path;
        }

        Character::create($validated);

        return redirect()->route('characters.index')
            ->with('success', 'Character identity has been locked into the system.');
    }
}
