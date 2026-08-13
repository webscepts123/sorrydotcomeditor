<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Project;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

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
            'aspect_ratio' => 'required|string|max:20',
            'style_preset' => 'required|string|max:100',
        ]);

        // Create the project under the LOGGED-IN user
        $project = Auth::user()->projects()->create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'aspect_ratio' => $validated['aspect_ratio'],
            'style_preset' => $validated['style_preset'],
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
        // Eager load scenes strictly ordered by their timeline index
        // We removed 'scenes.videoClips' because we use video_path on the scene itself
        $project->load(['scenes' => function($query) {
            $query->orderBy('order_index', 'asc');
        }]);
    
        // Calculate total rendered clips (scenes that have a video_path)
        $totalClips = $project->scenes->whereNotNull('video_path')->count();
        
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

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'aspect_ratio' => 'required|string|max:20',
            'style_preset' => 'required|string|max:100',
            'status' => 'required|in:draft,generating,stitching,completed,failed',
        ]);

        $project->update([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'description' => $validated['description'] ?? null,
            'aspect_ratio' => $validated['aspect_ratio'],
            'style_preset' => $validated['style_preset'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project configuration updated.');
    }

    public function editor(Project $project)
    {
        // Eager load scenes and characters for the editor view
        $project->load(['scenes', 'characters']);
        
        return view('projects.editor', compact('project'));
    }

    public function videoeditor(Project $project)
    {
        // Eager load scenes and characters for the editor view
        $project->load([
            'scenes' => function ($query) {
                $query->orderBy('order_index');
            },
            'characters'
        ]);
        
        // This looks for resources/views/projects/videoeditor.blade.php
        return view('projects.videoeditor', compact('project'));
    }

    public function renderBatch(Project $project)
    {
        $project->scenes()
            ->whereIn('status', ['Draft', 'Ready', 'failed'])
            ->update(['status' => 'Processing']);

        return back()->with('success', 'Batch render queued for this project.');
    }

    public function exportXml(Project $project)
    {
        $project->load(['scenes' => function ($query) {
            $query->orderBy('order_index');
        }]);

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><project/>');
        $xml->addChild('title', htmlspecialchars($project->title));
        $xml->addChild('aspect_ratio', htmlspecialchars($project->aspect_ratio ?? '16:9'));
        $timeline = $xml->addChild('timeline');

        foreach ($project->scenes as $scene) {
            $clip = $timeline->addChild('clip');
            $clip->addAttribute('id', (string) $scene->id);
            $clip->addAttribute('order', (string) $scene->order_index);
            $clip->addChild('status', htmlspecialchars($scene->status ?? 'Draft'));
            $clip->addChild('script', htmlspecialchars($scene->script_segment ?? ''));
            $clip->addChild('video_path', htmlspecialchars($scene->video_path ?? ''));
        }

        $fileName = Str::slug($project->title) . '-timeline.xml';

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function downloadVideo(Request $request, Project $project, string $quality)
    {
        $qualityMap = [
            'original' => null,
            '360p' => 360,
            '480p' => 480,
            '720p' => 720,
            '1080p' => 1080,
            '2k' => 1440,
            '4k' => 2160,
            '8k' => 4320,
        ];

        abort_unless(array_key_exists($quality, $qualityMap), 404);

        $project->load(['scenes' => function ($query) {
            $query->orderBy('order_index');
        }]);

        $scene = $request->filled('scene_id')
            ? $project->scenes->firstWhere('id', (int) $request->query('scene_id'))
            : $project->scenes->firstWhere(fn ($scene) => filled($scene->video_path));

        if (!$scene || !$scene->video_path || !Storage::disk('public')->exists($scene->video_path)) {
            return back()->with('error', 'No rendered video is attached to the selected scene.');
        }

        $sourcePath = Storage::disk('public')->path($scene->video_path);
        $sourceExtension = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'mp4';
        $downloadName = Str::slug($project->title)
            . '-seq-' . str_pad((string) $scene->order_index, 3, '0', STR_PAD_LEFT)
            . '-' . $quality . '.' . ($quality === 'original' ? $sourceExtension : 'mp4');

        if ($quality === 'original') {
            return response()->download($sourcePath, $downloadName);
        }

        $ffmpegPath = trim((string) shell_exec('where ffmpeg 2>NUL'));

        if ($ffmpegPath === '') {
            return back()->with('error', 'FFmpeg is not installed. Install FFmpeg to export 360p, 480p, 720p, 1080p, 2K, 4K, or 8K versions.');
        }

        $ffmpegExecutable = preg_split('/\r\n|\r|\n/', $ffmpegPath)[0] ?? 'ffmpeg';

        $height = $qualityMap[$quality];
        $exportDir = storage_path('app/public/exports');

        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0755, true);
        }

        $outputPath = $exportDir . DIRECTORY_SEPARATOR . pathinfo($downloadName, PATHINFO_FILENAME) . '.mp4';

        if (!file_exists($outputPath) || filemtime($outputPath) < filemtime($sourcePath)) {
            $process = new Process([
                $ffmpegExecutable,
                '-y',
                '-i',
                $sourcePath,
                '-vf',
                "scale=-2:{$height}",
                '-c:v',
                'libx264',
                '-preset',
                'medium',
                '-crf',
                $height >= 2160 ? '18' : '20',
                '-c:a',
                'aac',
                '-b:a',
                '192k',
                $outputPath,
            ]);

            $process->setTimeout(1800);
            $process->run();

            if (!$process->isSuccessful()) {
                return back()->with('error', 'Video export failed: ' . Str::limit($process->getErrorOutput(), 180));
            }
        }

        return response()->download($outputPath, $downloadName);
    }

    public function create()
    {
        // This looks for resources/views/projects/create.blade.php
        return view('projects.create');
    }

}
