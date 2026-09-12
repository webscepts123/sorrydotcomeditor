@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container-fluid timeline-container p-0">
    <!-- Top Action & Navigation Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-danger rounded-0 small tracking-widest text-uppercase" style="font-size: 8px;">LIVE TIMELINE</span>
                <span class="text-secondary small tracking-widest text-uppercase" style="font-size: 10px;">· {{ $project->scenes->count() }} SCENE{{ $project->scenes->count() == 1 ? '' : 'S' }} TOTAL</span>
            </div>
            <h2 class="fw-light tracking-widest mb-0 text-white" style="font-family: 'Syncopate', sans-serif;">{{ strtoupper($project->title) }}</h2>
            
            <div class="d-flex align-items-center gap-2 mt-2">
                <label for="timeline-project-selector" class="text-secondary tracking-widest text-uppercase mb-0" style="font-size: 9px;">PROJECT:</label>
                <select id="timeline-project-selector" class="form-select form-select-sm bg-black border-0 border-bottom border-secondary rounded-0 text-white px-2 py-0 tracking-widest" style="max-width: 260px; font-size: 11px;" onchange="if(this.value) window.location.href=this.value;">
                    @foreach($timelineProjects as $timelineProject)
                        <option value="{{ route('projects.timeline', $timelineProject) }}" {{ $timelineProject->id === $project->id ? 'selected' : '' }}>
                            {{ strtoupper($timelineProject->title) }}{{ $timelineProject->id === $project->id ? ' (CURRENT)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Middle: View Switcher -->
        <div class="view-mode-selector bg-black border border-secondary p-1 d-flex gap-1">
            <button type="button" class="btn btn-sm rounded-0 mode-btn active" data-mode="single" title="Single Scene Cinema Inspector (Press 1)">
                <i class="bi bi-display me-1"></i> <span class="d-none d-md-inline tracking-widest">SINGLE FOCUS</span>
            </button>
            <button type="button" class="btn btn-sm rounded-0 mode-btn" data-mode="sequencer" title="Horizontal Multi-Track Reel (Press 2)">
                <i class="bi bi-film me-1"></i> <span class="d-none d-md-inline tracking-widest">SEQUENCER</span>
            </button>
            <button type="button" class="btn btn-sm rounded-0 mode-btn" data-mode="grid" title="Storyboard Grid (Press 3)">
                <i class="bi bi-grid-3x3-gap me-1"></i> <span class="d-none d-md-inline tracking-widest">GRID</span>
            </button>
        </div>

        <!-- Right: Action Buttons -->
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('projects.videoeditor', $project) }}" class="btn btn-outline-info rounded-0 px-3 py-2 small tracking-widest text-uppercase">
                <i class="bi bi-film me-1"></i> NLE EDITOR
            </a>
            <a href="{{ route('scenes.create', ['project' => $project->id]) }}" class="btn btn-outline-light rounded-0 px-3 py-2 small tracking-widest text-uppercase">
                <i class="bi bi-plus-lg me-1"></i> ADD SCENE
            </a>
            <form action="{{ route('projects.render-batch', $project) }}" method="POST" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<span class=\'spinner-border spinner-border-sm me-1\'></span> QUEUEING...';">
                @csrf
                <button type="submit" class="btn btn-white bg-white text-black rounded-0 px-3 py-2 fw-bold small tracking-widest text-uppercase" {{ $project->scenes->isEmpty() ? 'disabled' : '' }}>
                    <i class="bi bi-cpu me-1"></i> BATCH RENDER
                </button>
            </form>
        </div>
    </div>

    @if($project->scenes->isEmpty())
        <!-- Empty State -->
        <div class="timeline-empty border border-dashed border-secondary text-center bg-black p-5 my-4">
            <i class="bi bi-film text-secondary d-block mb-3" style="font-size: 3.5rem;"></i>
            <h4 class="text-white tracking-widest text-uppercase">{{ $project->title }} Timeline Is Empty</h4>
            <p class="text-secondary small mb-4 mx-auto" style="max-width: 500px;">
                No scenes have been storyboarded yet. Add your first scene segment to unlock the AI cinema director monitor and video sequence pipeline.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <a href="{{ route('scenes.create', ['project' => $project->id]) }}" class="btn btn-white bg-white text-black rounded-0 px-4 py-2 fw-bold small tracking-widest text-uppercase">
                    <i class="bi bi-plus-circle me-1"></i> Create First Scene
                </a>
                @php($projectWithScenes = \App\Models\Project::where('user_id', auth()->id())->whereHas('scenes')->whereKeyNot($project->id)->latest('updated_at')->first())
                @if($projectWithScenes)
                    <a href="{{ route('projects.timeline', $projectWithScenes) }}" class="btn btn-outline-info rounded-0 px-4 py-2 small tracking-widest text-uppercase">
                        Open {{ $projectWithScenes->title }}
                    </a>
                @endif
            </div>
        </div>
    @else

        <!-- ================================================================= -->
        <!-- 1. SINGLE FOCUS / DIRECTOR MONITOR VIEW (Default Modern Interactive) -->
        <!-- ================================================================= -->
        <div id="single-view-container" class="timeline-view-pane active">
            <div class="row g-4">
                <!-- Main Cinema Monitor Area -->
                <div class="col-xl-8 col-lg-7">
                    <div class="monitor-console bg-black border border-secondary position-relative">
                        <!-- Monitor HUD Header -->
                        <div class="monitor-hud-header d-flex justify-content-between align-items-center px-3 py-2 border-bottom border-secondary bg-dark-gradient flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="status-indicator-dot" id="monitorStatusDot"></span>
                                <span class="font-monospace text-info small fw-bold tracking-widest" id="monitorSeqBadge">SEQ #001</span>
                                <span class="badge border border-secondary text-white rounded-0 font-monospace small px-2 py-0" id="monitorStatusPill" style="font-size: 9px;">DRAFT</span>
                            </div>

                            <!-- Look Filter & Composition Overlay Controls -->
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="d-flex align-items-center gap-1">
                                    <span class="text-secondary small tracking-widest text-uppercase d-none d-md-inline" style="font-size: 8px;">LOOK:</span>
                                    <select id="monitorLookPreset" class="form-select form-select-sm bg-black border-secondary rounded-0 text-info py-0 px-2 font-monospace" style="font-size: 9px; height: 22px; width: 130px;" onchange="applyMonitorLook(this.value)">
                                        <option value="none">Standard Rec.709</option>
                                        <option value="kodak2383">Kodak 2383 Print</option>
                                        <option value="teal_orange">Teal & Orange</option>
                                        <option value="cyberpunk">Cyberpunk Neon</option>
                                        <option value="noir">Master Noir B&W</option>
                                        <option value="bleach">Bleach Bypass</option>
                                        <option value="matrix">Matrix Green</option>
                                        <option value="fuji">Fuji Eterna Mint</option>
                                        <option value="dune">Dune Arrakis Gold</option>
                                        <option value="kodachrome">Kodachrome 64</option>
                                        <option value="moonlight">Cold Moonlight</option>
                                        <option value="technicolor">Technicolor 3-Strip</option>
                                    </select>
                                </div>

                                <div class="d-flex align-items-center gap-1">
                                    <span class="text-secondary small tracking-widest text-uppercase d-none d-md-inline" style="font-size: 8px;">GRID:</span>
                                    <select id="monitorOverlaySelect" class="form-select form-select-sm bg-black border-secondary rounded-0 text-white py-0 px-2 font-monospace" style="font-size: 9px; height: 22px; width: 95px;" onchange="applyMonitorOverlay(this.value)">
                                        <option value="none">Clean</option>
                                        <option value="thirds">3x3 Thirds</option>
                                        <option value="crosshair">Crosshair</option>
                                        <option value="safetitle">Safe Frame</option>
                                        <option value="scope">2.39:1 Scope</option>
                                    </select>
                                </div>

                                <div class="d-flex align-items-center gap-2 ms-1">
                                    <span class="font-monospace text-secondary small" style="font-size: 11px;">
                                        TC: <strong class="text-info" id="monitorTimecode">00:00:00:00</strong>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-0 p-0 px-2 text-white" id="btnFullscreen" title="Fullscreen (F)">
                                        <i class="bi bi-fullscreen"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 16:9 Cinema Canvas Screen -->
                        <div class="monitor-screen position-relative bg-black" id="monitorScreenContainer">
                            <div class="ratio ratio-16x9 position-relative overflow-hidden">
                                <!-- Video Player Element -->
                                <video id="singleMonitorVideo" class="w-100 h-100 object-fit-contain bg-black" preload="auto" playsinline style="transition: filter 0.3s ease, transform 0.3s ease;"></video>
                                
                                <!-- Composition Overlay Grid Layer -->
                                <div id="gridOverlayThirds" class="monitor-hud-overlay d-none">
                                    <div class="hud-line-v left-third"></div>
                                    <div class="hud-line-v right-third"></div>
                                    <div class="hud-line-h top-third"></div>
                                    <div class="hud-line-h bottom-third"></div>
                                </div>
                                <div id="gridOverlayCrosshair" class="monitor-hud-overlay d-none">
                                    <div class="hud-crosshair-center"></div>
                                </div>
                                <div id="gridOverlaySafeTitle" class="monitor-hud-overlay d-none">
                                    <div class="hud-safetitle-box"></div>
                                </div>
                                <div id="gridOverlayScope" class="monitor-hud-overlay d-none">
                                    <div class="hud-scope-matte-top"></div>
                                    <div class="hud-scope-matte-bottom"></div>
                                </div>
                            </div>

                            <!-- Empty / Not Rendered State Overlay -->
                            <div id="noVideoOverlay" class="screen-state-overlay d-flex flex-column align-items-center justify-content-center text-center p-4">
                                <div class="scanner-grid mb-3">
                                    <i class="bi bi-camera-reels text-secondary" style="font-size: 3rem;"></i>
                                </div>
                                <h6 class="text-white tracking-widest text-uppercase mb-1">AWAITING VIDEO GENERATION</h6>
                                <p class="text-secondary small mb-3 mx-auto" style="max-width: 380px; font-size: 11px;">
                                    This segment prompt is ready for AI generation. Click render to synthesize the 15-second cinematic clip.
                                </p>
                                <form id="singleRenderForm" action="" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-info text-black rounded-0 px-4 py-2 fw-bold tracking-widest small text-uppercase shadow-cyan">
                                        <i class="bi bi-cpu me-1"></i> RENDER THIS SCENE NOW
                                    </button>
                                </form>
                            </div>

                            <!-- Processing State Overlay -->
                            <div id="processingVideoOverlay" class="screen-state-overlay d-none flex-column align-items-center justify-content-center text-center p-4">
                                <div class="spinner-radar mb-3">
                                    <div class="spinner-border text-warning" role="status" style="width: 3.5rem; height: 3.5rem;">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <i class="bi bi-cpu text-warning position-absolute top-50 start-50 translate-middle fs-4"></i>
                                </div>
                                <h6 class="text-warning tracking-widest text-uppercase mb-1">AI GENERATION IN PROGRESS</h6>
                                <p class="text-secondary small mb-3 mx-auto" style="max-width: 360px; font-size: 11px;">
                                    Wan Video Engine is synthesizing high-definition frames.
                                </p>
                                <form id="singleSyncForm" action="" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning rounded-0 px-3 py-1 small tracking-widest text-uppercase">
                                        <i class="bi bi-arrow-repeat me-1"></i> CHECK STATUS / SYNC
                                    </button>
                                </form>
                            </div>

                            <!-- Center Big Play Button (For Rendered Video) -->
                            <button type="button" class="btn btn-light rounded-circle position-absolute top-50 start-50 translate-middle play-canvas-btn d-none" id="btnBigPlay">
                                <i class="bi bi-play-fill fs-2"></i>
                            </button>

                            <!-- Audio Visualizer Mock / Status watermark -->
                            <div class="position-absolute bottom-0 start-0 m-3 d-none d-sm-flex align-items-center gap-1 watermark-hud" style="pointer-events: none; z-index: 10;">
                                <div class="hud-bar bar-1"></div>
                                <div class="hud-bar bar-2"></div>
                                <div class="hud-bar bar-3"></div>
                                <div class="hud-bar bar-4"></div>
                                <span class="font-monospace text-secondary small ms-1" style="font-size: 9px;">DIRECTOR MON // 24.00 FPS</span>
                            </div>
                        </div>

                        <!-- Monitor Interactive Transport Controls Bar -->
                        <div class="monitor-controls px-3 py-2 border-top border-secondary bg-black">
                            <!-- Scrub / Progress Bar -->
                            <div class="scrub-container mb-2 position-relative" id="scrubContainer">
                                <div class="scrub-track bg-dark">
                                    <div class="scrub-fill bg-info" id="scrubFill" style="width: 0%;"></div>
                                    <div class="scrub-handle bg-white" id="scrubHandle" style="left: 0%;"></div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <!-- Left: Playback buttons -->
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-dark border border-secondary text-white rounded-0 px-2" id="btnPrevScene" title="Previous Scene (Left Arrow)">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-white bg-white text-black fw-bold rounded-0 px-3" id="btnPlayPause" title="Play/Pause (Space)">
                                        <i class="bi bi-play-fill me-1" id="btnPlayPauseIcon"></i> <span id="btnPlayPauseText">PLAY</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-dark border border-secondary text-white rounded-0 px-2" id="btnNextScene" title="Next Scene (Right Arrow)">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-0 px-2" id="btnStepBack" title="Step Back 1s">
                                        <i class="bi bi-arrow-counterclockwise" style="font-size: 11px;"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-0 px-2" id="btnStepForward" title="Step Forward 1s">
                                        <i class="bi bi-arrow-clockwise" style="font-size: 11px;"></i>
                                    </button>
                                </div>

                                <!-- Center: Current Time / Duration & Playback Rate -->
                                <div class="d-flex align-items-center gap-3">
                                    <div class="font-monospace text-secondary small text-center" style="font-size: 11px;">
                                        <span class="text-white fw-bold" id="currentTimeDisplay">00:00</span> / <span id="durationDisplay">00:15</span>
                                    </div>
                                    
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-dark border-secondary text-secondary py-0 px-2 font-monospace rate-btn" onclick="setMonitorRate(0.5, this)" style="font-size: 9px;">0.5x</button>
                                        <button type="button" class="btn btn-dark border-secondary text-info py-0 px-2 font-monospace rate-btn active" onclick="setMonitorRate(1.0, this)" style="font-size: 9px;">1x</button>
                                        <button type="button" class="btn btn-dark border-secondary text-secondary py-0 px-2 font-monospace rate-btn" onclick="setMonitorRate(1.5, this)" style="font-size: 9px;">1.5x</button>
                                        <button type="button" class="btn btn-dark border-secondary text-secondary py-0 px-2 font-monospace rate-btn" onclick="setMonitorRate(2.0, this)" style="font-size: 9px;">2x</button>
                                    </div>
                                </div>

                                <!-- Right: Volume, Auto-advance, Loop -->
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-secondary rounded-0 px-2 toggle-btn" id="btnLoop" title="Loop Clip">
                                        <i class="bi bi-repeat"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-secondary rounded-0 px-2 toggle-btn active" id="btnAutoPlayNext" title="Auto-Play Next Scene When Finished">
                                        <i class="bi bi-fast-forward"></i> <span class="d-none d-md-inline" style="font-size: 9px;">AUTO-NEXT</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-0 px-2" id="btnMute" title="Mute/Unmute (M)">
                                        <i class="bi bi-volume-up" id="btnMuteIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Filmstrip Reel (Scene Selector Ribbon) -->
                        <div class="filmstrip-ribbon border-top border-secondary bg-dark-gradient p-2">
                            <div class="d-flex align-items-center justify-content-between mb-2 px-1">
                                <span class="small tracking-widest text-uppercase text-secondary" style="font-size: 9px;">
                                    <i class="bi bi-film me-1"></i> MASTER SEQUENCE STRIP
                                </span>
                                <span class="font-monospace text-secondary small" style="font-size: 9px;" id="filmstripSceneCount">
                                    SCENE 1 OF {{ $project->scenes->count() }}
                                </span>
                            </div>
                            <div class="filmstrip-scroll d-flex gap-2 custom-scrollbar pb-1" id="filmstripScroll">
                                @foreach($project->scenes as $index => $scene)
                                    <div class="filmstrip-thumb border border-secondary {{ $index === 0 ? 'active' : '' }}" 
                                         data-scene-id="{{ $scene->id }}"
                                         data-order-index="{{ $scene->order_index }}"
                                         data-video-src="{{ $scene->video_path ? asset('storage/' . $scene->video_path) : '' }}"
                                         data-status="{{ strtolower($scene->status ?? 'draft') }}"
                                         data-script="{{ htmlspecialchars($scene->script_segment) }}"
                                         data-job-id="{{ $scene->generation_job_id }}"
                                         data-error="{{ htmlspecialchars($scene->generation_error ?? '') }}"
                                         data-edit-url="{{ route('scenes.edit', $scene) }}"
                                         data-render-url="{{ route('scenes.render', $scene) }}"
                                         data-sync-url="{{ route('scenes.sync-render', $scene) }}"
                                         data-delete-url="{{ route('scenes.destroy', $scene) }}"
                                         data-videoeditor-url="{{ route('projects.videoeditor', $project) }}?active_scene={{ $scene->id }}"
                                         data-characters="{{ $scene->characters->toJson() }}"
                                         onclick="selectTimelineScene({{ $scene->id }})">
                                        
                                        <div class="thumb-header d-flex justify-content-between px-1">
                                             <span class="font-monospace" style="font-size: 8px;">#{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}</span>
                                             <span class="status-dot dot-{{ strtolower($scene->status ?? 'draft') }}"></span>
                                        </div>
                                        
                                        <div class="thumb-media position-relative bg-black">
                                            @if($scene->video_path)
                                                <video src="{{ asset('storage/' . $scene->video_path) }}" class="w-100 h-100 object-fit-cover" muted preload="metadata"></video>
                                                <i class="bi bi-play-circle-fill play-indicator"></i>
                                            @else
                                                <div class="d-flex align-items-center justify-content-center h-100 text-secondary">
                                                    <i class="bi bi-camera-video-off" style="font-size: 11px;"></i>
                                                </div>
                                            @endif
                                            <span class="duration-badge font-monospace">15s</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right Inspector / HUD Metadata Console -->
                <div class="col-xl-4 col-lg-5">
                    <div class="inspector-panel bg-black border border-secondary p-3 p-md-4 h-100 d-flex flex-column custom-scrollbar overflow-auto">
                        <!-- Inspector Header -->
                        <div class="d-flex justify-content-between align-items-start border-bottom border-secondary pb-3 mb-3">
                            <div>
                                <span class="text-secondary small tracking-widest text-uppercase d-block" style="font-size: 9px;">ACTIVE SEGMENT INSPECTOR</span>
                                <h4 class="text-white font-monospace mb-0" id="inspectorSeqTitle">SEQ #001</h4>
                            </div>
                            <div class="text-end">
                                <span class="badge border border-info text-info rounded-0 font-monospace uppercase px-2 py-1" id="inspectorStatusBadge">
                                    DRAFT
                                </span>
                            </div>
                        </div>

                        <!-- Director Star Rating & Mood Tags -->
                        <div class="d-flex justify-content-between align-items-center p-2 bg-dark border border-secondary mb-3">
                            <div class="d-flex align-items-center gap-1" id="directorStarRating">
                                <span class="text-secondary font-monospace" style="font-size: 9px; margin-right: 4px;">RATING:</span>
                                <i class="bi bi-star director-star" data-rating="1" onclick="setDirectorRating(1)"></i>
                                <i class="bi bi-star director-star" data-rating="2" onclick="setDirectorRating(2)"></i>
                                <i class="bi bi-star director-star" data-rating="3" onclick="setDirectorRating(3)"></i>
                                <i class="bi bi-star director-star" data-rating="4" onclick="setDirectorRating(4)"></i>
                                <i class="bi bi-star director-star" data-rating="5" onclick="setDirectorRating(5)"></i>
                            </div>
                            <span class="badge bg-black border border-secondary text-secondary font-monospace" id="directorRatingScore" style="font-size: 9px;">UNRATED</span>
                        </div>

                        <!-- Generation Error Notice (if any) -->
                        <div id="inspectorErrorBox" class="alert alert-danger bg-black border-danger text-danger rounded-0 small p-2 mb-3 d-none font-monospace" style="font-size: 10px;">
                            <i class="bi bi-exclamation-triangle me-1"></i> <span id="inspectorErrorText"></span>
                        </div>

                        <!-- Script / Prompt Segment & Voiceover Audition -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="text-secondary small tracking-widest text-uppercase" style="font-size: 9px;">
                                    <i class="bi bi-chat-quote me-1"></i> PROMPT / SCRIPT SEGMENT
                                </label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-link text-info p-0 text-decoration-none small" id="btnVoiceoverAudition" onclick="toggleVoiceoverAudition()" style="font-size: 10px;" title="Audition AI Voiceover via Web Speech">
                                        <i class="bi bi-mic me-1" id="voiceoverIcon"></i> <span id="voiceoverLabel">AUDITION</span>
                                    </button>
                                    <button type="button" class="btn btn-link text-secondary p-0 text-decoration-none small" id="btnCopyPrompt" style="font-size: 10px;" title="Copy Prompt">
                                        <i class="bi bi-clipboard me-1"></i> COPY
                                    </button>
                                </div>
                            </div>
                            <div class="script-display-box p-3 bg-dark border border-secondary text-white font-monospace position-relative" style="font-size: 12px; line-height: 1.6; min-height: 90px; max-height: 150px; overflow-y: auto;" id="inspectorScriptText">
                                Loading script...
                            </div>
                        </div>

                        <!-- Quick AI Prompt Variations Generator -->
                        <div class="mb-3 p-2 bg-dark border border-secondary">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="text-secondary small tracking-widest text-uppercase" style="font-size: 8px;">
                                    <i class="bi bi-magic me-1"></i> AI PROMPT TEMPLATE PRESET
                                </label>
                            </div>
                            <div class="d-flex gap-1">
                                <select id="promptModifierSelect" class="form-select form-select-sm bg-black border-secondary rounded-0 text-white font-monospace" style="font-size: 9px; height: 26px;">
                                    <option value="">Choose cinematic enhancement...</option>
                                    <option value=" 35mm anamorphic lens, shallow depth of field, cinematic volumetric lighting, 8k photorealistic.">35mm Anamorphic Lighting</option>
                                    <option value=" Neon cyberpunk atmospheric night rain, reflections on wet asphalt, blade runner aesthetic.">Cyberpunk Neon Rain</option>
                                    <option value=" Dramatic golden hour rim lighting, slow motion 60fps, high dynamic range master shot.">Golden Hour Rim Light</option>
                                    <option value=" High-speed dynamic chase camera, motion blur, gritty action cinema style.">High-Action Dynamic Motion</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-outline-info rounded-0 px-2 py-0 font-monospace" style="font-size: 9px;" onclick="applyPromptEnhancement()">
                                    APPLY
                                </button>
                            </div>
                        </div>

                        <!-- Director Mood Tags -->
                        <div class="mb-3">
                            <label class="text-secondary small tracking-widest text-uppercase d-block mb-1" style="font-size: 9px;">
                                <i class="bi bi-tags me-1"></i> DIRECTOR SHOT TAGS
                            </label>
                            <div class="d-flex flex-wrap gap-1" id="directorTagsContainer">
                                <span class="tag-pill" onclick="toggleDirectorTag('#MasterShot', this)">#MasterShot</span>
                                <span class="tag-pill" onclick="toggleDirectorTag('#Action', this)">#Action</span>
                                <span class="tag-pill" onclick="toggleDirectorTag('#Closeup', this)">#Closeup</span>
                                <span class="tag-pill" onclick="toggleDirectorTag('#Cinematic', this)">#Cinematic</span>
                                <span class="tag-pill" onclick="toggleDirectorTag('#Night', this)">#Night</span>
                                <span class="tag-pill" onclick="toggleDirectorTag('#VFX', this)">#VFX</span>
                                <span class="tag-pill" onclick="toggleDirectorTag('#Drone', this)">#Drone</span>
                            </div>
                        </div>

                        <!-- Cast / Character Reference Tags -->
                        <div class="mb-3">
                            <label class="text-secondary small tracking-widest text-uppercase d-block mb-1" style="font-size: 9px;">
                                <i class="bi bi-people me-1"></i> CAST & SEED REFERENCES
                            </label>
                            <div class="d-flex flex-wrap gap-2" id="inspectorCharactersList">
                                <span class="text-secondary small font-monospace" style="font-size: 10px;">No characters assigned</span>
                            </div>
                        </div>

                        <!-- Technical Specs Matrix -->
                        <div class="specs-matrix p-2 bg-dark border border-secondary mb-3">
                            <div class="row g-2 font-monospace" style="font-size: 9px;">
                                <div class="col-6 text-secondary">DURATION: <span class="text-white">15.00s</span></div>
                                <div class="col-6 text-secondary">ASPECT: <span class="text-white">{{ $project->aspect_ratio ?? '16:9' }}</span></div>
                                <div class="col-6 text-secondary">STYLE DNA: <span class="text-white text-truncate d-inline-block align-bottom" style="max-width: 80px;">{{ $project->style_preset ?? 'Cinematic' }}</span></div>
                                <div class="col-6 text-secondary">ENGINE: <span class="text-info">WAN 2.1 API</span></div>
                            </div>
                        </div>

                        <!-- Quick Actions Grid -->
                        <div class="inspector-actions mt-auto d-flex flex-column gap-2 pt-2 border-top border-secondary">
                            <div class="d-flex gap-2">
                                <form id="inspectorRenderForm" action="" method="POST" class="flex-grow-1 m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-info rounded-0 w-100 py-2 small tracking-widest text-uppercase" id="inspectorRenderBtn" style="font-size: 10px;">
                                        <i class="bi bi-cpu me-1"></i> <span id="inspectorRenderBtnText">RENDER SINGLE</span>
                                    </button>
                                </form>
                                <a href="#" id="inspectorVideoEditorLink" class="btn btn-outline-light rounded-0 py-2 px-3 small tracking-widest text-uppercase" style="font-size: 10px;" title="Open in NLE Video Editor">
                                    <i class="bi bi-film"></i>
                                </a>
                                <button type="button" class="btn btn-outline-secondary text-white rounded-0 py-2 px-3 small" onclick="downloadCurrentSceneVideo()" title="Download MP4 Video File" style="font-size: 10px;">
                                    <i class="bi bi-download"></i>
                                </button>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="#" id="inspectorEditLink" class="btn btn-dark border-secondary rounded-0 py-2 flex-grow-1 small tracking-widest text-uppercase text-center text-white" style="font-size: 10px;">
                                    <i class="bi bi-sliders me-1"></i> CONFIGURE PROMPT
                                </a>

                                <button type="button" class="btn btn-dark border-secondary rounded-0 py-2 px-3 small text-info" onclick="exportProjectEDL()" title="Export EDL File (DaVinci / Premiere)" style="font-size: 10px;">
                                    <i class="bi bi-file-earmark-code"></i> EDL
                                </button>
                                
                                <form id="inspectorDeleteForm" action="" method="POST" class="m-0" onsubmit="return confirm('CRITICAL: Permanently purge this scene sequence and its video file?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger rounded-0 py-2 px-3 small tracking-widest" style="font-size: 10px;" title="Delete Scene">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- 2. HORIZONTAL SEQUENCER / MULTI-TRACK REEL VIEW                   -->
        <!-- ================================================================= -->
        <div id="sequencer-view-container" class="timeline-view-pane d-none">
            <div class="sequencer-console bg-black border border-secondary p-3 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="font-monospace text-info small fw-bold tracking-widest">
                            <i class="bi bi-soundwave me-1"></i> MASTER NLE SEQUENCER
                        </span>
                        <span class="badge bg-dark border border-secondary text-secondary rounded-0 font-monospace small" style="font-size: 9px;">
                            TOTAL RUNTIME: {{ $project->scenes->count() * 15 }}s
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-info rounded-0 font-monospace py-0 px-2" style="font-size: 9px;" onclick="exportProjectEDL()">
                            <i class="bi bi-download me-1"></i> EXPORT EDL
                        </button>
                        <span class="text-secondary small font-monospace d-none d-md-inline" style="font-size: 10px;">CLICK ANY CLIP TO INSPECT</span>
                    </div>
                </div>

                <!-- Timeline Ruler -->
                <div class="timeline-ruler bg-dark border border-secondary position-relative mb-2 font-monospace text-secondary" style="height: 24px; font-size: 9px;">
                    @php($totalDuration = max($project->scenes->count() * 15, 60))
                    @for($sec = 0; $sec <= $totalDuration; $sec += 15)
                        <div class="ruler-mark" style="left: {{ ($sec / $totalDuration) * 100 }}%;">
                            <span>{{ gmdate('i:s', $sec) }}</span>
                        </div>
                    @endfor
                </div>

                <!-- Video Track -->
                <div class="sequencer-track-container border border-secondary bg-dark-gradient p-2 mb-2">
                    <div class="track-label text-secondary small font-monospace mb-1" style="font-size: 9px;">
                        <i class="bi bi-camera-video me-1"></i> VIDEO TRACK 1 (V1)
                    </div>
                    <div class="sequencer-clips-strip d-flex gap-2 custom-scrollbar pb-2">
                        @foreach($project->scenes as $scene)
                            <div class="sequencer-clip-card bg-black border border-secondary p-2 position-relative transition-hover" 
                                 onclick="selectTimelineScene({{ $scene->id }}); switchTimelineMode('single');"
                                 style="min-width: 200px; max-width: 240px; cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-monospace text-info fw-bold" style="font-size: 10px;">#{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}</span>
                                    <span class="badge border border-{{ strtolower($scene->status) == 'ready' ? 'success' : 'warning' }} text-{{ strtolower($scene->status) == 'ready' ? 'success' : 'warning' }} rounded-0 py-0" style="font-size: 8px;">
                                        {{ $scene->status ?? 'DRAFT' }}
                                    </span>
                                </div>
                                <div class="ratio ratio-16x9 bg-dark border border-secondary mb-1">
                                    @if($scene->video_path)
                                        <video src="{{ asset('storage/' . $scene->video_path) }}" class="w-100 h-100 object-fit-cover" muted preload="metadata"></video>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 text-secondary">
                                            <i class="bi bi-camera-video-off"></i>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-secondary small text-truncate mb-0" style="font-size: 10px;">
                                    "{{ $scene->script_segment }}"
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Audio Track with Interactive Synthesizer -->
                <div class="sequencer-track-container border border-secondary bg-dark p-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="track-label text-secondary small font-monospace" style="font-size: 9px;">
                            <i class="bi bi-music-note-beamed me-1"></i> AUDIO MASTER TRACK (A1) & AMBIENCE SYNTH
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <select id="sequencerAudioPreset" class="form-select form-select-sm bg-black border-secondary text-info font-monospace py-0 px-2" style="font-size: 9px; height: 20px; width: 140px;">
                                <option value="drone">Deep Space Drone</option>
                                <option value="noir">Noir Cyber Synth</option>
                                <option value="tension">Tension Pulse</option>
                                <option value="rain">Cyberpunk Rain</option>
                            </select>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-white py-0 px-2 font-monospace" id="btnPlayAmbience" onclick="toggleAmbienceAudio()" style="font-size: 9px;">
                                <i class="bi bi-play-fill me-1" id="ambiencePlayIcon"></i> <span id="ambiencePlayText">PLAY SCORE</span>
                            </button>
                        </div>
                    </div>
                    <div class="audio-waveform-bar bg-black border border-secondary p-2 d-flex align-items-center gap-1" style="height: 38px;" id="sequencerWaveform">
                        @for($i = 0; $i < 50; $i++)
                            <div class="audio-bar-stick" style="height: {{ rand(20, 95) }}%;"></div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- 3. STORYBOARD GRID VIEW                                           -->
        <!-- ================================================================= -->
        <div id="grid-view-container" class="timeline-view-pane d-none">
            <div class="timeline-grid pb-4 custom-scrollbar">
                @foreach($project->scenes as $scene)
                <div class="timeline-block bg-black border border-secondary p-3 d-flex flex-column transition-hover shadow-sm position-relative">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="badge bg-dark border border-secondary text-white rounded-0 tracking-widest px-2 py-1 font-monospace">
                            SEQ #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}
                        </div>
                        <span class="badge border border-{{ strtolower($scene->status) == 'ready' ? 'success' : 'warning' }} text-{{ strtolower($scene->status) == 'ready' ? 'success' : 'warning' }} rounded-0 small uppercase tracking-widest font-monospace" style="font-size: 9px;">
                            {{ $scene->status ?? 'DRAFT' }}
                        </span>
                    </div>
                    
                    <div class="ratio ratio-16x9 bg-dark mb-3 border border-secondary position-relative group-hover cursor-pointer" onclick="selectTimelineScene({{ $scene->id }}); switchTimelineMode('single');">
                        @if($scene->video_path) 
                            <video
                                src="{{ asset('storage/' . $scene->video_path) }}"
                                class="w-100 h-100 object-fit-cover bg-black timeline-preview"
                                preload="metadata"
                                muted
                                onmouseover="this.play().catch(()=>{})"
                                onmouseout="this.pause()"
                            >
                                Your browser does not support HTML5 video playback.
                            </video>
                            <button
                                type="button"
                                class="btn btn-light rounded-circle position-absolute top-50 start-50 translate-middle play-video-button"
                                data-video-src="{{ asset('storage/' . $scene->video_path) }}"
                                data-scene-label="SEQ #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}"
                                aria-label="Play scene {{ $scene->order_index }}"
                                onclick="event.stopPropagation(); playModalVideo('{{ asset('storage/' . $scene->video_path) }}', 'SEQ #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}')"
                            >
                                <i class="bi bi-play-fill fs-4"></i>
                            </button>
                            @if(strtolower($scene->status) === 'processing')
                                <div class="position-absolute top-0 start-0 end-0 bg-warning text-black text-center py-1 fw-bold tracking-widest font-monospace" style="z-index:4;font-size:8px;">
                                    REPLACEMENT GENERATING
                                </div>
                            @endif
                        @else
                            <div class="d-flex flex-column align-items-center justify-content-center text-secondary h-100 bg-black">
                                <i class="bi bi-camera-video-off mb-2 fs-4"></i>
                                <span class="small tracking-widest uppercase font-monospace" style="font-size: 9px;">AWAITING RENDER</span>
                            </div>
                        @endif
                        <div class="position-absolute bottom-0 end-0 m-2 badge bg-black border border-secondary rounded-0 font-monospace" style="font-size: 10px;">15s</div>
                    </div>

                    <div class="flex-grow-1 mb-4">
                        <p class="text-white small italic mb-0" style="font-size: 12px; line-height: 1.6;">
                            "{{ Str::limit($scene->script_segment, 90, '...') }}"
                        </p>
                        @if($scene->characters->isNotEmpty())
                            <div class="d-flex flex-wrap gap-1 mt-3">
                                @foreach($scene->characters as $character)
                                    <span class="badge border border-info text-info rounded-0 font-monospace" style="font-size:8px;">
                                        {{ strtoupper($character->name) }}{{ $character->image_path ? ' · REF' : '' }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if($scene->generation_error)
                        <div class="text-danger mb-3 font-monospace" style="font-size:10px;">{{ Str::limit($scene->generation_error, 130) }}</div>
                    @endif

                    <div class="mt-auto border-top border-secondary pt-3 d-flex justify-content-between align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-info rounded-0 uppercase tracking-widest flex-grow-1" style="font-size: 10px;" onclick="selectTimelineScene({{ $scene->id }}); switchTimelineMode('single');">
                            <i class="bi bi-display me-1"></i> INSPECT
                        </button>
                        
                        <div class="dropdown flex-grow-1">
                            <button class="btn btn-sm btn-dark border-secondary rounded-0 uppercase tracking-widest w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size: 10px;">
                                OPTIONS
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark rounded-0 border-secondary shadow-lg mt-1" style="min-width: 200px;">
                                <li>
                                    <form action="{{ route('scenes.render', $scene) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item small tracking-widest uppercase text-info py-2" style="font-size:10px;">
                                            <i class="bi bi-cpu me-2 fs-6 align-middle"></i> {{ $scene->video_path ? 'Regenerate' : 'Render Single' }}
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <a class="dropdown-item small tracking-widest uppercase text-white py-2" href="{{ route('scenes.edit', $scene) }}" style="font-size: 10px;">
                                        <i class="bi bi-sliders me-2 fs-6 align-middle"></i> Configure
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <form action="{{ route('scenes.destroy', $scene) }}" method="POST" onsubmit="return confirm('CRITICAL: Purge this sequence and its generated assets?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item small tracking-widest uppercase text-danger py-2" style="font-size: 10px;">
                                            <i class="bi bi-trash3 me-2 fs-6 align-middle"></i> Delete Segment
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
                @endforeach
            </div>
        </div>

    @endif
</div>

<!-- Video Modal Player -->
<div class="modal fade" id="timelineVideoModal" tabindex="-1" aria-labelledby="timelineVideoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-black border border-secondary rounded-0">
            <div class="modal-header border-secondary">
                <h5 class="modal-title small tracking-widest font-monospace" id="timelineVideoModalLabel">SCENE PLAYER</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-black">
                <div class="ratio ratio-16x9">
                    <video id="timelineModalPlayer" class="w-100 h-100 object-fit-contain" controls playsinline preload="metadata"></video>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.15em; }
    .italic { font-style: italic; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    
    /* View Switcher */
    .mode-btn { color: #888; border: none; font-size: 11px; padding: 6px 14px; transition: all 0.2s ease; }
    .mode-btn:hover { color: #fff; background: #222; }
    .mode-btn.active { color: #000; background: #fff; font-weight: 700; }

    /* Single Monitor Console */
    .monitor-console {
        box-shadow: 0 10px 40px rgba(0,0,0,0.8);
        border: 1px solid #333 !important;
    }
    .bg-dark-gradient {
        background: linear-gradient(180deg, #181818 0%, #0d0d0d 100%);
    }
    .status-indicator-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        background: #0dcaf0;
        box-shadow: 0 0 8px #0dcaf0;
    }
    .monitor-screen {
        position: relative;
        background: #000;
        overflow: hidden;
    }
    .screen-state-overlay {
        position: absolute;
        inset: 0;
        z-index: 5;
        background: radial-gradient(circle, rgba(15,15,15,0.95) 0%, rgba(5,5,5,0.99) 100%);
    }
    .play-canvas-btn {
        width: 64px;
        height: 64px;
        z-index: 10;
        opacity: 0.9;
        transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .play-canvas-btn:hover {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1.1) !important;
    }
    .shadow-cyan {
        box-shadow: 0 0 20px rgba(13, 202, 240, 0.4);
    }

    /* Animated HUD Watermark */
    .hud-bar { width: 3px; background: #0dcaf0; animation: hudPulse 1.2s infinite ease-in-out; }
    .bar-1 { height: 12px; animation-delay: 0.1s; }
    .bar-2 { height: 18px; animation-delay: 0.3s; }
    .bar-3 { height: 8px; animation-delay: 0.2s; }
    .bar-4 { height: 14px; animation-delay: 0.4s; }
    @keyframes hudPulse {
        0%, 100% { transform: scaleY(0.4); opacity: 0.3; }
        50% { transform: scaleY(1); opacity: 0.9; }
    }

    /* Scrubber Track */
    .scrub-container {
        height: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
    }
    .scrub-track {
        width: 100%;
        height: 4px;
        border-radius: 2px;
        position: relative;
        transition: height 0.15s ease;
    }
    .scrub-container:hover .scrub-track {
        height: 6px;
    }
    .scrub-fill {
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        border-radius: 2px;
    }
    .scrub-handle {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        position: absolute;
        top: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 0 6px rgba(255,255,255,0.8);
        opacity: 0;
        transition: opacity 0.15s;
    }
    .scrub-container:hover .scrub-handle {
        opacity: 1;
    }

    /* Filmstrip Ribbon */
    .filmstrip-scroll {
        overflow-x: auto;
        white-space: nowrap;
    }
    .filmstrip-thumb {
        width: 120px;
        flex-shrink: 0;
        background: #000;
        padding: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .filmstrip-thumb:hover {
        border-color: #fff !important;
        transform: translateY(-2px);
    }
    .filmstrip-thumb.active {
        border-color: #0dcaf0 !important;
        box-shadow: 0 0 10px rgba(13, 202, 240, 0.4);
    }
    .filmstrip-thumb .thumb-media {
        aspect-ratio: 16 / 9;
        overflow: hidden;
        border: 1px solid #222;
    }
    .filmstrip-thumb .play-indicator {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: rgba(255,255,255,0.7);
        font-size: 16px;
        display: none;
    }
    .filmstrip-thumb:hover .play-indicator {
        display: block;
    }
    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
        background: #888;
    }
    .status-dot.dot-ready { background: #198754; box-shadow: 0 0 6px #198754; }
    .status-dot.dot-processing { background: #ffc107; box-shadow: 0 0 6px #ffc107; }
    .status-dot.dot-draft { background: #6c757d; }
    .status-dot.dot-failed { background: #dc3545; box-shadow: 0 0 6px #dc3545; }
    .duration-badge {
        position: absolute;
        bottom: 2px;
        right: 2px;
        background: rgba(0,0,0,0.85);
        color: #fff;
        font-size: 8px;
        padding: 0 3px;
    }

    /* Inspector Styling */
    .inspector-panel {
        box-shadow: 0 10px 30px rgba(0,0,0,0.6);
        border: 1px solid #333 !important;
    }

    /* Sequencer View Elements */
    .timeline-ruler {
        overflow: hidden;
    }
    .ruler-mark {
        position: absolute;
        top: 0;
        bottom: 0;
        border-left: 1px solid #444;
        padding-left: 4px;
    }
    .audio-bar-stick {
        flex: 1;
        background: #28a745;
        border-radius: 1px;
        opacity: 0.7;
    }

    /* Grid View Elements */
    .timeline-grid {
        display: grid;
        gap: 24px;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
        align-items: stretch;
    }
    .timeline-block { min-width: 0; width: 100%; }
    .transition-hover { transition: all 0.3s ease; }
    .transition-hover:hover { border-color: #fff !important; transform: translateY(-4px); background-color: #0a0a0a !important; }
    
    .cursor-pointer { cursor: pointer; }
    .toggle-btn.active { color: #0dcaf0 !important; border-color: #0dcaf0 !important; }

    /* HUD Overlays & Reticles */
    .monitor-hud-overlay {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 8;
    }
    .hud-line-v {
        position: absolute;
        top: 0;
        bottom: 0;
        width: 1px;
        background: rgba(255, 255, 255, 0.25);
    }
    .hud-line-v.left-third { left: 33.333%; }
    .hud-line-v.right-third { left: 66.666%; }
    .hud-line-h {
        position: absolute;
        left: 0;
        right: 0;
        height: 1px;
        background: rgba(255, 255, 255, 0.25);
    }
    .hud-line-h.top-third { top: 33.333%; }
    .hud-line-h.bottom-third { top: 66.666%; }
    
    .hud-crosshair-center {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 24px;
        height: 24px;
        transform: translate(-50%, -50%);
        border: 1px solid rgba(13, 202, 240, 0.6);
        border-radius: 50%;
    }
    .hud-crosshair-center::before {
        content: '';
        position: absolute;
        top: -6px;
        bottom: -6px;
        left: 50%;
        width: 1px;
        background: rgba(13, 202, 240, 0.8);
        transform: translateX(-50%);
    }
    .hud-crosshair-center::after {
        content: '';
        position: absolute;
        left: -6px;
        right: -6px;
        top: 50%;
        height: 1px;
        background: rgba(13, 202, 240, 0.8);
        transform: translateY(-50%);
    }

    .hud-safetitle-box {
        position: absolute;
        inset: 10%;
        border: 1px dashed rgba(255, 255, 255, 0.35);
        box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.5);
    }
    .hud-scope-matte-top {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 12.5%;
        background: #000;
    }
    .hud-scope-matte-bottom {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 12.5%;
        background: #000;
    }

    /* Director Rating & Tags */
    .director-star {
        color: #444;
        cursor: pointer;
        font-size: 13px;
        transition: color 0.15s ease, transform 0.15s ease;
    }
    .director-star:hover, .director-star.active {
        color: #ffc107;
        text-shadow: 0 0 6px rgba(255, 193, 7, 0.6);
    }
    .tag-pill {
        display: inline-block;
        font-family: 'Courier New', Courier, monospace;
        font-size: 8px;
        padding: 2px 6px;
        background: #161616;
        border: 1px solid #333;
        color: #888;
        cursor: pointer;
        border-radius: 2px;
        transition: all 0.15s ease;
    }
    .tag-pill:hover, .tag-pill.active {
        background: rgba(13, 202, 240, 0.15);
        border-color: #0dcaf0;
        color: #0dcaf0;
    }
    .rate-btn.active {
        background: #0dcaf0 !important;
        color: #000 !important;
        font-weight: bold;
    }

    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #000; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #333; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #0dcaf0; }
</style>

<script>
// State Management
let currentSceneIndex = 0;
let scenesData = [];
let isPlaying = false;
let isLooping = false;
let isAutoPlayNext = true;
let isSpeaking = false;
let audioCtx = null;
let ambienceGainNode = null;
let isAmbiencePlaying = false;
let ambienceOscillators = [];
const monitorVideo = document.getElementById('singleMonitorVideo');

// Collect scenes metadata from DOM
document.addEventListener('DOMContentLoaded', () => {
    const thumbElements = document.querySelectorAll('.filmstrip-thumb');
    thumbElements.forEach((el, index) => {
        let chars = [];
        try {
            chars = JSON.parse(el.dataset.characters || '[]');
        } catch(e) {}

        scenesData.push({
            index: index,
            id: parseInt(el.dataset.sceneId),
            orderIndex: parseInt(el.dataset.orderIndex),
            videoSrc: el.dataset.videoSrc,
            status: el.dataset.status,
            script: el.dataset.script,
            jobId: el.dataset.jobId,
            error: el.dataset.error,
            editUrl: el.dataset.editUrl,
            renderUrl: el.dataset.renderUrl,
            syncUrl: el.dataset.syncUrl,
            deleteUrl: el.dataset.deleteUrl,
            videoeditorUrl: el.dataset.videoeditorUrl,
            characters: chars
        });
    });

    // Initialize first scene if available
    if (scenesData.length > 0) {
        renderSceneToMonitor(0);
    }

    // View Mode Switcher Listeners
    document.querySelectorAll('.mode-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            switchTimelineMode(btn.dataset.mode);
        });
    });

    // Check saved mode or default to single
    const savedMode = localStorage.getItem('void_timeline_view_mode') || 'single';
    switchTimelineMode(savedMode);

    // Setup Video Event Listeners
    setupVideoControls();
    setupKeyboardShortcuts();
});

function switchTimelineMode(mode) {
    document.querySelectorAll('.mode-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.mode === mode);
    });

    document.getElementById('single-view-container')?.classList.toggle('d-none', mode !== 'single');
    document.getElementById('sequencer-view-container')?.classList.toggle('d-none', mode !== 'sequencer');
    document.getElementById('grid-view-container')?.classList.toggle('d-none', mode !== 'grid');

    localStorage.setItem('void_timeline_view_mode', mode);
}

function selectTimelineScene(sceneId) {
    const idx = scenesData.findIndex(s => s.id === sceneId);
    if (idx !== -1) {
        renderSceneToMonitor(idx);
    }
}

function renderSceneToMonitor(index) {
    if (index < 0 || index >= scenesData.length) return;
    currentSceneIndex = index;
    const scene = scenesData[index];

    // Cancel any speech synthesis if running
    if (window.speechSynthesis && window.speechSynthesis.speaking) {
        window.speechSynthesis.cancel();
        isSpeaking = false;
        document.getElementById('voiceoverIcon').className = 'bi bi-mic me-1';
        document.getElementById('voiceoverLabel').textContent = 'AUDITION';
    }

    // Update active thumb
    document.querySelectorAll('.filmstrip-thumb').forEach((thumb, i) => {
        thumb.classList.toggle('active', i === index);
    });

    // Scroll active thumb into view
    const activeThumb = document.querySelectorAll('.filmstrip-thumb')[index];
    if (activeThumb) {
        activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }

    // Update Monitor HUD Labels
    const seqStr = 'SEQ #' + String(scene.orderIndex).padStart(3, '0');
    document.getElementById('monitorSeqBadge').textContent = seqStr;
    document.getElementById('inspectorSeqTitle').textContent = seqStr;
    document.getElementById('filmstripSceneCount').textContent = `SCENE ${index + 1} OF ${scenesData.length}`;

    // Update Status Pill
    const statusPill = document.getElementById('monitorStatusPill');
    const statusBadge = document.getElementById('inspectorStatusBadge');
    const statusDot = document.getElementById('monitorStatusDot');
    const statusUpper = (scene.status || 'DRAFT').toUpperCase();
    
    statusPill.textContent = statusUpper;
    statusBadge.textContent = statusUpper;

    // Reset status classes
    statusPill.className = 'badge border rounded-0 font-monospace small px-2 py-0';
    statusBadge.className = 'badge border rounded-0 font-monospace uppercase px-2 py-1';
    
    if (scene.status === 'ready') {
        statusPill.classList.add('border-success', 'text-success');
        statusBadge.classList.add('border-success', 'text-success');
        statusDot.style.background = '#198754';
        statusDot.style.boxShadow = '0 0 8px #198754';
    } else if (scene.status === 'processing') {
        statusPill.classList.add('border-warning', 'text-warning');
        statusBadge.classList.add('border-warning', 'text-warning');
        statusDot.style.background = '#ffc107';
        statusDot.style.boxShadow = '0 0 8px #ffc107';
    } else {
        statusPill.classList.add('border-secondary', 'text-secondary');
        statusBadge.classList.add('border-secondary', 'text-secondary');
        statusDot.style.background = '#0dcaf0';
        statusDot.style.boxShadow = '0 0 8px #0dcaf0';
    }

    // Video State overlays
    const noVideoOverlay = document.getElementById('noVideoOverlay');
    const processingOverlay = document.getElementById('processingVideoOverlay');
    const btnBigPlay = document.getElementById('btnBigPlay');

    if (scene.status === 'processing') {
        processingOverlay.classList.remove('d-none');
        processingOverlay.classList.add('d-flex');
        noVideoOverlay.classList.add('d-none');
        btnBigPlay.classList.add('d-none');
        document.getElementById('singleSyncForm').action = scene.syncUrl;
    } else if (!scene.videoSrc) {
        noVideoOverlay.classList.remove('d-none');
        noVideoOverlay.classList.add('d-flex');
        processingOverlay.classList.add('d-none');
        btnBigPlay.classList.add('d-none');
        document.getElementById('singleRenderForm').action = scene.renderUrl;
    } else {
        noVideoOverlay.classList.add('d-none');
        processingOverlay.classList.add('d-none');
        btnBigPlay.classList.remove('d-none');
    }

    // Video player element
    if (scene.videoSrc) {
        monitorVideo.src = scene.videoSrc;
        monitorVideo.load();
    } else {
        monitorVideo.removeAttribute('src');
        monitorVideo.load();
    }
    updatePlayPauseUI(false);

    // Inspector Script Display
    document.getElementById('inspectorScriptText').textContent = scene.script || 'No script segment provided for this scene.';

    // Inspector Error Display
    const errorBox = document.getElementById('inspectorErrorBox');
    if (scene.error) {
        errorBox.classList.remove('d-none');
        document.getElementById('inspectorErrorText').textContent = scene.error;
    } else {
        errorBox.classList.add('d-none');
    }

    // Inspector Cast
    const charList = document.getElementById('inspectorCharactersList');
    charList.innerHTML = '';
    if (scene.characters && scene.characters.length > 0) {
        scene.characters.forEach(char => {
            const span = document.createElement('span');
            span.className = 'badge border border-info text-info rounded-0 font-monospace p-1 px-2';
            span.style.fontSize = '9px';
            span.innerHTML = `<i class="bi bi-person-fill me-1"></i> ${char.name.toUpperCase()}${char.image_path ? ' · REF' : ''}`;
            charList.appendChild(span);
        });
    } else {
        charList.innerHTML = '<span class="text-secondary small font-monospace" style="font-size: 10px;">No character seeds assigned</span>';
    }

    // Inspector Action Links
    document.getElementById('inspectorRenderForm').action = scene.renderUrl;
    document.getElementById('inspectorRenderBtnText').textContent = scene.videoSrc ? 'REGENERATE VARIATION' : 'RENDER SINGLE';
    document.getElementById('inspectorVideoEditorLink').href = scene.videoeditorUrl;
    document.getElementById('inspectorEditLink').href = scene.editUrl;
    document.getElementById('inspectorDeleteForm').action = scene.deleteUrl;

    // Load persisted Director Ratings & Tags for this scene
    loadDirectorRating(scene.id);
    loadDirectorTags(scene.id);
}

// ----------------------------------------------------
// Director Look Presets & HUD Overlays
// ----------------------------------------------------
function applyMonitorLook(look) {
    if (!monitorVideo) return;
    switch(look) {
        case 'kodak2383':
            monitorVideo.style.filter = 'sepia(18%) contrast(1.22) brightness(1.02) saturate(1.18)';
            break;
        case 'teal_orange':
            monitorVideo.style.filter = 'contrast(1.35) saturate(1.45) hue-rotate(25deg) brightness(1.02)';
            break;
        case 'cyberpunk':
            monitorVideo.style.filter = 'hue-rotate(190deg) contrast(1.45) saturate(1.8) brightness(0.95)';
            break;
        case 'noir':
            monitorVideo.style.filter = 'grayscale(100%) contrast(1.65) brightness(0.92)';
            break;
        case 'bleach':
            monitorVideo.style.filter = 'saturate(0.55) contrast(1.55) brightness(0.96)';
            break;
        case 'matrix':
            monitorVideo.style.filter = 'hue-rotate(75deg) saturate(1.6) contrast(1.35) brightness(0.98)';
            break;
        case 'fuji':
            monitorVideo.style.filter = 'hue-rotate(15deg) contrast(1.15) saturate(1.1) brightness(1.04)';
            break;
        case 'dune':
            monitorVideo.style.filter = 'sepia(35%) hue-rotate(-15deg) contrast(1.25) saturate(1.35) brightness(1.05)';
            break;
        case 'kodachrome':
            monitorVideo.style.filter = 'sepia(20%) saturate(1.65) contrast(1.28) brightness(1.03)';
            break;
        case 'moonlight':
            monitorVideo.style.filter = 'hue-rotate(160deg) saturate(0.9) contrast(1.3) brightness(0.88)';
            break;
        case 'technicolor':
            monitorVideo.style.filter = 'saturate(2.2) contrast(1.3) brightness(1.02)';
            break;
        default:
            monitorVideo.style.filter = 'none';
            break;
    }
}

function applyMonitorOverlay(overlay) {
    document.getElementById('gridOverlayThirds').classList.add('d-none');
    document.getElementById('gridOverlayCrosshair').classList.add('d-none');
    document.getElementById('gridOverlaySafeTitle').classList.add('d-none');
    document.getElementById('gridOverlayScope').classList.add('d-none');

    if (overlay === 'thirds') {
        document.getElementById('gridOverlayThirds').classList.remove('d-none');
    } else if (overlay === 'crosshair') {
        document.getElementById('gridOverlayCrosshair').classList.remove('d-none');
    } else if (overlay === 'safetitle') {
        document.getElementById('gridOverlaySafeTitle').classList.remove('d-none');
    } else if (overlay === 'scope') {
        document.getElementById('gridOverlayScope').classList.remove('d-none');
    }
}

function setMonitorRate(rate, btn) {
    if (monitorVideo) {
        monitorVideo.playbackRate = rate;
    }
    document.querySelectorAll('.rate-btn').forEach(b => {
        b.classList.remove('active', 'text-info');
        b.classList.add('text-secondary');
    });
    btn.classList.add('active');
    btn.classList.remove('text-secondary');
}

// ----------------------------------------------------
// Web Speech API - AI Voiceover Audition
// ----------------------------------------------------
function toggleVoiceoverAudition() {
    if (!('speechSynthesis' in window)) {
        alert('Web Speech API is not supported in this browser.');
        return;
    }

    const icon = document.getElementById('voiceoverIcon');
    const label = document.getElementById('voiceoverLabel');

    if (isSpeaking || window.speechSynthesis.speaking) {
        window.speechSynthesis.cancel();
        isSpeaking = false;
        icon.className = 'bi bi-mic me-1';
        label.textContent = 'AUDITION';
    } else {
        const text = document.getElementById('inspectorScriptText').textContent;
        if (!text || text.trim() === '' || text.includes('Loading script')) {
            alert('No script text available to audition.');
            return;
        }

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 1.0;
        utterance.pitch = 0.95;

        // Pick preferred English voice if available
        const voices = window.speechSynthesis.getVoices();
        const preferred = voices.find(v => v.lang.startsWith('en') && (v.name.includes('Natural') || v.name.includes('Daniel') || v.name.includes('Google')));
        if (preferred) utterance.voice = preferred;

        utterance.onstart = () => {
            isSpeaking = true;
            icon.className = 'bi bi-stop-circle-fill text-danger me-1';
            label.textContent = 'STOPPING';
        };

        utterance.onend = () => {
            isSpeaking = false;
            icon.className = 'bi bi-mic me-1';
            label.textContent = 'AUDITION';
        };

        utterance.onerror = () => {
            isSpeaking = false;
            icon.className = 'bi bi-mic me-1';
            label.textContent = 'AUDITION';
        };

        window.speechSynthesis.speak(utterance);
    }
}

// ----------------------------------------------------
// Director Star Rating & Tagging System
// ----------------------------------------------------
function setDirectorRating(stars) {
    if (!scenesData[currentSceneIndex]) return;
    const sceneId = scenesData[currentSceneIndex].id;
    localStorage.setItem(`void_rating_${sceneId}`, stars);
    updateStarsUI(stars);
}

function loadDirectorRating(sceneId) {
    const rating = parseInt(localStorage.getItem(`void_rating_${sceneId}`) || '0');
    updateStarsUI(rating);
}

function updateStarsUI(rating) {
    const scoreBadge = document.getElementById('directorRatingScore');
    const stars = document.querySelectorAll('#directorStarRating .director-star');
    stars.forEach(s => {
        const val = parseInt(s.dataset.rating);
        if (val <= rating) {
            s.className = 'bi bi-star-fill director-star active';
        } else {
            s.className = 'bi bi-star director-star';
        }
    });

    if (rating > 0) {
        scoreBadge.textContent = `${rating}/5 STARS`;
        scoreBadge.className = 'badge bg-black border border-warning text-warning font-monospace';
    } else {
        scoreBadge.textContent = 'UNRATED';
        scoreBadge.className = 'badge bg-black border border-secondary text-secondary font-monospace';
    }
}

function toggleDirectorTag(tag, el) {
    if (!scenesData[currentSceneIndex]) return;
    const sceneId = scenesData[currentSceneIndex].id;
    let saved = JSON.parse(localStorage.getItem(`void_tags_${sceneId}`) || '[]');
    
    if (saved.includes(tag)) {
        saved = saved.filter(t => t !== tag);
        el.classList.remove('active');
    } else {
        saved.push(tag);
        el.classList.add('active');
    }
    localStorage.setItem(`void_tags_${sceneId}`, JSON.stringify(saved));
}

function loadDirectorTags(sceneId) {
    const saved = JSON.parse(localStorage.getItem(`void_tags_${sceneId}`) || '[]');
    document.querySelectorAll('#directorTagsContainer .tag-pill').forEach(pill => {
        pill.classList.toggle('active', saved.includes(pill.textContent.trim()));
    });
}

// ----------------------------------------------------
// Prompt Template Enhancer
// ----------------------------------------------------
function applyPromptEnhancement() {
    const modifier = document.getElementById('promptModifierSelect').value;
    if (!modifier) return;
    const box = document.getElementById('inspectorScriptText');
    box.textContent = box.textContent.trim() + modifier;
    
    // Copy updated prompt to clipboard automatically
    navigator.clipboard.writeText(box.textContent).then(() => {
        const btn = document.getElementById('btnCopyPrompt');
        btn.innerHTML = '<i class="bi bi-check-lg text-success me-1"></i> ENHANCED & COPIED!';
        setTimeout(() => {
            btn.innerHTML = '<i class="bi bi-clipboard me-1"></i> COPY';
        }, 2000);
    });
}

// ----------------------------------------------------
// Download Video & EDL Decision List Export
// ----------------------------------------------------
function downloadCurrentSceneVideo() {
    if (!scenesData[currentSceneIndex] || !scenesData[currentSceneIndex].videoSrc) {
        alert('No rendered video file available for this scene.');
        return;
    }
    const a = document.createElement('a');
    a.href = scenesData[currentSceneIndex].videoSrc;
    a.download = `scene_${scenesData[currentSceneIndex].orderIndex}_master.mp4`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}

function exportProjectEDL() {
    if (!scenesData.length) return;
    let edl = `TITLE: ${document.querySelector('h2').textContent.trim()}\nFCM: NON-DROP FRAME\n\n`;
    let curSec = 0;
    
    scenesData.forEach((s, i) => {
        const inSec = curSec;
        const outSec = curSec + 15;
        curSec = outSec;
        
        const inTC = `01:00:${String(Math.floor(inSec)).padStart(2,'0')}:00`;
        const outTC = `01:00:${String(Math.floor(outSec)).padStart(2,'0')}:00`;
        const edlIndex = String(i + 1).padStart(3, '0');
        
        edl += `${edlIndex}  AX       V     C        00:00:00:00 00:00:15:00 ${inTC} ${outTC}\n`;
        edl += `* FROM CLIP: SCENE_${String(s.orderIndex).padStart(3, '0')}\n`;
        edl += `* PROMPT: ${s.script.replace(/\n/g, ' ')}\n\n`;
    });

    const blob = new Blob([edl], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `timeline_sequence.edl`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// ----------------------------------------------------
// Generative Web Audio Ambience Synth
// ----------------------------------------------------
function toggleAmbienceAudio() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (audioCtx.state === 'suspended') {
        audioCtx.resume();
    }

    const btn = document.getElementById('btnPlayAmbience');
    const icon = document.getElementById('ambiencePlayIcon');
    const text = document.getElementById('ambiencePlayText');
    const preset = document.getElementById('sequencerAudioPreset').value;

    if (isAmbiencePlaying) {
        ambienceOscillators.forEach(osc => osc.stop());
        ambienceOscillators = [];
        isAmbiencePlaying = false;
        icon.className = 'bi bi-play-fill me-1';
        text.textContent = 'PLAY SCORE';
        btn.classList.remove('btn-info', 'text-black');
        btn.classList.add('btn-dark', 'text-white');
    } else {
        ambienceGainNode = audioCtx.createGain();
        ambienceGainNode.gain.setValueAtTime(0.2, audioCtx.currentTime);
        ambienceGainNode.connect(audioCtx.destination);

        let freqs = [55, 110, 164.81]; // Root A
        if (preset === 'noir') freqs = [65.41, 130.81, 196.00]; // C minor
        if (preset === 'tension') freqs = [46.25, 92.50, 138.59]; // F# minor

        freqs.forEach(f => {
            const osc = audioCtx.createOscillator();
            osc.type = preset === 'noir' ? 'sawtooth' : 'sine';
            osc.frequency.setValueAtTime(f, audioCtx.currentTime);
            osc.connect(ambienceGainNode);
            osc.start();
            ambienceOscillators.push(osc);
        });

        isAmbiencePlaying = true;
        icon.className = 'bi bi-stop-fill me-1';
        text.textContent = 'STOP SCORE';
        btn.classList.remove('btn-dark', 'text-white');
        btn.classList.add('btn-info', 'text-black');
    }
}

function setupVideoControls() {
    const btnPlayPause = document.getElementById('btnPlayPause');
    const btnBigPlay = document.getElementById('btnBigPlay');
    const btnPrevScene = document.getElementById('btnPrevScene');
    const btnNextScene = document.getElementById('btnNextScene');
    const btnStepBack = document.getElementById('btnStepBack');
    const btnStepForward = document.getElementById('btnStepForward');
    const btnLoop = document.getElementById('btnLoop');
    const btnAutoPlayNext = document.getElementById('btnAutoPlayNext');
    const btnMute = document.getElementById('btnMute');
    const btnFullscreen = document.getElementById('btnFullscreen');
    const scrubContainer = document.getElementById('scrubContainer');
    const scrubFill = document.getElementById('scrubFill');
    const scrubHandle = document.getElementById('scrubHandle');
    const currentTimeDisplay = document.getElementById('currentTimeDisplay');
    const durationDisplay = document.getElementById('durationDisplay');
    const monitorTimecode = document.getElementById('monitorTimecode');

    function togglePlay() {
        if (!monitorVideo.src || monitorVideo.getAttribute('src') === '') return;
        if (monitorVideo.paused) {
            monitorVideo.play().then(() => {
                updatePlayPauseUI(true);
            }).catch(() => {});
        } else {
            monitorVideo.pause();
            updatePlayPauseUI(false);
        }
    }

    btnPlayPause.addEventListener('click', togglePlay);
    btnBigPlay.addEventListener('click', togglePlay);
    monitorVideo.addEventListener('click', togglePlay);

    btnPrevScene.addEventListener('click', () => {
        if (currentSceneIndex > 0) renderSceneToMonitor(currentSceneIndex - 1);
    });

    btnNextScene.addEventListener('click', () => {
        if (currentSceneIndex < scenesData.length - 1) renderSceneToMonitor(currentSceneIndex + 1);
    });

    btnStepBack.addEventListener('click', () => {
        if (monitorVideo.currentTime) monitorVideo.currentTime = Math.max(0, monitorVideo.currentTime - 1);
    });

    btnStepForward.addEventListener('click', () => {
        if (monitorVideo.duration) monitorVideo.currentTime = Math.min(monitorVideo.duration, monitorVideo.currentTime + 1);
    });

    btnLoop.addEventListener('click', () => {
        isLooping = !isLooping;
        monitorVideo.loop = isLooping;
        btnLoop.classList.toggle('active', isLooping);
    });

    btnAutoPlayNext.addEventListener('click', () => {
        isAutoPlayNext = !isAutoPlayNext;
        btnAutoPlayNext.classList.toggle('active', isAutoPlayNext);
    });

    btnMute.addEventListener('click', () => {
        monitorVideo.muted = !monitorVideo.muted;
        const icon = document.getElementById('btnMuteIcon');
        if (monitorVideo.muted) {
            icon.className = 'bi bi-volume-mute text-secondary';
        } else {
            icon.className = 'bi bi-volume-up text-white';
        }
    });

    btnFullscreen.addEventListener('click', () => {
        const container = document.getElementById('monitorScreenContainer');
        if (!document.fullscreenElement) {
            container.requestFullscreen().catch(() => {});
        } else {
            document.exitFullscreen().catch(() => {});
        }
    });

    // Time Update & Scrub
    monitorVideo.addEventListener('timeupdate', () => {
        if (!monitorVideo.duration) return;
        const pct = (monitorVideo.currentTime / monitorVideo.duration) * 100;
        scrubFill.style.width = pct + '%';
        scrubHandle.style.left = pct + '%';

        const curMin = Math.floor(monitorVideo.currentTime / 60);
        const curSec = Math.floor(monitorVideo.currentTime % 60);
        const curFrames = Math.floor((monitorVideo.currentTime % 1) * 24);
        
        const durMin = Math.floor(monitorVideo.duration / 60);
        const durSec = Math.floor(monitorVideo.duration % 60);

        currentTimeDisplay.textContent = `${String(curMin).padStart(2,'0')}:${String(curSec).padStart(2,'0')}`;
        durationDisplay.textContent = `${String(durMin).padStart(2,'0')}:${String(durSec).padStart(2,'0')}`;
        
        monitorTimecode.textContent = `00:${String(curMin).padStart(2,'0')}:${String(curSec).padStart(2,'0')}:${String(curFrames).padStart(2,'0')}`;
    });

    // Video Ended
    monitorVideo.addEventListener('ended', () => {
        updatePlayPauseUI(false);
        if (isAutoPlayNext && !isLooping) {
            if (currentSceneIndex < scenesData.length - 1) {
                renderSceneToMonitor(currentSceneIndex + 1);
                setTimeout(() => {
                    monitorVideo.play().then(() => updatePlayPauseUI(true)).catch(() => {});
                }, 300);
            }
        }
    });

    // Scrub Clicking & Dragging
    let isDraggingScrub = false;
    function handleScrub(e) {
        const rect = scrubContainer.getBoundingClientRect();
        const pos = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
        if (monitorVideo.duration) {
            monitorVideo.currentTime = pos * monitorVideo.duration;
        }
    }
    scrubContainer.addEventListener('mousedown', (e) => {
        isDraggingScrub = true;
        handleScrub(e);
    });
    window.addEventListener('mousemove', (e) => {
        if (isDraggingScrub) handleScrub(e);
    });
    window.addEventListener('mouseup', () => {
        isDraggingScrub = false;
    });

    // Copy Prompt Button
    document.getElementById('btnCopyPrompt').addEventListener('click', () => {
        const text = document.getElementById('inspectorScriptText').textContent;
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('btnCopyPrompt');
            btn.innerHTML = '<i class="bi bi-check-lg text-success me-1"></i> COPIED!';
            setTimeout(() => {
                btn.innerHTML = '<i class="bi bi-clipboard me-1"></i> COPY';
            }, 2000);
        });
    });
}

function updatePlayPauseUI(playing) {
    isPlaying = playing;
    const btnPlayPauseIcon = document.getElementById('btnPlayPauseIcon');
    const btnPlayPauseText = document.getElementById('btnPlayPauseText');
    const btnBigPlay = document.getElementById('btnBigPlay');

    if (playing) {
        btnPlayPauseIcon.className = 'bi bi-pause-fill me-1';
        btnPlayPauseText.textContent = 'PAUSE';
        btnBigPlay.classList.add('d-none');
    } else {
        btnPlayPauseIcon.className = 'bi bi-play-fill me-1';
        btnPlayPauseText.textContent = 'PLAY';
        if (monitorVideo.src && monitorVideo.getAttribute('src') !== '') {
            btnBigPlay.classList.remove('d-none');
        }
    }
}

function setupKeyboardShortcuts() {
    window.addEventListener('keydown', (e) => {
        // Ignore if user is typing in an input or textarea
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;

        if (e.code === 'Space') {
            e.preventDefault();
            document.getElementById('btnPlayPause').click();
        } else if (e.code === 'ArrowLeft') {
            e.preventDefault();
            document.getElementById('btnPrevScene').click();
        } else if (e.code === 'ArrowRight') {
            e.preventDefault();
            document.getElementById('btnNextScene').click();
        } else if (e.key === '1') {
            switchTimelineMode('single');
        } else if (e.key === '2') {
            switchTimelineMode('sequencer');
        } else if (e.key === '3') {
            switchTimelineMode('grid');
        } else if (e.key === 'f' || e.key === 'F') {
            document.getElementById('btnFullscreen').click();
        } else if (e.key === 'm' || e.key === 'M') {
            document.getElementById('btnMute').click();
        }
    });
}

function playModalVideo(src, label) {
    const player = document.getElementById('timelineModalPlayer');
    document.getElementById('timelineVideoModalLabel').textContent = label + ' / CINEMA PLAYER';
    player.src = src;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('timelineVideoModal')).show();
    player.play().catch(() => {});
}

document.getElementById('timelineVideoModal')?.addEventListener('hidden.bs.modal', () => {
    const player = document.getElementById('timelineModalPlayer');
    player.pause();
    player.removeAttribute('src');
    player.load();
});
</script>
@endsection
