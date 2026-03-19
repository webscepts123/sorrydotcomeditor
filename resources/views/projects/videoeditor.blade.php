<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VOID SHADOW EDITOR - {{ $project->title }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Syncopate:wght@400;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --nle-bg: #141414; /* Deeper dark for modern feel */
            --nle-panel: #1c1c1c;
            --nle-border: #2a2a2a;
            --nle-text: #b3b3b3;
            --nle-text-light: #e0e0e0;
            --nle-active: #0dcaf0; /* Cyan Selection */
            --nle-hover: #2d2d2d;
            --nle-audio-green: #28a745;
            --nle-clip-bg: #1f509a; /* Premiere blue */
            --nle-audio-clip-bg: #2a6644; /* Resolve green */
            --nle-danger: #e03a3e;
            --font-xs: 9px;
            --font-sm: 11px;
            --font-md: 12px;
        }

        body, html { margin: 0; padding: 0; width: 100vw; height: 100vh; background-color: var(--nle-bg); color: var(--nle-text); font-family: 'Inter', sans-serif; overflow: hidden; user-select: none; }
        
        /* Typography Helpers */
        .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
        .fw-medium { font-weight: 500; }
        
        .nle-container { display: flex; flex-direction: column; height: 100vh; width: 100vw; }
        
        /* Top Navigation Bar */
        .nle-header { background: #0a0a0a; height: 35px; display: flex; align-items: center; justify-content: space-between; padding: 0 15px; font-size: var(--font-sm); border-bottom: 1px solid #000; }
        .nle-menus { display: flex; gap: 15px; }
        .nle-menu-item { cursor: pointer; color: var(--nle-text); transition: color 0.2s; }
        .nle-menu-item:hover { color: #fff; }
        
        /* Workspace Switcher */
        .workspace-tabs { display: flex; gap: 20px; font-weight: 600; font-size: 10px; letter-spacing: 1px; }
        .workspace-tab { color: #666; cursor: pointer; text-transform: uppercase; }
        .workspace-tab.active { color: #fff; border-bottom: 2px solid var(--nle-active); padding-bottom: 8px; margin-bottom: -9px; }

        /* Main Workspace Splitting */
        .nle-workspace { display: flex; flex: 1; height: calc(60vh - 35px); overflow: hidden; }
        
        /* Panels */
        .nle-panel { background: var(--nle-panel); border-right: 1px solid var(--nle-border); border-bottom: 1px solid var(--nle-border); display: flex; flex-direction: column; }
        .nle-panel-header { background: #222; font-size: var(--font-sm); padding: 8px 12px; border-bottom: 1px solid var(--nle-border); display: flex; justify-content: space-between; align-items: center; font-weight: 600; color: var(--nle-text-light);}
        .nle-panel-content { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 12px; font-size: var(--font-sm); }
        
        /* Panel Widths */
        .panel-properties { width: 340px; min-width: 340px; }
        .panel-playlist { width: 280px; min-width: 280px; }
        .panel-monitor { flex: 1; display: flex; flex-direction: column; background: #000; }
        .panel-audio-meter { width: 180px; min-width: 180px; border-right: none; }

        /* Inspector Tabs */
        .inspector-tabs { display: flex; border-bottom: 1px solid var(--nle-border); background: #181818; }
        .insp-tab { flex: 1; text-align: center; padding: 8px 0; cursor: pointer; font-size: 10px; font-weight: 600; color: #666; border-bottom: 2px solid transparent; text-transform: uppercase;}
        .insp-tab.active { color: var(--nle-active); border-bottom: 2px solid var(--nle-active); background: var(--nle-panel); }
        .insp-content { display: none; }
        .insp-content.active { display: block; }

        /* Forms & Inputs */
        .input-group-custom { display: flex; align-items: center; margin-bottom: 8px; justify-content: space-between; }
        .input-label { width: 40%; font-size: var(--font-sm); color: #aaa; }
        .nle-input, .nle-select, .nle-textarea { background: #111; border: 1px solid #333; color: #ddd; font-size: var(--font-sm); padding: 4px 6px; border-radius: 3px; font-family: 'Inter', sans-serif; transition: border 0.2s;}
        .nle-input:focus, .nle-select:focus, .nle-textarea:focus { outline: none; border-color: var(--nle-active); }
        
        /* Keyframe Diamond */
        .keyframe-icon { color: #555; cursor: pointer; font-size: 10px; margin-left: 8px; transition: color 0.2s; }
        .keyframe-icon:hover, .keyframe-icon.active { color: var(--nle-danger); }

        /* Buttons */
        .nle-btn { background: #2a2a2a; border: 1px solid #3a3a3a; color: #ccc; padding: 6px 12px; font-size: var(--font-sm); cursor: pointer; border-radius: 4px; font-weight: 500; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center;}
        .nle-btn:hover { background: #333; color: #fff; border-color: #555;}
        .nle-btn-primary { background: rgba(13, 202, 240, 0.1); border-color: var(--nle-active); color: var(--nle-active); }
        .nle-btn-primary:hover { background: var(--nle-active); color: #000; }

        /* Timeline Section */
        .nle-timeline-area { height: 40vh; background: var(--nle-bg); border-top: 1px solid #000; display: flex; flex-direction: column; }
        
        /* Advanced Timeline Toolbar */
        .timeline-toolbar { height: 38px; background: #181818; border-bottom: 1px solid var(--nle-border); display: flex; align-items: center; padding: 0 15px; gap: 10px; font-size: 14px; }
        .tool-icon { width: 26px; height: 26px; display: flex; align-items: center; justify-content: center; border-radius: 4px; cursor: pointer; color: #888; transition: all 0.2s; }
        .tool-icon:hover { background: var(--nle-hover); color: #fff; }
        .tool-icon.active { background: #2a2a2a; color: var(--nle-active); }
        .tool-divider { width: 1px; height: 16px; background: #333; margin: 0 5px; }

        .timeline-ruler { height: 22px; background: #1c1c1c; border-bottom: 1px solid var(--nle-border); display: flex; font-size: var(--font-xs); color: #777; padding-left: 150px; font-family: monospace; }
        .timeline-ruler-tick { flex: 1; border-left: 1px solid #333; padding-left: 4px; display: flex; flex-direction: column; justify-content: flex-end; padding-bottom: 2px;}
        
        .timeline-tracks-container { display: flex; flex: 1; overflow: auto; position: relative; }
        .timeline-headers { width: 150px; background: #1a1a1a; position: sticky; left: 0; z-index: 10; border-right: 1px solid var(--nle-border); box-shadow: 2px 0 5px rgba(0,0,0,0.2); }
        
        /* Track Headers with Mute/Solo */
        .track-header { height: 60px; border-bottom: 1px solid var(--nle-border); padding: 8px; display: flex; flex-direction: column; justify-content: space-between; font-size: 11px; }
        .track-controls { display: flex; gap: 4px; margin-top: 4px; }
        .btn-track { background: #222; border: 1px solid #333; color: #888; font-size: 9px; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; border-radius: 2px; cursor: pointer; font-weight: bold;}
        .btn-track.m-active { background: var(--nle-danger); color: #fff; border-color: var(--nle-danger); }
        .btn-track.s-active { background: #ffc107; color: #000; border-color: #ffc107; }
        
        .timeline-grid { flex: 1; background: #111; display: flex; flex-direction: column; position: relative; min-width: 3000px; }
        .track-row { height: 60px; border-bottom: 1px solid #222; display: flex; align-items: center; padding-left: 2px; position: relative; }
        
        .clip-block { height: 50px; margin-right: 2px; border-radius: 3px; font-size: 10px; padding: 4px; overflow: hidden; position: relative; color: #fff; cursor: pointer; text-decoration: none; box-shadow: inset 0 1px 0 rgba(255,255,255,0.2);}
        .clip-video { background: var(--nle-clip-bg); border: 1px solid #10386b; }
        .clip-audio { background: var(--nle-audio-clip-bg); border: 1px solid #1a422b; }
        .clip-active { border: 1px solid #fff; box-shadow: 0 0 0 1px #fff, inset 0 1px 0 rgba(255,255,255,0.4); }
        
        /* Clip Opacity/Volume Lines */
        .clip-line { position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: rgba(255,255,255,0.5); pointer-events: none; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: #141414; }
        ::-webkit-scrollbar-thumb { background: #333; border-radius: 5px; border: 2px solid #141414; }
        ::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>
</head>
<body>

<div class="nle-container">
    
    <div class="nle-header">
        <div class="nle-menus">
            <span class="fw-bold text-white me-3" style="font-family: 'Syncopate'; letter-spacing: 1px;">VOID SHADOW  <span class="text-info">EDITOR</span></span>
            <span class="nle-menu-item">File</span>
            <span class="nle-menu-item">Edit</span>
            <span class="nle-menu-item">Trim</span>
            <span class="nle-menu-item">Timeline</span>
            <span class="nle-menu-item">Color</span>
            <span class="nle-menu-item">Playback</span>
            <span class="nle-menu-item">Help</span>
        </div>
        
        <div class="workspace-tabs">
            <div class="workspace-tab">Media</div>
            <div class="workspace-tab">Cut</div>
            <div class="workspace-tab active">Edit</div>
            <div class="workspace-tab">Color</div>
            <div class="workspace-tab">Fairlight</div>
            <div class="workspace-tab">Deliver</div>
        </div>

        <div>
            <a href="{{ route('projects.export-xml', $project) }}" class="btn btn-sm btn-outline-info rounded-1 py-0 px-2" style="font-size: 10px;">Export XML</a>
            <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-danger rounded-1 py-0 px-2 ms-2 fw-bold" style="font-size: 10px;">EXIT</a>
        </div>
    </div>

    <div class="nle-workspace">
        
        @php $activeSceneId = request('active_scene'); @endphp
        @php $currentScene = $activeSceneId ? $project->scenes->firstWhere('id', $activeSceneId) : null; @endphp

        <div class="nle-panel panel-properties">
            <div class="nle-panel-header">
                <span>Inspector</span>
                <div class="d-flex gap-2 text-secondary">
                    <i class="bi bi-pin-angle"></i>
                    <i class="bi bi-three-dots"></i>
                </div>
            </div>
            
            <div class="inspector-tabs">
                <div class="insp-tab active" onclick="switchInspTab('video')">Video</div>
                <div class="insp-tab" onclick="switchInspTab('audio')">Audio</div>
                <div class="insp-tab" onclick="switchInspTab('ai')"><i class="bi bi-cpu-fill me-1"></i>Engine</div>
            </div>

            <div class="nle-panel-content custom-scrollbar">
                @if($currentScene)
                    
                    <div id="insp-video" class="insp-content active">
                        <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-secondary">
                            <div class="form-check form-switch m-0 me-2">
                                <input class="form-check-input bg-black border-secondary" type="checkbox" checked>
                            </div>
                            <span class="fw-bold text-white small">Transform</span>
                            <i class="bi bi-arrow-counterclockwise ms-auto text-secondary" style="cursor:pointer; font-size:10px;"></i>
                        </div>

                        <div class="input-group-custom">
                            <label class="input-label">Zoom</label>
                            <div class="d-flex align-items-center" style="width: 55%;">
                                <input type="number" class="nle-input m-0" value="1.000" step="0.001">
                                <i class="bi bi-link-45deg mx-1 text-secondary"></i>
                                <input type="number" class="nle-input m-0" value="1.000" step="0.001">
                            </div>
                            <i class="bi bi-diamond keyframe-icon" onclick="this.classList.toggle('active')"></i>
                        </div>
                        
                        <div class="input-group-custom">
                            <label class="input-label">Position</label>
                            <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                <input type="number" class="nle-input m-0" value="0.00">
                                <input type="number" class="nle-input m-0" value="0.00">
                            </div>
                            <i class="bi bi-diamond keyframe-icon" onclick="this.classList.toggle('active')"></i>
                        </div>

                        <div class="input-group-custom">
                            <label class="input-label">Rotation Angle</label>
                            <div class="d-flex align-items-center" style="width: 55%;">
                                <input type="number" class="nle-input m-0 w-100" value="0.00">
                            </div>
                            <i class="bi bi-diamond keyframe-icon" onclick="this.classList.toggle('active')"></i>
                        </div>

                        <div class="d-flex align-items-center mt-4 mb-3 pb-2 border-bottom border-secondary">
                            <div class="form-check form-switch m-0 me-2">
                                <input class="form-check-input bg-black border-secondary" type="checkbox" checked>
                            </div>
                            <span class="fw-bold text-white small">Composite</span>
                        </div>
                        
                        <div class="input-group-custom">
                            <label class="input-label">Composite Mode</label>
                            <select class="nle-select m-0" style="width: 55%;">
                                <option>Normal</option>
                                <option>Add</option>
                                <option>Multiply</option>
                                <option>Screen</option>
                                <option>Overlay</option>
                            </select>
                            <i class="bi bi-diamond keyframe-icon" style="opacity:0;"></i>
                        </div>
                        <div class="input-group-custom mt-2">
                            <label class="input-label">Opacity</label>
                            <div class="d-flex align-items-center" style="width: 55%;">
                                <input type="range" class="form-range me-2" value="100">
                                <span class="text-white" style="font-size:10px;">100%</span>
                            </div>
                            <i class="bi bi-diamond keyframe-icon" onclick="this.classList.toggle('active')"></i>
                        </div>
                    </div>

                    <div id="insp-audio" class="insp-content">
                        <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-secondary">
                            <div class="form-check form-switch m-0 me-2">
                                <input class="form-check-input bg-black border-secondary" type="checkbox" checked>
                            </div>
                            <span class="fw-bold text-white small">Volume</span>
                        </div>
                        
                        <div class="input-group-custom">
                            <label class="input-label">Clip Volume</label>
                            <div class="d-flex align-items-center gap-2" style="width: 55%;">
                                <input type="range" class="form-range" min="-24" max="12" value="0">
                                <span class="text-white" style="font-size:10px; width:30px;">0 dB</span>
                            </div>
                            <i class="bi bi-diamond keyframe-icon" onclick="this.classList.toggle('active')"></i>
                        </div>

                        <div class="alert bg-dark border-secondary p-2 mt-4 text-secondary" style="font-size: 10px;">
                            <i class="bi bi-magic text-info me-1"></i> <strong>AI Stabilizer Active.</strong> Auto-normalizing dialogue to -6dB.
                        </div>
                    </div>

                    <div id="insp-ai" class="insp-content">
                        <form id="nle-engine-form" action="{{ route('scenes.update', $currentScene) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            
                            <label class="text-info fw-bold mb-2 uppercase" style="font-size: 9px; letter-spacing:1px;">Seedance Generation Logic</label>
                            
                            <label class="input-label mb-1">Target Action Prompt</label>
                            <textarea name="script_segment" class="nle-textarea" rows="4">{{ $currentScene->script_segment }}</textarea>
                            
                            <label class="input-label mb-1 mt-2">Negative Exclusions</label>
                            <textarea name="negative_prompt" class="nle-textarea border-secondary text-danger" rows="2" placeholder="blur, text, watermark..."></textarea>

                            <div class="mt-3 pt-3 border-top border-secondary">
                                <label class="input-label mb-1">Camera Motion</label>
                                <select name="camera_motion" class="nle-select">
                                    <option value="static">Static Locked-off</option>
                                    <option value="pan_left">Slow Pan Left</option>
                                    <option value="drone">Drone Tracking</option>
                                </select>
                            </div>

                            <div class="input-group-custom mt-2">
                                <label class="input-label">Motion Intensity</label>
                                <div class="d-flex align-items-center" style="width: 55%;">
                                    <input type="range" name="motion_intensity" class="form-range me-2" min="1" max="10" value="5">
                                    <span class="text-white" style="font-size:10px;">5</span>
                                </div>
                            </div>

                            <div class="mt-2">
                                <label class="input-label mb-1">Lighting & Atmosphere</label>
                                <select name="lighting_style" class="nle-select">
                                    <option value="cinematic">Cinematic / Volumetric</option>
                                    <option value="neon">Cyberpunk Neon</option>
                                    <option value="natural">Natural Daylight</option>
                                    <option value="noir">Moody Noir (High Contrast)</option>
                                </select>
                            </div>

                            <div class="mt-2">
                                <label class="input-label mb-1">Render Quality</label>
                                <select name="render_quality" class="nle-select">
                                    <option value="standard">Standard (1080p, 24fps)</option>
                                    <option value="high">High (4K, 24fps)</option>
                                    <option value="ultra">Ultra (4K, 60fps, Upscaled)</option>
                                </select>
                            </div>

                            <div class="input-group-custom mt-2">
                                <label class="input-label">Generation Seed</label>
                                <input type="text" name="generation_seed" class="nle-input m-0" style="width: 55%;" placeholder="Random (-1)" value="-1">
                            </div>

                            <div class="d-flex align-items-center mt-3 mb-1">
                                <div class="form-check form-switch m-0 me-2">
                                    <input class="form-check-input bg-black border-secondary" type="checkbox" name="enhance_faces" checked>
                                </div>
                                <span class="text-white small">AI Face Enhancement</span>
                            </div>
                            <button type="submit" class="nle-btn w-100 mt-3 mb-2"><i class="bi bi-save me-2"></i>Save Node Config</button>
                        </form>

                        <form action="{{ route('scenes.render', $currentScene) }}" method="POST" class="mt-3 pt-3 border-top border-secondary">
                            @csrf
                            <button type="submit" class="nle-btn nle-btn-primary w-100 py-2 fw-bold"><i class="bi bi-cpu-fill me-2"></i>Render AI Clip</button>
                        </form>
                    </div>

                @else
                    <div class="text-center text-secondary mt-5 pt-5">
                        <i class="bi bi-cursor fs-1 d-block mb-3"></i>
                        Select a clip in the timeline to inspect properties.
                    </div>
                @endif
            </div>
        </div>

        <div class="nle-panel panel-playlist border-end">
            <div class="nle-panel-header">
                <span>Media Pool</span>
                <div class="d-flex gap-3 text-secondary" style="font-size: 12px;">
                    <i class="bi bi-search"></i>
                    <i class="bi bi-grid-fill text-white"></i>
                    <i class="bi bi-list-ul"></i>
                </div>
            </div>
            
            <div style="background: #181818; padding: 5px 10px; font-size: 10px; border-bottom: 1px solid var(--nle-border); display:flex; align-items:center;">
                <i class="bi bi-folder-fill text-secondary me-2"></i> Master <i class="bi bi-chevron-right mx-1 text-secondary" style="font-size:8px;"></i> Rendered Clips
            </div>

            <div class="nle-panel-content" style="padding: 8px;">
                <div class="row g-2">
                    @forelse($project->scenes->sortBy('order_index') as $scene)
                        <div class="col-6">
                            <a href="{{ route('projects.videoeditor', $project->id) }}?active_scene={{ $scene->id }}" 
                               class="text-decoration-none d-block" style="border: 1px solid {{ request('active_scene') == $scene->id ? 'var(--nle-active)' : 'transparent' }}; background: #1a1a1a; padding: 3px; transition: all 0.2s; border-radius: 4px;">
                                <div style="aspect-ratio: 16/9; background: #000; position: relative; overflow: hidden; display: flex; align-items:center; justify-content:center; border-radius: 2px;">
                                    @if($scene->video_path)
                                        <video src="{{ asset('storage/'.$scene->video_path) }}" style="width: 100%; pointer-events:none;"></video>
                                    @else
                                        <i class="bi bi-camera-reels" style="color:#444; font-size: 24px;"></i>
                                    @endif
                                    <div style="position: absolute; bottom:0; left:0; width:100%; height:2px; background:rgba(255,255,255,0.1);"></div>
                                </div>
                                <div style="font-size: 10px; color: #ccc; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding: 4px 2px 0 2px;">
                                    SEQ_{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}.mp4
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-12 text-center text-secondary py-4 small">Bin Empty.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="panel-monitor position-relative border-right border-secondary" style="border-right: 1px solid var(--nle-border);">
            
            <div style="height: 30px; background: #111; display:flex; justify-content: space-between; align-items: center; padding: 0 15px; font-size: 10px; color: #888;">
                <div class="d-flex gap-3">
                    <span class="text-white">Fit <i class="bi bi-chevron-down ms-1" style="font-size:8px;"></i></span>
                    <span>1920x1080</span>
                </div>
                <div class="d-flex gap-3">
                    <i class="bi bi-aspect-ratio cursor-pointer hover-white"></i>
                    <i class="bi bi-grid cursor-pointer hover-white"></i>
                    <i class="bi bi-arrows-fullscreen cursor-pointer hover-white"></i>
                </div>
            </div>

            <div style="flex: 1; display: flex; align-items: center; justify-content: center; background: #000; position: relative; overflow: hidden;">
                @if($currentScene && $currentScene->video_path)
                    <video id="mainPlayer" src="{{ asset('storage/'.$currentScene->video_path) }}" class="w-100 h-100 object-fit-contain"></video>
                @elseif($currentScene)
                    <div class="text-center text-warning" style="font-family: monospace;">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c2/SMPTE_Color_Bars.svg" style="width:100%; height:100%; position:absolute; top:0; left:0; opacity:0.3; object-fit:cover;">
                        <div style="position:relative; z-index:2; background:rgba(0,0,0,0.7); padding: 10px;">
                            <i class="bi bi-exclamation-triangle fs-2 d-block mb-2"></i>
                            MEDIA OFFLINE<br>
                            <span class="text-white mt-1 d-block" style="font-size: 10px;">Render In Progress</span>
                        </div>
                    </div>
                @else
                    <div class="text-center text-secondary" style="opacity: 0.2;">
                        <i class="bi bi-film" style="font-size: 80px;"></i>
                    </div>
                @endif
            </div>

            <div style="height: 45px; background: #111; border-top: 1px solid #222; display: flex; align-items: center; justify-content: center; gap: 25px; font-size: 16px; color: #aaa;">
                <span style="font-family: 'Courier New', monospace; font-size: 14px; position: absolute; left: 15px; color: var(--nle-active);">01:00:00:00</span>
                
                <i class="bi bi-skip-start-fill" style="cursor: pointer;"></i>
                <i class="bi bi-caret-left-fill" style="cursor: pointer; font-size: 14px;"></i>
                
                <i id="playPauseBtn" class="bi bi-play-fill text-white fs-2" style="cursor: pointer; transition: color 0.1s;" onclick="togglePlay()"></i>
                
                <i class="bi bi-caret-right-fill" style="cursor: pointer; font-size: 14px;"></i>
                <i class="bi bi-skip-end-fill" style="cursor: pointer;"></i>
                
                <div style="position: absolute; right: 15px; display:flex; gap: 15px; font-size: 10px; color: #666;">
                    <i class="bi bi-arrow-repeat hover-white cursor-pointer"></i>
                    <i class="bi bi-volume-up hover-white cursor-pointer"></i>
                </div>
            </div>
        </div>

        <div class="nle-panel panel-audio-meter">
            <div class="nle-panel-header">
                <span>Mixer</span>
                <i class="bi bi-gear" style="font-size:10px; color:#666;"></i>
            </div>
            <div class="nle-panel-content d-flex flex-column" style="padding: 0;">
                
                <div style="flex: 1; padding: 15px 10px; border-bottom: 1px solid var(--nle-border); display: flex; flex-direction: column; align-items: center; background: #141414;">
                    <span class="text-secondary mb-3 fw-bold" style="font-size: 9px; letter-spacing: 1px;">M1</span>
                    
                    <div style="flex: 1; display: flex; gap: 6px; width: 100%; justify-content: center; position: relative;">
                        <div style="font-size: 8px; color: #555; font-family: monospace; display: flex; flex-direction: column; justify-content: space-between; height: 100%; padding-right: 5px;">
                            <span>0</span><span>-5</span><span>-10</span><span>-20</span><span>-30</span><span>-40</span>
                        </div>
                        
                        <div style="width: 14px; background: #0a0a0a; position: relative; border: 1px solid #222;">
                            <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 78%; background: linear-gradient(to top, var(--nle-audio-green) 65%, #ffc107 85%, #dc3545 100%);"></div>
                            <div style="position:absolute; top: 18%; width:100%; height:2px; background:#fff;"></div>
                        </div>
                        
                        <div style="width: 14px; background: #0a0a0a; position: relative; border: 1px solid #222;">
                            <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 72%; background: linear-gradient(to top, var(--nle-audio-green) 65%, #ffc107 85%, #dc3545 100%);"></div>
                            <div style="position:absolute; top: 22%; width:100%; height:2px; background:#fff;"></div>
                        </div>
                    </div>
                </div>

                <div style="height: 120px; padding: 10px; background: var(--nle-panel);">
                    <div class="mb-2 text-secondary pb-1 uppercase" style="font-size:9px; border-bottom:1px solid #333;">Render Queue</div>
                    @foreach($project->scenes->where('status', 'Processing') as $job)
                        <div class="d-flex align-items-center mb-2 text-warning" style="font-size: 10px; font-family: monospace;">
                            <span class="spinner-grow spinner-grow-sm me-2" style="width: 8px; height: 8px;"></span> 
                            Render_{{ $job->id }}
                        </div>
                    @endforeach
                    @if($project->scenes->where('status', 'Processing')->count() == 0)
                        <div class="text-muted" style="font-size: 9px; font-style: italic;">No active jobs.</div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <div class="nle-timeline-area">
        
        <div class="timeline-toolbar">
            <div class="tool-icon active" onclick="activateTool(this)" title="Selection Mode (A)"><i class="bi bi-cursor-fill"></i></div>
            <div class="tool-icon" onclick="activateTool(this)" title="Blade / Razor (B)"><i class="bi bi-scissors"></i></div>
            <div class="tool-icon" onclick="activateTool(this)" title="Slip Edit (S)"><i class="bi bi-arrows-expand"></i></div>
            <div class="tool-divider"></div>
            <div class="tool-icon active" onclick="this.classList.toggle('active')" title="Snapping (N)"><i class="bi bi-magnet-fill"></i></div>
            <div class="tool-icon active" onclick="this.classList.toggle('active')" title="Linked Selection"><i class="bi bi-link"></i></div>
            <div class="tool-icon" title="Add Marker (M)"><i class="bi bi-bookmark-fill"></i></div>
            
            <div class="ms-auto d-flex align-items-center gap-2">
                <i class="bi bi-zoom-out text-secondary" style="font-size: 14px;"></i>
                <input type="range" class="form-range" style="width: 150px; height: 2px;" value="50">
                <i class="bi bi-zoom-in text-white" style="font-size: 14px;"></i>
            </div>
        </div>

        <div class="timeline-ruler">
            <div class="timeline-ruler-tick">01:00:00:00</div>
            <div class="timeline-ruler-tick">01:00:05:00</div>
            <div class="timeline-ruler-tick">01:00:10:00</div>
            <div class="timeline-ruler-tick">01:00:15:00</div>
            <div class="timeline-ruler-tick">01:00:20:00</div>
            <div class="timeline-ruler-tick">01:00:25:00</div>
            <div class="timeline-ruler-tick">01:00:30:00</div>
        </div>

        <div class="timeline-tracks-container custom-scrollbar">
            
            <div class="timeline-headers">
                <div class="track-header">
                    <span class="fw-bold">V2</span>
                    <div class="track-controls">
                        <div class="btn-track" onclick="this.classList.toggle('m-active')" title="Mute Track">M</div>
                        <i class="bi bi-lock ms-auto mt-1" style="font-size:10px; color:#555;"></i>
                    </div>
                </div>
                <div class="track-header" style="background: #252525;"> 
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold text-white">V1 <span class="text-secondary" style="font-weight:normal;">(Master)</span></span>
                        <i class="bi bi-eye text-white"></i>
                    </div>
                    <div class="track-controls">
                        <div class="btn-track" onclick="this.classList.toggle('m-active')">M</div>
                    </div>
                </div>
                
                <div class="track-header" style="background: #252525; height: 70px;">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold" style="color: var(--nle-audio-green);">A1 <span class="text-secondary" style="font-weight:normal;">(Score)</span></span>
                    </div>
                    <div class="track-controls">
                        <div class="btn-track" onclick="this.classList.toggle('m-active')">M</div>
                        <div class="btn-track" onclick="this.classList.toggle('s-active')" title="Solo Track">S</div>
                    </div>
                </div>
            </div>

            <div class="timeline-grid">
                
                <div style="position: absolute; left: 240px; top: 0; bottom: 0; width: 1px; background: var(--nle-danger); z-index: 50;">
                    <div style="position: absolute; top: -10px; left: -5px; border-left: 5px solid transparent; border-right: 5px solid transparent; border-top: 10px solid var(--nle-danger);"></div>
                </div>

                <div class="track-row"></div> <div class="track-row">
                    @foreach($project->scenes->sortBy('order_index') as $scene)
                        <a href="{{ route('projects.videoeditor', $project->id) }}?active_scene={{ $scene->id }}" 
                           class="clip-block clip-video {{ request('active_scene') == $scene->id ? 'clip-active' : '' }}" 
                           style="width: 180px;">
                           @if($scene->video_path)
                                <div style="position: absolute; top:0; left:0; bottom:0; width: 50px; background-image: url('{{ asset('storage/'.$scene->video_path) }}'); background-size: cover; border-right: 1px solid rgba(0,0,0,0.5);"></div>
                           @endif
                           <span style="position:relative; z-index: 2; padding-left: 55px; font-family: 'Inter', sans-serif; font-weight:500;">SEQ_0{{ $scene->order_index }}</span>
                           <div class="clip-line"></div> </a>
                    @endforeach
                </div>

                <div class="track-row" style="height: 70px; background: rgba(40, 167, 69, 0.03);">
                    @foreach($project->scenes->sortBy('order_index') as $scene)
                        <div class="clip-block clip-audio" style="width: 180px; height: 60px; position: relative;">
                           <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 60%; border-top: 1px solid rgba(255,255,255,0.1); background-image: repeating-linear-gradient(90deg, transparent, transparent 3px, rgba(255,255,255,0.2) 3px, rgba(255,255,255,0.2) 4px);"></div>
                           <span style="position:relative; z-index: 2; color: #b0d4b8;">A_0{{ $scene->order_index }} [M]</span>
                           <div class="clip-line" style="top: 30%;"></div> </div>
                    @endforeach
                </div>

            </div>
        </div>

    </div>
</div>

<script>
    // 1. Switch Inspector Tabs
    function switchInspTab(tabName) {
        // Remove active class from all tabs and contents
        document.querySelectorAll('.insp-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.insp-content').forEach(c => c.classList.remove('active'));
        
        // Add active class to clicked
        event.target.classList.add('active');
        document.getElementById('insp-' + tabName).classList.add('active');
    }

    // 2. Toggle Timeline Tools (Only one active at a time)
    function activateTool(element) {
        document.querySelectorAll('.tool-icon').forEach(t => {
            // Don't remove active from magnet/link toggles, only the main tools
            if(!t.hasAttribute('onclick') || t.getAttribute('onclick').includes('toggle')) return;
            t.classList.remove('active');
        });
        element.classList.add('active');
    }

    // 3. Play/Pause Toggle for the Monitor
    function togglePlay() {
        const btn = document.getElementById('playPauseBtn');
        const video = document.getElementById('mainPlayer');
        
        if (btn.classList.contains('bi-play-fill')) {
            btn.classList.remove('bi-play-fill');
            btn.classList.add('bi-pause-fill');
            btn.style.color = '#0dcaf0';
            if(video) video.play();
        } else {
            btn.classList.remove('bi-pause-fill');
            btn.classList.add('bi-play-fill');
            btn.style.color = '#fff';
            if(video) video.pause();
        }
    }
</script>

</body>
</html>