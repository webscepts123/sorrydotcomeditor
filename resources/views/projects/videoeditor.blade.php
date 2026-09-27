<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VOID SHADOW NLE STUDIO - {{ $project->title }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Syncopate:wght@400;700&family=JetBrains+Mono:wght@400;500;700&family=Bebas+Neue&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --nle-bg: #0a0a0a;
            --nle-panel: #131313;
            --nle-panel-header: #1a1a1a;
            --nle-border: #262626;
            --nle-text: #a4a4a4;
            --nle-text-light: #f0f0f0;
            --nle-active: #0dcaf0;
            --nle-active-glow: rgba(13, 202, 240, 0.4);
            --nle-hover: #222222;
            --nle-audio-green: #2ecc71;
            --nle-clip-bg: #1e4a7a;
            --nle-audio-clip-bg: #1b5e20;
            --nle-danger: #e74c3c;
            --nle-warning: #f39c12;
            --font-xs: 9px;
            --font-sm: 11px;
            --font-md: 12px;
        }

        body, html {
            margin: 0;
            padding: 0;
            width: 100vw;
            height: 100vh;
            background-color: var(--nle-bg);
            color: var(--nle-text);
            font-family: 'Inter', sans-serif;
            overflow: hidden;
            user-select: none;
        }
        
        .uppercase { text-transform: uppercase; letter-spacing: 0.08em; }
        .font-mono { font-family: 'JetBrains Mono', 'Courier New', monospace; }
        
        .nle-container { display: flex; flex-direction: column; height: 100vh; width: 100vw; }
        
        /* Top Navigation Bar */
        .nle-header {
            background: #060606;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 14px;
            font-size: var(--font-sm);
            border-bottom: 1px solid var(--nle-border);
            z-index: 100;
        }
        .nle-menus { display: flex; gap: 12px; align-items: center; }
        .nle-menu-group { position: relative; }
        .nle-menu-item { background: transparent; border: 0; cursor: pointer; color: var(--nle-text); transition: color 0.2s; padding: 4px 6px; font-size: var(--font-sm); border-radius: 3px; }
        .nle-menu-item:hover, .nle-menu-item.active { color: #fff; background: var(--nle-hover); }
        .nle-menu-dropdown {
            background: #181818;
            border: 1px solid #333;
            box-shadow: 0 12px 36px rgba(0,0,0,0.8);
            display: none;
            left: 0;
            min-width: 210px;
            padding: 6px;
            position: absolute;
            /* Keep the dropdown touching its trigger so hover stays active. */
            top: 100%;
            z-index: 500;
            border-radius: 4px;
        }
        .nle-menu-group.is-open .nle-menu-dropdown { display: block; }
        .nle-menu-group.is-open > .nle-menu-item { color: #fff; background: var(--nle-hover); }
        .nle-menu-link {
            align-items: center;
            background: transparent;
            border: 0;
            color: #ccc;
            display: flex;
            font-size: 11px;
            gap: 8px;
            justify-content: space-between;
            padding: 6px 10px;
            text-decoration: none;
            width: 100%;
            border-radius: 3px;
            cursor: pointer;
        }
        .nle-menu-link:hover { background: var(--nle-active); color: #000; font-weight: 500; }
        .nle-menu-link[disabled] { color: #555; cursor: not-allowed; background: transparent; }
        .nle-menu-separator { border-top: 1px solid #333; margin: 5px 0; }
        
        /* Workspace Tabs */
        .workspace-tabs { display: flex; gap: 3px; background: #111; padding: 2px 4px; border-radius: 4px; border: 1px solid #222; }
        .workspace-tab {
            color: #777;
            cursor: pointer;
            font-size: 10px;
            font-weight: 600;
            padding: 4px 11px;
            border-radius: 3px;
            transition: all 0.15s ease;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .workspace-tab:hover { color: #ddd; background: #222; }
        .workspace-tab.active { color: #000; background: var(--nle-active); font-weight: 700; box-shadow: 0 0 10px var(--nle-active-glow); }

        /* Workspace Panels Layout */
        .nle-workspace-view { display: none; flex: 1; height: calc(62vh - 38px); overflow: hidden; }
        .nle-workspace-view.active { display: flex; }
        
        .nle-panel { background: var(--nle-panel); border-right: 1px solid var(--nle-border); border-bottom: 1px solid var(--nle-border); display: flex; flex-direction: column; }
        .nle-panel-header { background: var(--nle-panel-header); font-size: var(--font-sm); padding: 8px 12px; border-bottom: 1px solid var(--nle-border); display: flex; justify-content: space-between; align-items: center; font-weight: 600; color: var(--nle-text-light); }
        .nle-panel-content { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 12px; font-size: var(--font-sm); }
        
        /* Panel Widths */
        .panel-properties { width: 350px; min-width: 330px; }
        .panel-playlist { width: 270px; min-width: 250px; }
        .panel-monitor { flex: 1; display: flex; flex-direction: column; background: #000; }
        .panel-audio-meter { width: 210px; min-width: 200px; border-right: none; }

        /* Inspector Tabs */
        .inspector-tabs { display: flex; border-bottom: 1px solid var(--nle-border); background: #111; overflow-x: auto; }
        .insp-tab { flex: 1; text-align: center; padding: 7px 4px; cursor: pointer; font-size: 9px; font-weight: 600; color: #666; border-bottom: 2px solid transparent; text-transform: uppercase; white-space: nowrap; }
        .insp-tab.active { color: var(--nle-active); border-bottom: 2px solid var(--nle-active); background: var(--nle-panel); }
        .insp-content { display: none; }
        .insp-content.active { display: block; }

        /* Forms & Inputs */
        .input-group-custom { display: flex; align-items: center; margin-bottom: 8px; justify-content: space-between; }
        .input-label { width: 44%; font-size: var(--font-sm); color: #aaa; }
        .nle-input, .nle-select, .nle-textarea { background: #080808; border: 1px solid #333; color: #ddd; font-size: var(--font-sm); padding: 4px 8px; border-radius: 3px; font-family: 'Inter', sans-serif; transition: border 0.2s; }
        .nle-input:focus, .nle-select:focus, .nle-textarea:focus { outline: none; border-color: var(--nle-active); box-shadow: 0 0 6px var(--nle-active-glow); }
        
        .keyframe-icon { color: #555; cursor: pointer; font-size: 11px; margin-left: 6px; transition: color 0.2s; }
        .keyframe-icon:hover, .keyframe-icon.active { color: var(--nle-danger); text-shadow: 0 0 6px rgba(231, 76, 60, 0.6); }

        /* Buttons */
        .nle-btn { background: #222; border: 1px solid #383838; color: #ccc; padding: 5px 12px; font-size: var(--font-sm); cursor: pointer; border-radius: 3px; font-weight: 500; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; }
        .nle-btn:hover { background: #333; color: #fff; border-color: #555; }
        .nle-btn-primary { background: rgba(13, 202, 240, 0.15); border-color: var(--nle-active); color: var(--nle-active); font-weight: 600; }
        .nle-btn-primary:hover { background: var(--nle-active); color: #000; box-shadow: 0 0 10px var(--nle-active-glow); }

        /* Timeline Section */
        .nle-timeline-area { height: 38vh; background: var(--nle-bg); border-top: 1px solid #000; display: flex; flex-direction: column; }
        
        /* Timeline Toolbar */
        .timeline-toolbar { height: 38px; background: #151515; border-bottom: 1px solid var(--nle-border); display: flex; align-items: center; padding: 0 12px; gap: 8px; font-size: 13px; }
        .tool-icon { width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; border-radius: 3px; cursor: pointer; color: #888; transition: all 0.15s; }
        .tool-icon:hover { background: var(--nle-hover); color: #fff; }
        .tool-icon.active { background: #2a2a2a; color: var(--nle-active); border: 1px solid #444; }
        .tool-divider { width: 1px; height: 18px; background: #333; margin: 0 4px; }

        .timeline-ruler { height: 22px; background: #181818; border-bottom: 1px solid var(--nle-border); display: flex; font-size: var(--font-xs); color: #777; padding-left: 150px; font-family: monospace; position: relative; cursor: pointer; }
        .timeline-ruler-tick { flex: 1; border-left: 1px solid #333; padding-left: 4px; display: flex; flex-direction: column; justify-content: flex-end; padding-bottom: 2px; }
        
        .timeline-tracks-container { display: flex; flex: 1; overflow: auto; position: relative; }
        .timeline-headers { width: 150px; background: #171717; position: sticky; left: 0; z-index: 20; border-right: 1px solid var(--nle-border); box-shadow: 2px 0 6px rgba(0,0,0,0.3); }
        
        .track-header { height: 56px; border-bottom: 1px solid var(--nle-border); padding: 6px 10px; display: flex; flex-direction: column; justify-content: space-between; font-size: 11px; }
        .track-controls { display: flex; gap: 4px; margin-top: 2px; }
        .btn-track { background: #202020; border: 1px solid #333; color: #888; font-size: 9px; width: 22px; height: 20px; display: flex; align-items: center; justify-content: center; border-radius: 2px; cursor: pointer; font-weight: bold; }
        .btn-track.m-active { background: var(--nle-danger); color: #fff; border-color: var(--nle-danger); }
        .btn-track.s-active { background: #ffc107; color: #000; border-color: #ffc107; }
        
        .timeline-grid { flex: 1; background: #0e0e0e; display: flex; flex-direction: column; position: relative; min-width: 3200px; }
        .track-row { height: 56px; border-bottom: 1px solid #1e1e1e; display: flex; align-items: center; padding-left: 2px; position: relative; }
        
        .clip-block { height: 46px; margin-right: 2px; border-radius: 3px; font-size: 10px; padding: 4px 6px; overflow: hidden; position: relative; color: #fff; cursor: pointer; text-decoration: none; box-shadow: inset 0 1px 0 rgba(255,255,255,0.15); transition: transform 0.1s, border-color 0.1s; }
        .clip-block:hover { transform: translateY(-1px); border-color: #fff !important; }
        .clip-video { background: var(--nle-clip-bg); border: 1px solid #10386b; }
        .clip-audio { background: var(--nle-audio-clip-bg); border: 1px solid #1a422b; }
        .clip-active { border: 1px solid #0dcaf0 !important; box-shadow: 0 0 8px var(--nle-active-glow), inset 0 1px 0 rgba(255,255,255,0.4); }
        .clip-line { position: absolute; top: 50%; left: 0; right: 0; height: 1px; background: rgba(255,255,255,0.35); pointer-events: none; }

        /* Draggable Playhead */
        .timeline-playhead {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 1px;
            background: var(--nle-danger);
            z-index: 60;
            pointer-events: none;
            box-shadow: 0 0 6px rgba(231, 76, 60, 0.8);
        }
        .timeline-playhead-head {
            position: absolute;
            top: -12px;
            left: -6px;
            width: 13px;
            height: 12px;
            background: var(--nle-danger);
            clip-path: polygon(0 0, 100% 0, 100% 60%, 50% 100%, 0 60%);
        }

        /* Scope Letterbox Overlay */
        .scope-mask-top, .scope-mask-bottom {
            position: absolute;
            left: 0;
            right: 0;
            background: #000;
            z-index: 15;
            transition: height 0.25s ease;
            pointer-events: none;
        }
        .scope-mask-top { top: 0; height: 0%; }
        .scope-mask-bottom { bottom: 0; height: 0%; }

        /* Pillarbox for 9:16 Vertical */
        .scope-mask-left, .scope-mask-right {
            position: absolute;
            top: 0;
            bottom: 0;
            background: rgba(0,0,0,0.85);
            z-index: 15;
            transition: width 0.25s ease;
            pointer-events: none;
            backdrop-filter: blur(8px);
        }
        .scope-mask-left { left: 0; width: 0%; }
        .scope-mask-right { right: 0; width: 0%; }

        /* Sound FX Button */
        .sfx-card {
            background: #181818;
            border: 1px solid #2e2e2e;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .sfx-card:hover {
            border-color: var(--nle-active);
            background: #202020;
            transform: translateY(-2px);
        }
        .sfx-card:active {
            transform: scale(0.97);
            background: var(--nle-active);
            color: #000;
        }

        /* Video Canvas */
        .video-canvas-container {
            width: 100%;
            height: 100%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #000;
        }

        .lut-pill {
            padding: 4px 10px;
            background: #1c1c1c;
            border: 1px solid #333;
            color: #bbb;
            border-radius: 12px;
            font-size: 10px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .lut-pill:hover, .lut-pill.active {
            background: var(--nle-active);
            color: #000;
            font-weight: 700;
            border-color: var(--nle-active);
        }

        /* Audio Mixer */
        .mixer-strip { display: grid; grid-template-columns: 24px 1fr 1fr; gap: 6px; align-items: stretch; min-height: 190px; }
        .meter-scale { display: flex; flex-direction: column; justify-content: space-between; color: #555; font-size: 8px; font-family: monospace; text-align: right; padding-right: 2px; }
        .meter-shell { background: #080808; border: 1px solid #222; position: relative; overflow: hidden; border-radius: 2px; }
        .meter-fill { position: absolute; bottom: 0; left: 0; width: 100%; height: 4%; background: linear-gradient(to top, #2ecc71 0%, #2ecc71 65%, #f1c40f 82%, #e74c3c 100%); transition: height 0.08s linear; }
        .meter-peak { position: absolute; left: 0; width: 100%; height: 2px; background: #fff; bottom: 4%; opacity: 0.9; }
        .mixer-control-row { display: flex; gap: 4px; justify-content: center; margin-top: 8px; }
        .mixer-mini-btn { background: #1c1c1c; border: 1px solid #333; color: #888; font-size: 9px; height: 22px; min-width: 26px; border-radius: 2px; font-weight: 700; }
        .mixer-mini-btn:hover { border-color: #666; color: #fff; }
        .mixer-mini-btn.active-muted { background: var(--nle-danger); border-color: var(--nle-danger); color: #fff; }
        .mixer-mini-btn.active-solo { background: #ffc107; border-color: #ffc107; color: #000; }
        .mixer-readout { background: #080808; border: 1px solid #252525; color: var(--nle-active); font-family: monospace; font-size: 10px; padding: 3px 6px; text-align: center; border-radius: 2px; }

        /* Custom Scrollbars */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #0a0a0a; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #282828; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #444; }

        /* Transition Library & FX Suite */
        .trans-card {
            background: #121212;
            border: 1px solid #282828;
            border-radius: 3px;
            padding: 7px 9px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .trans-card:hover {
            background: #1a1a1a;
            border-color: #444;
            transform: translateX(2px);
        }
        .trans-card.active {
            background: #102129;
            border-color: var(--nle-active) !important;
            box-shadow: 0 0 10px rgba(13, 202, 240, 0.28);
        }
        .trans-title {
            color: #fff;
            font-size: 10px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            line-height: 1.2;
        }
        .trans-sub {
            color: #777;
            font-size: 8.5px;
            font-family: 'Inter', sans-serif;
            line-height: 1.2;
            margin-top: 1px;
        }
        .trans-cat-pill {
            background: #161616;
            border: 1px solid #333;
            color: #888;
            font-size: 8.5px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.15s ease;
            text-transform: uppercase;
        }
        .trans-cat-pill:hover, .trans-cat-pill.active {
            background: var(--nle-active);
            color: #000;
            border-color: var(--nle-active);
            box-shadow: 0 0 6px var(--nle-active-glow);
        }

        /* Video Monitor Dynamic Transition FX Overlay */
        .trans-fx-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 18;
            opacity: 0;
            transition: opacity 0.1s ease;
        }

        /* Cinematic VFX Layers & Optical Overlays */
        .vfx-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 16;
            display: none;
        }
        .vfx-grain-active {
            display: block;
            background-image: radial-gradient(rgba(255,255,255,0.08) 1px, transparent 0);
            background-size: 3px 3px;
            opacity: 0.7;
            mix-blend-mode: overlay;
        }
        .vfx-crt-active {
            display: block;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.45) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.04), rgba(0, 255, 0, 0.02), rgba(0, 0, 255, 0.04));
            background-size: 100% 3px, 6px 100%;
            pointer-events: none;
        }
        .vfx-halation-active {
            display: block;
            box-shadow: inset 0 0 80px rgba(255, 60, 20, 0.25);
            mix-blend-mode: screen;
        }
        .vfx-flare-active {
            display: block;
            background: linear-gradient(180deg, transparent 48%, rgba(13, 202, 240, 0.7) 49.5%, rgba(255, 255, 255, 0.95) 50%, rgba(13, 202, 240, 0.7) 50.5%, transparent 52%);
            mix-blend-mode: screen;
            opacity: 0.6;
        }
        .vfx-tiltshift-active {
            display: block;
            backdrop-filter: blur(4px);
            mask-image: linear-gradient(to bottom, black 0%, transparent 25%, transparent 75%, black 100%);
            -webkit-mask-image: linear-gradient(to bottom, black 0%, transparent 25%, transparent 75%, black 100%);
        }

        /* 3D LUT Color Cards */
        .lut-card {
            background: #141414;
            border: 1px solid #282828;
            border-radius: 3px;
            padding: 7px 9px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .lut-card:hover {
            background: #1e1e1e;
            border-color: #555;
            transform: translateY(-1px);
        }
        .lut-card.active {
            background: #12222a;
            border-color: var(--nle-active) !important;
            box-shadow: 0 0 8px rgba(13, 202, 240, 0.3);
        }
        .lut-preview-swatch {
            width: 100%;
            height: 4px;
            border-radius: 2px;
            margin-top: 4px;
        }

        @keyframes gateWeaveAnim {
            0% { transform: translate(0, 0); }
            25% { transform: translate(0.5px, -0.4px); }
            50% { transform: translate(-0.4px, 0.5px); }
            75% { transform: translate(0.3px, 0.3px); }
            100% { transform: translate(0, 0); }
        }
        .gate-weave-active {
            animation: gateWeaveAnim 0.12s infinite;
        }
    </style>
</head>
<body>

<div class="nle-container">

    @php
        $scenes = $project->scenes->sortBy('order_index')->values();
        $secondsPerScene = 15;
        $clipWidth = 180;
        $timelineSeconds = max(30, $scenes->count() * $secondsPerScene);
        $rulerStep = 5;
        $activeSceneId = request('active_scene');
        $currentScene = $activeSceneId ? $scenes->firstWhere('id', (int) $activeSceneId) : null;
        $currentScene = $currentScene ?: $scenes->first();
        $currentSceneId = $currentScene?->id;
        $currentSceneIndex = $currentScene ? max(0, $scenes->search(fn ($scene) => $scene->id === $currentScene->id)) : 0;
        $playheadLeft = $currentSceneIndex * ($clipWidth + 2);
        $formatTimecode = fn ($seconds) => '01:00:' . str_pad((string) floor($seconds), 2, '0', STR_PAD_LEFT) . ':00';
        $sceneFileName = fn ($scene) => $scene->video_path ? basename($scene->video_path) : 'No rendered video';
        $sceneStatus = fn ($scene) => strtoupper($scene->status ?? 'Draft');
    @endphp
    
    <!-- Top Header Bar -->
    <div class="nle-header">
        <div class="nle-menus">
            <span class="fw-bold text-white me-2" style="font-family: 'Syncopate'; font-size: 11px; letter-spacing: 1px;">
                VOID SHADOW <span class="text-info">STUDIO</span>
            </span>
            
            <div class="nle-menu-group">
                <button type="button" class="nle-menu-item">File</button>
                <div class="nle-menu-dropdown">
                    <a class="nle-menu-link" href="{{ route('projects.show', $project) }}"><span>Project Overview</span><small>Esc</small></a>
                    <a class="nle-menu-link" href="{{ route('projects.export-xml', $project) }}"><span>Export FCP XML</span><small>XML</small></a>
                    @if($currentScene)
                        <a class="nle-menu-link" href="{{ route('projects.download-video', ['project' => $project, 'quality' => 'original', 'scene_id' => $currentScene->id]) }}"><span>Download Scene MP4</span><small>MP4</small></a>
                    @endif
                    <button type="button" class="nle-menu-link" onclick="takeSnapshot()"><span>Capture 4K PNG Still</span><small>Snap</small></button>
                    <button type="button" class="nle-menu-link" onclick="document.getElementById('batchRenderForm').submit()"><span>Batch Render All</span><small>Batch</small></button>
                    <div class="nle-menu-separator"></div>
                    <a class="nle-menu-link text-danger" href="{{ route('projects.show', $project) }}"><span>Exit Studio</span><small>Exit</small></a>
                </div>
            </div>

            <div class="nle-menu-group">
                <button type="button" class="nle-menu-item">Edit</button>
                <div class="nle-menu-dropdown">
                    <button type="button" class="nle-menu-link" onclick="seekToStart()"><span>Go to Start</span><small>Home</small></button>
                    <button type="button" class="nle-menu-link" onclick="stepFrame(-1)"><span>Step -1 Frame</span><small>←</small></button>
                    <button type="button" class="nle-menu-link" onclick="stepFrame(1)"><span>Step +1 Frame</span><small>→</small></button>
                    <button type="button" class="nle-menu-link" onclick="togglePlay()"><span>Play / Pause</span><small>Space</small></button>
                    <div class="nle-menu-separator"></div>
                    <button type="button" class="nle-menu-link" onclick="setSpeed(0.5)"><span>Slow Motion 0.5x</span><small>J</small></button>
                    <button type="button" class="nle-menu-link" onclick="setSpeed(1.0)"><span>Normal Speed 1.0x</span><small>K</small></button>
                    <button type="button" class="nle-menu-link" onclick="setSpeed(2.0)"><span>Fast Speed 2.0x</span><small>L</small></button>
                </div>
            </div>

            <div class="nle-menu-group">
                <button type="button" class="nle-menu-item">Cinematography</button>
                <div class="nle-menu-dropdown">
                    <button type="button" class="nle-menu-link" onclick="setScopeMask('16x9')"><span>16:9 Standard Cinema</span><small>1.78:1</small></button>
                    <button type="button" class="nle-menu-link" onclick="setScopeMask('anamorphic')"><span>2.39:1 Anamorphic Scope</span><small>Scope</small></button>
                    <button type="button" class="nle-menu-link" onclick="setScopeMask('vertical')"><span>9:16 Vertical Phone (Shorts)</span><small>9:16</small></button>
                    <button type="button" class="nle-menu-link" onclick="setScopeMask('classic43')"><span>4:3 Academy Retro Ratio</span><small>4:3</small></button>
                </div>
            </div>

            <div class="nle-menu-group">
                <button type="button" class="nle-menu-item">AI Engine</button>
                <div class="nle-menu-dropdown">
                    <button type="button" class="nle-menu-link" onclick="switchInspTab('ai'); switchWorkspace('edit');"><span>Wan 2.1 Prompt Studio</span><small>AI</small></button>
                    <button type="button" class="nle-menu-link" onclick="enhancePrompt()"><span>Enhance Prompt Style</span><small>Magic</small></button>
                    <button type="button" class="nle-menu-link" onclick="randomizeSeed()"><span>Randomize AI Seed</span><small>Seed</small></button>
                </div>
            </div>
        </div>
        
        <!-- Center Workspaces Bar -->
        <div class="workspace-tabs">
            <div class="workspace-tab active" data-workspace="edit" onclick="switchWorkspace('edit')">EDIT</div>
            <div class="workspace-tab" data-workspace="color" onclick="switchWorkspace('color')"><i class="bi bi-palette me-1"></i>COLOR</div>
            <div class="workspace-tab" data-workspace="fairlight" onclick="switchWorkspace('fairlight')"><i class="bi bi-soundwave me-1"></i>FAIRLIGHT</div>
            <div class="workspace-tab" data-workspace="deliver" onclick="switchWorkspace('deliver')"><i class="bi bi-box-arrow-up-right me-1"></i>DELIVER</div>
        </div>

        <!-- Right Tools -->
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-dark border-secondary text-info py-0 px-2" style="font-size: 10px;" onclick="takeSnapshot()" title="Capture 4K PNG Frame Snapshot">
                <i class="bi bi-camera me-1"></i> SNAP
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary text-white py-0 px-2" style="font-size: 10px;" onclick="openShortcutsModal()" title="Shortcuts (?)">
                <i class="bi bi-keyboard"></i>
            </button>
            <a href="{{ route('projects.timeline', $project) }}" class="btn btn-sm btn-outline-light rounded-1 py-0 px-2" style="font-size: 10px;">TIMELINE</a>
            <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-danger rounded-1 py-0 px-2 fw-bold" style="font-size: 10px;">EXIT</a>
        </div>
    </div>

    <form id="batchRenderForm" action="{{ route('projects.render-batch', $project) }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- ================================================================= -->
    <!-- 1. EDIT WORKSPACE                                                 -->
    <!-- ================================================================= -->
    <div id="workspace-edit" class="nle-workspace-view active">
        
        <!-- Left: Inspector with Expanded Option Tabs -->
        <div class="nle-panel panel-properties">
            <div class="nle-panel-header">
                <span>INSPECTOR</span>
                <div class="d-flex gap-2 text-secondary" style="font-size: 11px;">
                    <i class="bi bi-sliders"></i>
                </div>
            </div>
            
            <div class="inspector-tabs">
                <div class="insp-tab active" onclick="switchInspTab('video')">Transform</div>
                <div class="insp-tab" onclick="switchInspTab('vfx')"><i class="bi bi-stars me-1"></i>VFX</div>
                <div class="insp-tab" onclick="switchInspTab('titles')"><i class="bi bi-type me-1"></i>Titles</div>
                <div class="insp-tab" onclick="switchInspTab('transitions')"><i class="bi bi-intersect me-1"></i>Transitions</div>
                <div class="insp-tab" onclick="switchInspTab('ai')"><i class="bi bi-cpu-fill me-1"></i>AI Engine</div>
            </div>

            <div class="nle-panel-content custom-scrollbar">
                @if($currentScene)
                    
                    <!-- Tab 1: Video Transform & Motion -->
                    <div id="insp-video" class="insp-content active">
                        <div class="mb-3 p-2 border border-secondary bg-black">
                            <div class="text-white fw-bold font-mono mb-1" style="font-size: 11px;">SEQ #{{ str_pad($currentScene->order_index, 3, '0', STR_PAD_LEFT) }}</div>
                            <div class="text-secondary text-truncate" title="{{ $sceneFileName($currentScene) }}" style="font-size: 10px;">
                                {{ $sceneFileName($currentScene) }}
                            </div>
                            <div style="font-size: 9px; color: {{ $currentScene->video_path ? '#2ecc71' : '#f1c40f' }};">
                                {{ $currentScene->video_path ? '● MEDIA ONLINE (15.00s)' : '○ ' . $sceneStatus($currentScene) }}
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3 pb-2 border-bottom border-secondary">
                            <span class="fw-bold text-white small">Transform Geometry</span>
                            <i class="bi bi-arrow-counterclockwise ms-auto text-secondary" style="cursor:pointer; font-size:10px;" onclick="resetTransform()" title="Reset"></i>
                        </div>

                        <div class="input-group-custom">
                            <label class="input-label">Scale / Zoom</label>
                            <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                <input type="range" class="form-range" id="zoomRange" min="50" max="250" value="100" oninput="applyTransform()">
                                <span class="font-mono text-white" id="zoomValue" style="font-size: 10px; width: 36px;">100%</span>
                            </div>
                        </div>
                        
                        <div class="input-group-custom">
                            <label class="input-label">Position X / Y</label>
                            <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                <input type="number" id="posX" class="nle-input m-0 w-50 font-mono" value="0" step="5" oninput="applyTransform()">
                                <input type="number" id="posY" class="nle-input m-0 w-50 font-mono" value="0" step="5" oninput="applyTransform()">
                            </div>
                        </div>

                        <div class="input-group-custom">
                            <label class="input-label">Rotation Angle</label>
                            <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                <input type="range" class="form-range" id="rotateRange" min="-180" max="180" value="0" oninput="applyTransform()">
                                <span class="font-mono text-white" id="rotateValue" style="font-size: 10px; width: 36px;">0°</span>
                            </div>
                        </div>

                        <div class="input-group-custom">
                            <label class="input-label">Opacity</label>
                            <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                <input type="range" class="form-range" id="opacityRange" min="0" max="100" value="100" oninput="applyTransform()">
                                <span class="font-mono text-white" id="opacityValue" style="font-size: 10px; width: 36px;">100%</span>
                            </div>
                        </div>

                        <div class="input-group-custom mt-3">
                            <label class="input-label">Ken Burns Motion</label>
                            <select id="kenBurnsPreset" class="nle-select" style="width: 55%;" onchange="applyKenBurns(this.value)">
                                <option value="none">None (Static)</option>
                                <option value="zoom_in">Slow Zoom In (Dolly)</option>
                                <option value="zoom_out">Slow Zoom Out (Pull)</option>
                                <option value="pan_lr">Cinematic Pan L-to-R</option>
                                <option value="pan_rl">Cinematic Pan R-to-L</option>
                            </select>
                        </div>

                        <div class="d-flex align-items-center mt-4 mb-3 pb-2 border-bottom border-secondary">
                            <span class="fw-bold text-white small">Speed & Shuttling</span>
                        </div>
                        <div class="d-flex gap-1 mb-3">
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-secondary p-1 flex-grow-1 speed-btn" onclick="setSpeed(0.25)">0.25x</button>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-secondary p-1 flex-grow-1 speed-btn" onclick="setSpeed(0.5)">0.5x</button>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-white active p-1 flex-grow-1 speed-btn" id="speed1x" onclick="setSpeed(1.0)">1.0x</button>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-secondary p-1 flex-grow-1 speed-btn" onclick="setSpeed(1.5)">1.5x</button>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-secondary p-1 flex-grow-1 speed-btn" onclick="setSpeed(2.0)">2.0x</button>
                        </div>
                    </div>

                    <!-- Tab 2: VFX & Optics Suite (Expanded) -->
                    <div id="insp-vfx" class="insp-content">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="text-info fw-bold uppercase mb-0" style="font-size: 9px; letter-spacing:1px;">
                                <i class="bi bi-stars me-1"></i> Cinematic VFX & Optics
                            </label>
                            <span class="badge bg-black border border-info text-info font-mono" style="font-size: 8px;">12 FX Processors</span>
                        </div>

                        <!-- Section 1: Film Stock & Analog Emulation -->
                        <div class="p-2 bg-black border border-secondary mb-2 rounded-1">
                            <div class="text-white fw-bold mb-2 font-mono" style="font-size: 9.5px;">
                                <i class="bi bi-film text-warning me-1"></i> FILM STOCK & ANALOG TEXTURE
                            </div>

                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input bg-black border-secondary" type="checkbox" id="vfxGrainToggle" onchange="updateVfxFilters()">
                                <label class="form-check-label text-white small font-mono" for="vfxGrainToggle" style="font-size: 10px;">Film Grain Overlay</label>
                            </div>
                            <div class="input-group-custom mb-2">
                                <label class="input-label" style="font-size: 8.5px;">Grain Stock</label>
                                <select id="vfxGrainStock" class="nle-select" style="width: 55%; font-size: 9px;" onchange="updateVfxFilters()">
                                    <option value="35mm">35mm Fine Eastman Grain</option>
                                    <option value="16mm">16mm Gritty Indie Grain</option>
                                    <option value="8mm">8mm Super8 Coarse Grain</option>
                                </select>
                            </div>

                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input bg-black border-secondary" type="checkbox" id="vfxGateWeaveToggle" onchange="updateVfxFilters()">
                                <label class="form-check-label text-white small font-mono" for="vfxGateWeaveToggle" style="font-size: 10px;">Gate Weave & Frame Jitter</label>
                            </div>
                        </div>

                        <!-- Section 2: Optical Lens Imperfections -->
                        <div class="p-2 bg-black border border-secondary mb-2 rounded-1">
                            <div class="text-white fw-bold mb-2 font-mono" style="font-size: 9.5px;">
                                <i class="bi bi-camera me-1 text-info"></i> OPTICAL LENS IMPERFECTIONS
                            </div>

                            <!-- Vignette Falloff -->
                            <div class="input-group-custom mb-1">
                                <label class="input-label" style="font-size: 8.5px;">Vignette Edge Falloff</label>
                                <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                    <input type="range" class="form-range" id="vfxVignetteRange" min="0" max="100" value="0" oninput="updateVfxFilters()">
                                    <span class="font-mono text-white" id="vfxVignetteVal" style="font-size: 9px; min-width: 24px;">0%</span>
                                </div>
                            </div>

                            <!-- Optical Halation -->
                            <div class="input-group-custom mb-1">
                                <label class="input-label" style="font-size: 8.5px;">Optical Halation (Red Glow)</label>
                                <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                    <input type="range" class="form-range" id="vfxHalationRange" min="0" max="100" value="0" oninput="updateVfxFilters()">
                                    <span class="font-mono text-white" id="vfxHalationVal" style="font-size: 9px; min-width: 24px;">0%</span>
                                </div>
                            </div>

                            <!-- Chromatic Aberration -->
                            <div class="input-group-custom mb-1">
                                <label class="input-label" style="font-size: 8.5px;">Chromatic Aberration</label>
                                <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                    <input type="range" class="form-range" id="vfxAberrationRange" min="0" max="100" value="0" oninput="updateVfxFilters()">
                                    <span class="font-mono text-white" id="vfxAberrationVal" style="font-size: 9px; min-width: 24px;">0%</span>
                                </div>
                            </div>

                            <!-- Anamorphic Blue Flare Streak -->
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input bg-black border-secondary" type="checkbox" id="vfxFlareToggle" onchange="updateVfxFilters()">
                                <label class="form-check-label text-white small font-mono" for="vfxFlareToggle" style="font-size: 10px;">Anamorphic Blue Flare Streak</label>
                            </div>
                        </div>

                        <!-- Section 3: Atmosphere & Diffusion -->
                        <div class="p-2 bg-black border border-secondary mb-2 rounded-1">
                            <div class="text-white fw-bold mb-2 font-mono" style="font-size: 9.5px;">
                                <i class="bi bi-cloud-fog2 me-1 text-success"></i> ATMOSPHERE & DIFFUSION
                            </div>

                            <!-- Pro-Mist Soft Glow Diffusion -->
                            <div class="input-group-custom mb-1">
                                <label class="input-label" style="font-size: 8.5px;">1/4 Black Pro-Mist Glow</label>
                                <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                    <input type="range" class="form-range" id="vfxProMistRange" min="0" max="100" value="0" oninput="updateVfxFilters()">
                                    <span class="font-mono text-white" id="vfxProMistVal" style="font-size: 9px; min-width: 24px;">0%</span>
                                </div>
                            </div>

                            <!-- Tilt-Shift Focus -->
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input bg-black border-secondary" type="checkbox" id="vfxTiltShiftToggle" onchange="updateVfxFilters()">
                                <label class="form-check-label text-white small font-mono" for="vfxTiltShiftToggle" style="font-size: 10px;">Tilt-Shift Depth Focus Blur</label>
                            </div>

                            <!-- CRT Scanlines -->
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input bg-black border-secondary" type="checkbox" id="vfxCrtToggle" onchange="updateVfxFilters()">
                                <label class="form-check-label text-white small font-mono" for="vfxCrtToggle" style="font-size: 10px;">CRT Phosphor Scanlines</label>
                            </div>
                        </div>

                        <!-- Section 4: Letterbox Scope Matte -->
                        <div class="p-2 bg-black border border-secondary mb-3 rounded-1">
                            <div class="text-white fw-bold mb-2 font-mono" style="font-size: 9.5px;">
                                <i class="bi bi-aspect-ratio me-1 text-primary"></i> LETTERBOX SCOPE MATTE
                            </div>

                            <div class="d-flex gap-1 mb-2">
                                <button type="button" class="btn btn-dark border-secondary p-0 px-2 text-white font-mono flex-fill" style="font-size: 8px;" onclick="setScopePreset(13)">2.39:1 Scope</button>
                                <button type="button" class="btn btn-dark border-secondary p-0 px-2 text-white font-mono flex-fill" style="font-size: 8px;" onclick="setScopePreset(6)">1.85:1 Flat</button>
                                <button type="button" class="btn btn-dark border-secondary p-0 px-2 text-white font-mono flex-fill" style="font-size: 8px;" onclick="setScopePreset(0)">Off (16:9)</button>
                            </div>

                            <div class="input-group-custom mb-0">
                                <label class="input-label" style="font-size: 8.5px;">Matte Height</label>
                                <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                    <input type="range" class="form-range" id="vfxLetterboxRange" min="0" max="25" value="0" oninput="updateLetterboxMatte(this.value)">
                                    <span class="font-mono text-white" id="vfxLetterboxVal" style="font-size: 9px; min-width: 24px;">0%</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm flex-fill font-mono" style="font-size: 10px;" onclick="resetVfxFilters()">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> RESET VFX
                            </button>
                            <button type="button" class="btn btn-info btn-sm text-black fw-bold flex-fill font-mono" style="font-size: 10px;" onclick="applyVfxToAllScenes()">
                                <i class="bi bi-check2-all me-1"></i> BATCH ALL
                            </button>
                        </div>
                    </div>

                    <!-- Tab 3: Live Titles & Lower-Thirds Generator -->
                    <div id="insp-titles" class="insp-content">
                        <label class="text-info fw-bold mb-2 uppercase" style="font-size: 9px; letter-spacing:1px;">Live Title & Subtitle Generator</label>
                        
                        <div class="mb-2">
                            <label class="input-label mb-1">Headline Text</label>
                            <input type="text" id="titleText" class="nle-input w-100" placeholder="e.g. COLOMBO NIGHTS" value="SCENE 01" oninput="updateLiveTitle()">
                        </div>

                        <div class="mb-2">
                            <label class="input-label mb-1">Subtitle / Character Line</label>
                            <input type="text" id="subtitleText" class="nle-input w-100" placeholder="e.g. DIRECTED BY AMILA" value="A SHADOW PRODUCTION" oninput="updateLiveTitle()">
                        </div>

                        <div class="input-group-custom mt-3">
                            <label class="input-label">Font Family</label>
                            <select id="titleFont" class="nle-select" style="width: 55%;" onchange="updateLiveTitle()">
                                <option value="'Syncopate', sans-serif">Syncopate (Cinema)</option>
                                <option value="'Bebas Neue', sans-serif">Bebas Neue (Bold)</option>
                                <option value="'JetBrains Mono', monospace">Terminal Mono</option>
                                <option value="'Outfit', sans-serif">Outfit (Modern)</option>
                            </select>
                        </div>

                        <div class="input-group-custom">
                            <label class="input-label">Position Style</label>
                            <select id="titlePosition" class="nle-select" style="width: 55%;" onchange="updateLiveTitle()">
                                <option value="center">Center Epic Title</option>
                                <option value="lower_third">Lower-Third Strap</option>
                                <option value="subtitle">Bottom Dialogue Subtitle</option>
                                <option value="watermark">Top Watermark</option>
                            </select>
                        </div>

                        <div class="input-group-custom">
                            <label class="input-label">Text Color</label>
                            <input type="color" id="titleColor" class="form-control form-control-color bg-black border-secondary p-0" style="width: 55%; height: 26px;" value="#ffffff" oninput="updateLiveTitle()">
                        </div>

                        <div class="input-group-custom">
                            <label class="input-label">Glow & Shadow</label>
                            <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                <input type="range" class="form-range" id="titleGlowRange" min="0" max="30" value="10" oninput="updateLiveTitle()">
                                <span class="font-mono text-white" id="titleGlowVal" style="font-size: 9px;">10px</span>
                            </div>
                        </div>

                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input bg-black border-secondary" type="checkbox" id="titleVisible" checked onchange="updateLiveTitle()">
                            <label class="form-check-label text-white small" for="titleVisible">Display Overlay on Monitor</label>
                        </div>
                    </div>

                    <!-- Tab 4: Transitions & Dissolves (Expanded FX Suite) -->
                    <div id="insp-transitions" class="insp-content">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="text-info fw-bold uppercase mb-0" style="font-size: 9px; letter-spacing:1px;">
                                <i class="bi bi-intersect me-1"></i> FX Transitions Library
                            </label>
                            <span class="badge bg-black border border-info text-info font-mono" style="font-size: 8px;" id="transCountBadge">22 Presets</span>
                        </div>

                        <!-- Instant Filter Search -->
                        <div class="position-relative mb-2">
                            <i class="bi bi-search position-absolute text-secondary" style="left: 8px; top: 6px; font-size: 10px;"></i>
                            <input type="text" id="transSearchInput" class="nle-input w-100 ps-4 py-1" style="font-size: 10px;" placeholder="Search transitions (e.g. burn, zoom, glitch)..." oninput="filterTransitions()">
                        </div>

                        <!-- Category Filter Pills -->
                        <div class="d-flex gap-1 mb-2 overflow-auto pb-1 custom-scrollbar" style="white-space: nowrap;" id="transCategoryTabs">
                            <button type="button" class="trans-cat-pill active" onclick="setTransCategory('all', this)">ALL</button>
                            <button type="button" class="trans-cat-pill" onclick="setTransCategory('dissolve', this)">DISSOLVES</button>
                            <button type="button" class="trans-cat-pill" onclick="setTransCategory('wipe', this)">WIPES</button>
                            <button type="button" class="trans-cat-pill" onclick="setTransCategory('motion', this)">MOTION</button>
                            <button type="button" class="trans-cat-pill" onclick="setTransCategory('cyber', this)">CYBER VFX</button>
                            <button type="button" class="trans-cat-pill" onclick="setTransCategory('light', this)">LIGHT & FLARES</button>
                        </div>

                        <!-- Transition Cards List -->
                        <div class="d-flex flex-column gap-1 mb-3 custom-scrollbar" id="transCardsContainer" style="max-height: 250px; overflow-y: auto; padding-right: 2px;">
                            <!-- Dissolves -->
                            <div class="trans-card active" data-category="dissolve" data-type="fade" onclick="selectTransition('fade', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-circle-half text-info fs-6"></i>
                                        <div>
                                            <div class="trans-title">Cross Dissolve / Dip to Black</div>
                                            <div class="trans-sub">Smooth linear luminance fade</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-info font-mono" style="font-size: 8px;">FADE</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="dissolve" data-type="flash" onclick="selectTransition('flash', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-lightning-charge-fill text-warning fs-6"></i>
                                        <div>
                                            <div class="trans-title">White Exposure Flash Burn</div>
                                            <div class="trans-sub">Overexposed high-key highlight burst</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-warning font-mono" style="font-size: 8px;">FLASH</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="dissolve" data-type="film_burn" onclick="selectTransition('film_burn', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-fire text-danger fs-6"></i>
                                        <div>
                                            <div class="trans-title">35mm Film Burn & Light Leak</div>
                                            <div class="trans-sub">Vintage celluloid heat flare sweep</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-danger font-mono" style="font-size: 8px;">35MM</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="dissolve" data-type="bloom" onclick="selectTransition('bloom', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-brightness-high text-success fs-6"></i>
                                        <div>
                                            <div class="trans-title">Dreamy Soft Glow Optical Bloom</div>
                                            <div class="trans-sub">Diffuse ethereal glow crossfade</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-success font-mono" style="font-size: 8px;">BLOOM</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="dissolve" data-type="color_dip" onclick="selectTransition('color_dip', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-palette-fill text-info fs-6"></i>
                                        <div>
                                            <div class="trans-title">Cyan Hue Tint Wash Dip</div>
                                            <div class="trans-sub">Colorized stylized immersion fade</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-info font-mono" style="font-size: 8px;">TINT</span>
                                </div>
                            </div>

                            <!-- Wipes & Splits -->
                            <div class="trans-card" data-category="wipe" data-type="wipe" onclick="selectTransition('wipe', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-layout-split text-primary fs-6"></i>
                                        <div>
                                            <div class="trans-title">Linear Push Slide Wipe</div>
                                            <div class="trans-sub">Directional frame displacement</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-primary font-mono" style="font-size: 8px;">SLIDE</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="wipe" data-type="barn_door" onclick="selectTransition('barn_door', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-layout-three-columns text-info fs-6"></i>
                                        <div>
                                            <div class="trans-title">Center Barn Door Split</div>
                                            <div class="trans-sub">Dual-wing shutter expansion</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-info font-mono" style="font-size: 8px;">SPLIT</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="wipe" data-type="radial_iris" onclick="selectTransition('radial_iris', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-bullseye text-warning fs-6"></i>
                                        <div>
                                            <div class="trans-title">Circular Iris Zoom Wipe</div>
                                            <div class="trans-sub">Retro cinematic spotlight aperture</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-warning font-mono" style="font-size: 8px;">IRIS</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="wipe" data-type="diagonal_slice" onclick="selectTransition('diagonal_slice', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-slash-lg text-danger fs-6"></i>
                                        <div>
                                            <div class="trans-title">45° Blade Diagonal Slash</div>
                                            <div class="trans-sub">High-speed geometric slash wipe</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-danger font-mono" style="font-size: 8px;">SLASH</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="wipe" data-type="venetian_blinds" onclick="selectTransition('venetian_blinds', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-view-stacked text-secondary fs-6"></i>
                                        <div>
                                            <div class="trans-title">Venetian Blinds Shutter Slices</div>
                                            <div class="trans-sub">Multi-slat cinematic shutter reveal</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-secondary font-mono" style="font-size: 8px;">SLATS</span>
                                </div>
                            </div>

                            <!-- Motion & 3D -->
                            <div class="trans-card" data-category="motion" data-type="zoom" onclick="selectTransition('zoom', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-zoom-in text-success fs-6"></i>
                                        <div>
                                            <div class="trans-title">Whip Zoom Blur (Crash In)</div>
                                            <div class="trans-sub">Extreme velocity focal slam</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-success font-mono" style="font-size: 8px;">ZOOM+</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="motion" data-type="zoom_out" onclick="selectTransition('zoom_out', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-zoom-out text-info fs-6"></i>
                                        <div>
                                            <div class="trans-title">Whip Zoom Pullback (Crash Out)</div>
                                            <div class="trans-sub">Rapid wide reverse acceleration</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-info font-mono" style="font-size: 8px;">ZOOM-</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="motion" data-type="vortex_spin" onclick="selectTransition('vortex_spin', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-arrow-repeat text-warning fs-6"></i>
                                        <div>
                                            <div class="trans-title">360° Spin Vortex Swirl</div>
                                            <div class="trans-sub">High-RPM rotational barrel roll</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-warning font-mono" style="font-size: 8px;">SPIN</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="motion" data-type="motion_blur_push" onclick="selectTransition('motion_blur_push', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-arrows-expand-vertical text-primary fs-6"></i>
                                        <div>
                                            <div class="trans-title">Directional Motion Blur Push</div>
                                            <div class="trans-sub">Linear optic streak translation</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-primary font-mono" style="font-size: 8px;">PUSH</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="motion" data-type="camera_shake" onclick="selectTransition('camera_shake', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-soundwave text-danger fs-6"></i>
                                        <div>
                                            <div class="trans-title">Impact Earthquake Jolt Shake</div>
                                            <div class="trans-sub">Heavy kinetic handheld hit tremor</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-danger font-mono" style="font-size: 8px;">SHAKE</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="motion" data-type="cube_flip" onclick="selectTransition('cube_flip', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-box text-light fs-6"></i>
                                        <div>
                                            <div class="trans-title">3D Perspective Cube Turn</div>
                                            <div class="trans-sub">Three-dimensional spatial yaw</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-light font-mono" style="font-size: 8px;">3D CUBE</span>
                                </div>
                            </div>

                            <!-- Cyber VFX & Glitch -->
                            <div class="trans-card" data-category="cyber" data-type="glitch" onclick="selectTransition('glitch', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-activity text-danger fs-6"></i>
                                        <div>
                                            <div class="trans-title">Cyberpunk RGB Glitch Hit</div>
                                            <div class="trans-sub">Chromatic aberration frame tear</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-danger font-mono" style="font-size: 8px;">GLITCH</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="cyber" data-type="vhs_roll" onclick="selectTransition('vhs_roll', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-cassette-fill text-warning fs-6"></i>
                                        <div>
                                            <div class="trans-title">Analog VHS Static Tape Roll</div>
                                            <div class="trans-sub">VCR tracking noise head glitch</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-warning font-mono" style="font-size: 8px;">VHS</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="cyber" data-type="pixelate" onclick="selectTransition('pixelate', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-grid-3x3-gap-fill text-info fs-6"></i>
                                        <div>
                                            <div class="trans-title">Digital Matrix Pixelate Mosaic</div>
                                            <div class="trans-sub">8-bit pixel block quantization</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-info font-mono" style="font-size: 8px;">PIXEL</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="cyber" data-type="thermal_flash" onclick="selectTransition('thermal_flash', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-eye-fill text-danger fs-6"></i>
                                        <div>
                                            <div class="trans-title">Thermal Invert / X-Ray Flash</div>
                                            <div class="trans-sub">Predator heat-map negative shock</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-danger font-mono" style="font-size: 8px;">THERMAL</span>
                                </div>
                            </div>

                            <!-- Light & Flare -->
                            <div class="trans-card" data-category="light" data-type="laser_streak" onclick="selectTransition('laser_streak', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-slash text-info fs-6"></i>
                                        <div>
                                            <div class="trans-title">Anamorphic Blue Laser Streak</div>
                                            <div class="trans-sub">Horizontal sci-fi lens flare flash</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-info font-mono" style="font-size: 8px;">STREAK</span>
                                </div>
                            </div>

                            <div class="trans-card" data-category="light" data-type="prism_split" onclick="selectTransition('prism_split', this)">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-rainbow text-warning fs-6"></i>
                                        <div>
                                            <div class="trans-title">Prismatic Optical Refraction</div>
                                            <div class="trans-sub">Rainbow spectral crystal split</div>
                                        </div>
                                    </div>
                                    <span class="badge bg-dark border border-secondary text-warning font-mono" style="font-size: 8px;">PRISM</span>
                                </div>
                            </div>
                        </div>

                        <!-- Parameters Customization Suite -->
                        <div class="p-2 border border-secondary bg-black mb-3 rounded-1">
                            <div class="text-white fw-bold mb-2 font-mono" style="font-size: 10px;">
                                <i class="bi bi-sliders text-info me-1"></i> PARAMETER TUNER
                            </div>

                            <!-- Direction -->
                            <div class="input-group-custom">
                                <label class="input-label">Direction</label>
                                <select id="transDirection" class="nle-select" style="width: 55%; font-size: 9px;">
                                    <option value="lr">Left → Right (Standard)</option>
                                    <option value="rl">Right → Left</option>
                                    <option value="tb">Top → Bottom</option>
                                    <option value="bt">Bottom → Top</option>
                                    <option value="center">Center Radial Out</option>
                                </select>
                            </div>

                            <!-- Easing -->
                            <div class="input-group-custom">
                                <label class="input-label">Easing Curve</label>
                                <select id="transEasing" class="nle-select" style="width: 55%; font-size: 9px;">
                                    <option value="ease-in-out">Cinematic Smooth (Ease-in-out)</option>
                                    <option value="cubic-bezier(0.16, 1, 0.3, 1)">Snappy Exponential</option>
                                    <option value="linear">Linear Direct</option>
                                    <option value="cubic-bezier(0.68, -0.55, 0.27, 1.55)">Elastic Spring Bounce</option>
                                </select>
                            </div>

                            <!-- Length Slider & Preset Buttons -->
                            <div class="input-group-custom">
                                <label class="input-label">Length (Duration)</label>
                                <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                    <input type="range" class="form-range" id="transDuration" min="100" max="3000" step="50" value="800" oninput="updateTransDurationVal(this.value)">
                                    <span class="font-mono text-white" id="transDurationVal" style="font-size: 9px; min-width: 28px;">0.8s</span>
                                </div>
                            </div>
                            <div class="d-flex gap-1 mb-2">
                                <button type="button" class="btn btn-dark border-secondary p-0 px-2 text-white font-mono flex-fill" style="font-size: 8px;" onclick="setTransDurationPreset(300)">0.3s Quick</button>
                                <button type="button" class="btn btn-dark border-secondary p-0 px-2 text-white font-mono flex-fill" style="font-size: 8px;" onclick="setTransDurationPreset(600)">0.6s Dynamic</button>
                                <button type="button" class="btn btn-dark border-secondary p-0 px-2 text-info font-mono flex-fill" style="font-size: 8px;" onclick="setTransDurationPreset(1000)">1.0s Cinema</button>
                                <button type="button" class="btn btn-dark border-secondary p-0 px-2 text-white font-mono flex-fill" style="font-size: 8px;" onclick="setTransDurationPreset(2000)">2.0s Slow</button>
                            </div>

                            <!-- Intensity / Blur Slider -->
                            <div class="input-group-custom">
                                <label class="input-label">Blur & Intensity</label>
                                <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                    <input type="range" class="form-range" id="transIntensity" min="20" max="100" value="80" oninput="document.getElementById('transIntensityVal').textContent = this.value + '%'">
                                    <span class="font-mono text-white" id="transIntensityVal" style="font-size: 9px; min-width: 28px;">80%</span>
                                </div>
                            </div>

                            <!-- Sound FX Sync -->
                            <div class="border-top border-secondary pt-2 mt-2">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input bg-black border-secondary" type="checkbox" id="transAudioSync" checked>
                                    <label class="form-check-label text-white small" for="transAudioSync" style="font-size: 9px;">
                                        <i class="bi bi-volume-up-fill text-warning me-1"></i> Sync Audio Whoosh FX on Cut
                                    </label>
                                </div>
                                <div class="input-group-custom mb-0">
                                    <label class="input-label">Audio Sound Profile</label>
                                    <select id="transAudioProfile" class="nle-select" style="width: 55%; font-size: 9px;">
                                        <option value="auto">Auto-Matched to FX</option>
                                        <option value="laser_whoosh">Cinematic Air Whoosh</option>
                                        <option value="laser_hit">Cyber Laser Zap</option>
                                        <option value="bass_boom">Deep Sub-Bass Drop</option>
                                        <option value="vhs_glitch">Analog Tape Static</option>
                                        <option value="tension_riser">Tension Pitch Riser</option>
                                        <option value="none">Mute (Silent)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Action Triggers -->
                        <div class="d-flex flex-column gap-2">
                            <button type="button" class="btn btn-info text-black fw-bold p-2 font-mono w-100 shadow-sm" style="font-size: 11px;" onclick="playSelectedTransition()">
                                <i class="bi bi-play-circle-fill me-1"></i> PREVIEW TRANSITION ON MONITOR
                            </button>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-dark border-secondary text-white font-mono flex-fill p-1" style="font-size: 9px;" onclick="applyTransitionToClip('current')">
                                    <i class="bi bi-scissors text-info me-1"></i> Apply to Cut
                                </button>
                                <button type="button" class="btn btn-sm btn-dark border-secondary text-info font-mono flex-fill p-1" style="font-size: 9px;" onclick="applyTransitionToClip('all')">
                                    <i class="bi bi-collection-play me-1"></i> Apply to All Cuts
                                </button>
                            </div>
                        </div>

                        <!-- Toast Feedback -->
                        <div id="transToast" class="alert alert-info py-1 px-2 mt-2 font-mono text-center d-none" style="font-size: 9px;">
                            <i class="bi bi-check-circle me-1"></i> <span id="transToastMsg">Transition applied</span>
                        </div>
                    </div>

                    <!-- Tab 5: AI Engine & Studio Seeds -->
                    <div id="insp-ai" class="insp-content">
                        <form id="nle-engine-form" action="{{ route('scenes.update', $currentScene) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <input type="hidden" name="order_index" value="{{ $currentScene->order_index }}">
                            <input type="hidden" name="status" value="{{ $currentScene->status ?? 'Draft' }}">
                            <input type="hidden" name="preserve_characters" value="1">
                            <input type="hidden" name="return_to_videoeditor" value="1">
                            
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="text-info fw-bold uppercase mb-0" style="font-size: 9px; letter-spacing:1px;">Wan 2.1 Action Prompt</label>
                                <button type="button" class="btn btn-link text-info p-0 text-decoration-none small" style="font-size: 9px;" onclick="enhancePrompt()">
                                    <i class="bi bi-stars me-1"></i> Enhance
                                </button>
                            </div>
                            <textarea id="aiPromptTextarea" name="script_segment" class="nle-textarea w-100 mb-2 font-mono" rows="3">{{ $currentScene->script_segment }}</textarea>
                            
                            <label class="input-label mb-1">Cinematography Trajectory</label>
                            <select name="camera_motion" class="nle-select w-100 mb-2">
                                <option value="cinematic_dolly">Cinematic Dolly In (35mm Anamorphic)</option>
                                <option value="slow_pan">Slow Horizon Pan</option>
                                <option value="drone_orbit">Aerial 4K Drone Orbit</option>
                                <option value="dutch_angle">Dutch Angle 15° High-Tension</option>
                                <option value="handheld">Gritty Handheld Action</option>
                            </select>

                            <div class="input-group-custom">
                                <label class="input-label">AI Seed</label>
                                <div class="d-flex align-items-center gap-1" style="width: 55%;">
                                    <input type="text" id="aiSeedInput" name="generation_seed" class="nle-input m-0 w-75 font-mono" placeholder="-1 (Random)" value="-1">
                                    <button type="button" class="btn btn-sm btn-dark border-secondary p-1" onclick="randomizeSeed()" title="Randomize Seed">🎲</button>
                                </div>
                            </div>

                            <button type="submit" class="nle-btn w-100 mt-2 mb-2"><i class="bi bi-save me-1"></i>Save Prompt Config</button>
                        </form>

                        <form action="{{ route('scenes.render', $currentScene) }}" method="POST" class="mt-2 pt-2 border-top border-secondary">
                            @csrf
                            <button type="submit" class="nle-btn nle-btn-primary w-100 py-2 fw-bold">
                                <i class="bi bi-cpu-fill me-1"></i>Render AI Variation
                            </button>
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

        <!-- Middle-Left: Media Pool -->
        <div class="nle-panel panel-playlist border-end">
            <div class="nle-panel-header">
                <span>MEDIA BIN</span>
                <div class="d-flex gap-2 text-secondary" style="font-size: 11px;">
                    <i class="bi bi-film"></i>
                </div>
            </div>
            
            <div style="background: #111; padding: 6px 10px; font-size: 10px; border-bottom: 1px solid var(--nle-border); display:flex; align-items:center;">
                <i class="bi bi-folder-fill text-warning me-2"></i> {{ $project->title }} <i class="bi bi-chevron-right mx-1 text-secondary" style="font-size:8px;"></i> Media Pool
            </div>

            <div class="nle-panel-content custom-scrollbar" style="padding: 8px;">
                <div class="row g-2">
                    @forelse($scenes as $scene)
                        <div class="col-6">
                            <a href="{{ route('projects.videoeditor', $project) }}?active_scene={{ $scene->id }}" 
                               class="text-decoration-none d-block p-1" 
                               style="border: 1px solid {{ $currentSceneId == $scene->id ? 'var(--nle-active)' : '#242424' }}; background: #161616; border-radius: 3px;">
                                <div style="aspect-ratio: 16/9; background: #000; position: relative; overflow: hidden; display: flex; align-items:center; justify-content:center;">
                                    @if($scene->video_path)
                                        <video src="{{ asset('storage/'.$scene->video_path) }}" style="width: 100%; pointer-events:none;"></video>
                                    @else
                                        <i class="bi bi-camera-reels" style="color:#555; font-size: 20px;"></i>
                                    @endif
                                    <span style="position:absolute; top:2px; left:2px; font-size:7px; background:rgba(0,0,0,.8); color:{{ $scene->video_path ? '#2ecc71' : '#f1c40f' }}; padding:1px 3px; border-radius:2px;">
                                        {{ $scene->video_path ? 'ONLINE' : $sceneStatus($scene) }}
                                    </span>
                                </div>
                                <div class="font-mono text-white text-truncate mt-1 px-1" style="font-size: 9px;">
                                    SEQ #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-12 text-center text-secondary py-4 small">Bin Empty.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Center: Cinema Monitor -->
        <div class="panel-monitor position-relative">
            <!-- Monitor Top Bar -->
            <div style="height: 30px; background: #111; display:flex; justify-content: space-between; align-items: center; padding: 0 12px; font-size: 10px; color: #888;">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-dark border border-secondary text-info font-mono" style="font-size: 9px;">DIRECTOR MON</span>
                    <span class="text-white text-truncate" style="max-width: 260px;">{{ $currentScene?->video_path ? $sceneFileName($currentScene) : 'Awaiting Render' }}</span>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <!-- Scope Mask Selector -->
                    <select id="scopeMaskSelector" class="nle-select py-0" style="font-size: 9px; height: 22px;" onchange="setScopeMask(this.value)">
                        <option value="16x9">16:9 Cinema</option>
                        <option value="anamorphic">2.39:1 Scope</option>
                        <option value="vertical">9:16 Vertical</option>
                        <option value="classic43">4:3 Academy</option>
                    </select>

                    <button type="button" class="btn btn-sm btn-dark border-secondary text-info p-0 px-1" style="font-size: 9px;" onclick="takeSnapshot()" title="Capture 4K Still Frame">
                        <i class="bi bi-camera"></i>
                    </button>
                    <i class="bi bi-fullscreen cursor-pointer hover-white" onclick="toggleFullscreen()" title="Fullscreen (F)"></i>
                </div>
            </div>

            <!-- Video Screen Viewport with Scope Masks & Titles -->
            <div class="video-canvas-container" id="monitorViewport">
                <!-- Scope Letterbox Overlays -->
                <div class="scope-mask-top" id="maskTop"></div>
                <div class="scope-mask-bottom" id="maskBottom"></div>
                <div class="scope-mask-left" id="maskLeft"></div>
                <!-- Dynamic FX / Transition Overlay Layer -->
                <div id="transFxOverlay" class="trans-fx-overlay"></div>
                <!-- Cinematic VFX Overlays -->
                <div id="vfxGrainLayer" class="vfx-layer"></div>
                <div id="vfxCrtLayer" class="vfx-layer"></div>
                <div id="vfxHalationLayer" class="vfx-layer"></div>
                <div id="vfxFlareLayer" class="vfx-layer"></div>
                <div id="vfxTiltShiftLayer" class="vfx-layer"></div>

                @if($currentScene && $currentScene->video_path)
                    <video id="mainPlayer" src="{{ asset('storage/'.$currentScene->video_path) }}" class="w-100 h-100 object-fit-contain" playsinline preload="auto"></video>
                    
                    <!-- Title/Subtitle Overlay Layer -->
                    <div id="canvasTitleOverlay" class="position-absolute text-center" style="pointer-events: none; z-index: 20; width: 90%; left: 50%; transform: translateX(-50%); bottom: 15%;">
                        <h3 id="overlayHeadline" class="fw-bold tracking-widest text-white text-uppercase mb-0" style="text-shadow: 0 2px 14px rgba(0,0,0,0.95); font-family: 'Syncopate', sans-serif;">SCENE 01</h3>
                        <p id="overlaySubtitle" class="small tracking-widest text-secondary font-mono uppercase mb-0" style="font-size: 11px; text-shadow: 0 2px 10px rgba(0,0,0,0.95);">A SHADOW PRODUCTION</p>
                    </div>
                @elseif($currentScene)
                    <div class="text-center text-warning p-4" style="font-family: monospace; z-index: 2;">
                        <i class="bi bi-exclamation-triangle fs-1 d-block mb-2 text-warning"></i>
                        <h6 class="text-white tracking-widest">MEDIA OFFLINE</h6>
                        <p class="text-secondary small mb-3">Clip is awaiting AI synthesis or video attachment.</p>
                        
                        <form action="{{ route('scenes.attach-video', $currentScene) }}" method="POST" enctype="multipart/form-data" class="mx-auto" style="max-width: 320px;">
                            @csrf
                            <input type="file" name="video_file" class="nle-input w-100 mb-2" accept="video/mp4,video/quicktime,video/webm,video/x-m4v" required>
                            <button type="submit" class="nle-btn nle-btn-primary w-100">
                                <i class="bi bi-upload me-2"></i>Attach Video File
                            </button>
                        </form>
                    </div>
                @else
                    <div class="text-center text-secondary opacity-25">
                        <i class="bi bi-film" style="font-size: 60px;"></i>
                    </div>
                @endif
            </div>

            <!-- Monitor Transport Bar -->
            <div style="height: 44px; background: #111; border-top: 1px solid #222; display: flex; align-items: center; justify-content: center; gap: 18px; font-size: 16px; color: #aaa; position: relative;">
                <span id="timecode" class="font-mono" style="font-size: 13px; position: absolute; left: 14px; color: var(--nle-active);">01:00:00:00</span>
                
                <i class="bi bi-skip-start-fill" style="cursor: pointer;" onclick="seekToStart()" title="Home"></i>
                <i class="bi bi-caret-left-fill" style="cursor: pointer; font-size: 14px;" onclick="stepFrame(-1)" title="Step -1 Frame"></i>
                
                <i id="playPauseBtn" class="bi bi-play-fill text-white fs-2" style="cursor: pointer; transition: color 0.1s;" onclick="togglePlay()" title="Play/Pause (Space)"></i>
                
                <i class="bi bi-caret-right-fill" style="cursor: pointer; font-size: 14px;" onclick="stepFrame(1)" title="Step +1 Frame"></i>
                <i class="bi bi-skip-end-fill" style="cursor: pointer;" onclick="seekToEnd()" title="End"></i>
                
                <div style="position: absolute; right: 14px; display:flex; gap: 12px; font-size: 12px; color: #777;">
                    <i class="bi bi-arrow-repeat hover-white cursor-pointer" onclick="toggleLoop()" id="btnLoopIcon" title="Toggle Loop"></i>
                    <i class="bi bi-volume-up hover-white cursor-pointer" onclick="toggleMute()" title="Mute (M)"></i>
                </div>
            </div>
        </div>

        <!-- Right: Fairlight Master Meter & Render Queue -->
        <div class="nle-panel panel-audio-meter">
            <div class="nle-panel-header">
                <span>MASTER AUDIO</span>
                <i class="bi bi-soundwave text-secondary" style="font-size:11px;"></i>
            </div>
            <div class="nle-panel-content d-flex flex-column" style="padding: 0;">
                
                <div style="padding: 10px; border-bottom: 1px solid var(--nle-border); display: flex; flex-direction: column; background: #111;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary fw-bold" style="font-size: 8px; letter-spacing: 1px;">M1 MASTER OUT</span>
                        <span id="mixerState" class="font-mono text-success" style="font-size: 9px;">ONLINE</span>
                    </div>
                    
                    <div class="mixer-strip">
                        <div class="meter-scale">
                            <span>0</span><span>-6</span><span>-12</span><span>-18</span><span>-24</span><span>-36</span>
                        </div>
                        
                        <div class="meter-shell">
                            <div id="meterLeft" class="meter-fill"></div>
                            <div id="peakLeft" class="meter-peak"></div>
                        </div>
                        
                        <div class="meter-shell">
                            <div id="meterRight" class="meter-fill"></div>
                            <div id="peakRight" class="meter-peak"></div>
                        </div>
                    </div>

                    <div class="mixer-control-row">
                        <button type="button" id="muteBtn" class="mixer-mini-btn" onclick="toggleMute()">M</button>
                        <button type="button" id="soloBtn" class="mixer-mini-btn" onclick="toggleSolo()">S</button>
                        <button type="button" id="dimBtn" class="mixer-mini-btn" onclick="toggleDim()">DIM</button>
                    </div>

                    <label class="text-secondary mt-2 mb-1 uppercase" style="font-size: 8px;">Master Output</label>
                    <input id="masterVolume" type="range" class="form-range" min="0" max="100" value="85" oninput="setMasterVolume(this.value)">
                    <div class="mixer-readout font-mono" id="volumeReadout">-1.8 dB / 85%</div>
                </div>

                <div style="flex: 1; padding: 8px; background: var(--nle-panel); overflow-y: auto;">
                    <div class="mb-2 text-secondary pb-1 uppercase d-flex justify-content-between align-items-center" style="font-size:8px; border-bottom:1px solid #333;">
                        <span>Render Pipeline</span>
                        <span class="text-info">{{ $project->scenes->where('status', 'Processing')->count() }} Active</span>
                    </div>

                    @forelse($scenes->take(4) as $job)
                        <div class="p-2 mb-2 bg-black border border-secondary" style="font-size: 9px;">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-white font-mono">SEQ {{ str_pad($job->order_index, 3, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-{{ in_array($job->status, ['ready', 'Ready']) ? 'success' : 'warning' }} font-mono">
                                    {{ strtoupper($job->status ?? 'Draft') }}
                                </span>
                            </div>
                            <div class="d-flex gap-1 mt-1">
                                <form action="{{ route('scenes.render', $job) }}" method="POST" class="flex-grow-1 m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-dark border-secondary text-info w-100 p-0" style="font-size:8px;">Render</button>
                                </form>
                                <a href="{{ route('projects.videoeditor', $project) }}?active_scene={{ $job->id }}" class="btn btn-sm btn-dark border-secondary text-white p-0 px-2" style="font-size:8px;">Open</a>
                            </div>
                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- ================================================================= -->
    <!-- 2. COLOR GRADING WORKSPACE                                        -->
    <!-- ================================================================= -->
    <div id="workspace-color" class="nle-workspace-view">
        <div class="row g-0 w-100 h-100">
            <!-- Left: Color Controls Panel -->
            <div class="col-4 bg-black border-end border-secondary p-3 custom-scrollbar overflow-auto">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary">
                    <h6 class="text-white font-mono small tracking-widest uppercase mb-0">
                        <i class="bi bi-palette text-info me-1"></i> COLOR WHEELS & 3D LUTS
                    </h6>
                    <span class="badge bg-dark border border-info text-info font-mono" style="font-size: 8px;">16 Film Shades</span>
                </div>

                <!-- 3D LUT Presets Category Selector -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="text-secondary small uppercase" style="font-size: 9px;">Cinema 3D LUT Film Shades</label>
                        <span class="font-mono text-info" id="activeLutLabel" style="font-size: 8.5px;">Rec.709 Natural</span>
                    </div>

                    <div class="d-flex flex-column gap-1 mb-3 custom-scrollbar" style="max-height: 200px; overflow-y: auto; padding-right: 2px;">
                        <!-- Film Stocks -->
                        <div class="lut-card active" onclick="applyLut('none', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">Standard Rec.709</span>
                                <span class="badge bg-dark border border-secondary text-secondary" style="font-size: 7.5px;">NEUTRAL</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #333, #777, #bbb, #fff);"></div>
                        </div>

                        <div class="lut-card" onclick="applyLut('kodak2383', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">Kodak Vision3 2383 Print</span>
                                <span class="badge bg-dark border border-secondary text-warning" style="font-size: 7.5px;">35MM FILM</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #1f1208, #824718, #e0a353, #fceed8);"></div>
                        </div>

                        <div class="lut-card" onclick="applyLut('fuji_eterna', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">Fujifilm Eterna 250D Soft Mint</span>
                                <span class="badge bg-dark border border-secondary text-success" style="font-size: 7.5px;">FUJI COLOR</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #0d1a16, #2d5a4c, #78a896, #dcf2e9);"></div>
                        </div>

                        <div class="lut-card" onclick="applyLut('teal_orange', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">Teal & Orange Blockbuster</span>
                                <span class="badge bg-dark border border-secondary text-info" style="font-size: 7.5px;">HOLLYWOOD</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #051e24, #0dcaf0, #e67e22, #fad7a0);"></div>
                        </div>

                        <div class="lut-card" onclick="applyLut('cyberpunk', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">Blade Runner Cyberpunk Neon</span>
                                <span class="badge bg-dark border border-secondary text-danger" style="font-size: 7.5px;">CYBERPUNK</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #0b032d, #8400ff, #00f0ff, #ff007f);"></div>
                        </div>

                        <div class="lut-card" onclick="applyLut('matrix', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">The Matrix Digital Green Wash</span>
                                <span class="badge bg-dark border border-secondary text-success" style="font-size: 7.5px;">SCI-FI</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #001a05, #006611, #2ecc71, #d4f8d3);"></div>
                        </div>

                        <div class="lut-card" onclick="applyLut('bleach_bypass', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">Oppenheimer Bleach Bypass</span>
                                <span class="badge bg-dark border border-secondary text-secondary" style="font-size: 7.5px;">SILVER</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #111, #444, #888, #ddd);"></div>
                        </div>

                        <div class="lut-card" onclick="applyLut('dune_gold', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">Dune Arrakis Golden Hour</span>
                                <span class="badge bg-dark border border-secondary text-warning" style="font-size: 7.5px;">DESERT GOLD</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #2b1803, #995c0d, #f39c12, #fdebd0);"></div>
                        </div>

                        <div class="lut-card" onclick="applyLut('fincher', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">Fincher Low-Key Thriller Green</span>
                                <span class="badge bg-dark border border-secondary text-info" style="font-size: 7.5px;">DARK MOOD</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #08110e, #1a3328, #476b5a, #a2bfb2);"></div>
                        </div>

                        <div class="lut-card" onclick="applyLut('noir', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">Master Shadow Noir B&W</span>
                                <span class="badge bg-dark border border-secondary text-light" style="font-size: 7.5px;">MONO</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #000, #333, #888, #fff);"></div>
                        </div>

                        <div class="lut-card" onclick="applyLut('kodachrome', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">Kodachrome 64 Vintage 1970s</span>
                                <span class="badge bg-dark border border-secondary text-danger" style="font-size: 7.5px;">RETRO</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #1f0b08, #942b1e, #e74c3c, #f9d5d1);"></div>
                        </div>

                        <div class="lut-card" onclick="applyLut('moonlight', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">Cold Moonlight Cobalt Blue</span>
                                <span class="badge bg-dark border border-secondary text-primary" style="font-size: 7.5px;">NIGHT</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #030a1c, #0d2859, #2980b9, #d4e6f1);"></div>
                        </div>

                        <div class="lut-card" onclick="applyLut('technicolor', this)">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-white font-mono" style="font-size: 9.5px;">Technicolor 3-Strip Classic</span>
                                <span class="badge bg-dark border border-secondary text-warning" style="font-size: 7.5px;">VIBRANT</span>
                            </div>
                            <div class="lut-preview-swatch" style="background: linear-gradient(90deg, #e74c3c, #2ecc71, #3498db, #f1c40f);"></div>
                        </div>
                    </div>
                </div>

                <!-- Primary Color Sliders -->
                <div class="p-3 bg-dark border border-secondary mb-3 rounded-1">
                    <span class="text-info font-mono small fw-bold d-block mb-2" style="font-size: 10px;">
                        <i class="bi bi-sliders me-1"></i> PRIMARY COLOR ADJUSTMENTS
                    </span>
                    
                    <!-- Exposure -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between text-secondary" style="font-size: 9px;">
                            <span>EXPOSURE (EV GAIN)</span>
                            <span id="cgExpVal" class="text-white font-mono">1.00</span>
                        </div>
                        <input type="range" class="form-range" id="cgExposure" min="50" max="180" value="100" oninput="updateColorGrade()">
                    </div>

                    <!-- Contrast -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between text-secondary" style="font-size: 9px;">
                            <span>CONTRAST & CURVE</span>
                            <span id="cgConVal" class="text-white font-mono">1.00</span>
                        </div>
                        <input type="range" class="form-range" id="cgContrast" min="60" max="180" value="100" oninput="updateColorGrade()">
                    </div>

                    <!-- Saturation -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between text-secondary" style="font-size: 9px;">
                            <span>SATURATION & VIBRANCE</span>
                            <span id="cgSatVal" class="text-white font-mono">1.00</span>
                        </div>
                        <input type="range" class="form-range" id="cgSaturation" min="0" max="220" value="100" oninput="updateColorGrade()">
                    </div>

                    <!-- Color Temperature -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between text-secondary" style="font-size: 9px;">
                            <span>COLOR TEMPERATURE (KELVIN)</span>
                            <span id="cgTempVal" class="text-white font-mono">5600K</span>
                        </div>
                        <input type="range" class="form-range" id="cgTemperature" min="-50" max="50" value="0" oninput="updateColorGrade()">
                    </div>

                    <!-- Hue Rotation -->
                    <div class="mb-1">
                        <div class="d-flex justify-content-between text-secondary" style="font-size: 9px;">
                            <span>HUE ROTATION (DEGREES)</span>
                            <span id="cgHueVal" class="text-white font-mono">0°</span>
                        </div>
                        <input type="range" class="form-range" id="cgHueRotation" min="-180" max="180" value="0" oninput="updateColorGrade()">
                    </div>
                </div>

                <!-- Action Controls -->
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill font-mono" style="font-size: 10px;" onclick="resetColorGrade()">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> RESET GRADE
                        </button>
                        <button type="button" class="btn btn-info btn-sm text-black fw-bold flex-fill font-mono" style="font-size: 10px;" onclick="applyGradeToAllScenes()">
                            <i class="bi bi-check2-all me-1"></i> BATCH ALL CLIPS
                        </button>
                    </div>

                    <!-- Toast Feedback -->
                    <div id="cgToast" class="alert alert-info py-1 px-2 mb-0 font-mono text-center d-none" style="font-size: 9px;">
                        <i class="bi bi-check-circle me-1"></i> <span id="cgToastMsg">Color grade saved</span>
                    </div>
                </div>
            </div>

            <!-- Right: Big Color Grade Cinema Screen with Real-Time Histogram/Waveform Scope -->
            <div class="col-8 bg-black d-flex flex-column">
                <div class="p-2 border-bottom border-secondary d-flex justify-content-between align-items-center font-mono" style="font-size: 10px;">
                    <span class="text-secondary"><i class="bi bi-display me-1"></i> COLOR GRADING SCOPE // REC.709 DCI-P3</span>
                    <span class="text-info">ACTIVE CLIP: SEQ #{{ str_pad($currentScene?->order_index ?? 1, 3, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="flex-grow-1 position-relative d-flex align-items-center justify-content-center bg-black overflow-hidden" id="colorViewport">
                    @if($currentScene && $currentScene->video_path)
                        <video id="colorPlayer" src="{{ asset('storage/'.$currentScene->video_path) }}" class="w-100 h-100 object-fit-contain" loop muted autoplay playsinline></video>
                    @else
                        <div class="text-secondary small font-mono">No video loaded for color grading.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- 3. FAIRLIGHT AUDIO & SOUND FX WORKSPACE                           -->
    <!-- ================================================================= -->
    <!-- ================================================================= -->
    <!-- 3. FAIRLIGHT AUDIO & SOUND FX WORKSPACE                           -->
    <!-- ================================================================= -->
    <div id="workspace-fairlight" class="nle-workspace-view">
        <div class="p-4 bg-black w-100 h-100 custom-scrollbar overflow-auto">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
                <h5 class="text-white font-mono mb-0"><i class="bi bi-soundwave text-success me-2"></i>FAIRLIGHT PRO AUDIO & FOLEY FX</h5>
                <span class="badge bg-dark border border-secondary text-secondary font-mono">48kHz / 24-BIT MASTER</span>
            </div>

            <div class="row g-4">
                <!-- Foley Sound FX Trigger Board -->
                <div class="col-md-6">
                    <div class="bg-dark p-3 border border-secondary mb-3">
                        <h6 class="text-white font-mono small uppercase mb-3 border-bottom border-secondary pb-2">
                            <i class="bi bi-boombox text-warning me-1"></i> Interactive Foley & Sound FX Board
                        </h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="sfx-card text-center" onclick="playSfx('bass_boom')">
                                    <i class="bi bi-soundwave text-danger d-block fs-4 mb-1"></i>
                                    <span class="font-mono text-white small d-block">Sub Bass Drop</span>
                                    <small class="text-secondary" style="font-size: 8px;">Cinematic Braam</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="sfx-card text-center" onclick="playSfx('laser_whoosh')">
                                    <i class="bi bi-lightning-charge text-info d-block fs-4 mb-1"></i>
                                    <span class="font-mono text-white small d-block">Cyber Whoosh</span>
                                    <small class="text-secondary" style="font-size: 8px;">Fast Transition</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="sfx-card text-center" onclick="playSfx('vhs_glitch')">
                                    <i class="bi bi-cassette text-warning d-block fs-4 mb-1"></i>
                                    <span class="font-mono text-white small d-block">Tape Glitch</span>
                                    <small class="text-secondary" style="font-size: 8px;">Analog Static</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="sfx-card text-center" onclick="playSfx('tension_riser')">
                                    <i class="bi bi-stopwatch text-danger d-block fs-4 mb-1"></i>
                                    <span class="font-mono text-white small d-block">Tension Riser</span>
                                    <small class="text-secondary" style="font-size: 8px;">Action Pulse</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="sfx-card text-center" onclick="playSfx('laser_hit')">
                                    <i class="bi bi-fire text-info d-block fs-4 mb-1"></i>
                                    <span class="font-mono text-white small d-block">Laser Hit</span>
                                    <small class="text-secondary" style="font-size: 8px;">Sci-Fi Impact</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="sfx-card text-center" onclick="playSfx('footstep')">
                                    <i class="bi bi-bullseye text-success d-block fs-4 mb-1"></i>
                                    <span class="font-mono text-white small d-block">Heavy Impact</span>
                                    <small class="text-secondary" style="font-size: 8px;">Foley Kick</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Generative Ambient Score Synthesizer -->
                    <div class="bg-dark p-3 border border-secondary mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-secondary">
                            <h6 class="text-white font-mono small uppercase mb-0">
                                <i class="bi bi-soundwave text-info me-1"></i> Generative Cinematic Ambience
                            </h6>
                            <button type="button" class="btn btn-sm btn-info text-black font-mono py-0 px-2 fw-bold" id="btnNleAmbience" onclick="toggleNleAmbience()" style="font-size: 9px;">
                                <i class="bi bi-play-fill me-1" id="nleAmbienceIcon"></i> <span id="nleAmbienceText">PLAY AMBIENCE</span>
                            </button>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <select id="nleAmbiencePreset" class="form-select form-select-sm bg-black border-secondary text-white font-mono" style="font-size: 10px;">
                                <option value="space">Deep Space Obsidian Drone</option>
                                <option value="noir">Blade Runner Analog Synth</option>
                                <option value="tension">High Tension Cinematic Pulse</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 6-Band Parametric Graphic EQ -->
                <div class="col-md-6">
                    <div class="bg-dark p-3 border border-secondary mb-3">
                        <h6 class="text-white font-mono small uppercase mb-3 border-bottom border-secondary pb-2">6-Band Parametric EQ</h6>
                        <div class="d-flex justify-content-between align-items-end" style="height: 120px; padding: 0 10px;">
                            @foreach(['60Hz', '150Hz', '400Hz', '1kHz', '4kHz', '12kHz'] as $band)
                                <div class="d-flex flex-column align-items-center gap-2" style="height: 100%;">
                                    <input type="range" class="form-range" min="-12" max="12" value="0" style="writing-mode: vertical-lr; direction: rtl; height: 90px;">
                                    <span class="text-secondary font-mono" style="font-size: 8px;">{{ $band }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- 4. DELIVER / EXPORT WORKSPACE                                     -->
    <!-- ================================================================= -->
    <div id="workspace-deliver" class="nle-workspace-view">
        <div class="p-4 bg-black w-100 h-100 custom-scrollbar overflow-auto">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom border-secondary">
                <h5 class="text-white font-mono mb-0"><i class="bi bi-box-arrow-up-right text-info me-2"></i>CINEMA EXPORT & DELIVERY</h5>
                <span class="badge bg-dark border border-secondary text-secondary font-mono">FINAL DCI ENCODER</span>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="bg-dark p-3 border border-secondary mb-3">
                        <span class="text-info font-mono small fw-bold d-block mb-3">1-CLICK EXPORT PRESETS</span>
                        <div class="d-flex flex-column gap-2">
                            <button type="button" class="btn btn-sm btn-outline-light text-start p-2 font-mono" style="font-size: 11px;">
                                <i class="bi bi-youtube text-danger me-2"></i> YouTube / Vimeo 4K (H.265 Master)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-light text-start p-2 font-mono" style="font-size: 11px;">
                                <i class="bi bi-phone text-info me-2"></i> TikTok / Reels 9:16 Vertical Crop
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-light text-start p-2 font-mono" style="font-size: 11px;">
                                <i class="bi bi-film text-warning me-2"></i> Apple ProRes 422 Cinema Master
                            </button>
                        </div>
                    </div>

                    <!-- Sidecar Assets Export Package -->
                    <div class="bg-dark p-3 border border-secondary">
                        <span class="text-white font-mono small fw-bold d-block mb-2 border-bottom border-secondary pb-1">SIDECAR ASSETS</span>
                        <div class="d-flex flex-column gap-2">
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-info text-start font-mono" style="font-size: 10px;" onclick="exportSRT()">
                                <i class="bi bi-badge-cc me-2"></i> Download .SRT Subtitles
                            </button>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-info text-start font-mono" style="font-size: 10px;" onclick="exportNleEDL()">
                                <i class="bi bi-file-earmark-code me-2"></i> Download Sequence EDL
                            </button>
                            <button type="button" class="btn btn-sm btn-dark border-secondary text-info text-start font-mono" style="font-size: 10px;" onclick="exportShotlistCSV()">
                                <i class="bi bi-file-earmark-spreadsheet me-2"></i> Download Shot List CSV
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="bg-dark p-3 border border-secondary">
                        <h6 class="text-white font-mono small uppercase mb-3 border-bottom border-secondary pb-2">Encoding Specifications</h6>
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="input-label mb-1">Format Container</label>
                                <select class="nle-select w-100">
                                    <option>QuickTime Movie (.mov)</option>
                                    <option selected>MPEG-4 (.mp4)</option>
                                    <option>Final Cut Pro XML (.xml)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="input-label mb-1">Video Codec</label>
                                <select class="nle-select w-100">
                                    <option selected>H.265 (HEVC High Profile)</option>
                                    <option>H.264 (AVC)</option>
                                    <option>Apple ProRes 422 HQ</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="input-label mb-1">Resolution</label>
                                <select class="nle-select w-100">
                                    <option selected>3840 x 2160 (4K UHD)</option>
                                    <option>1920 x 1080 (1080p Full HD)</option>
                                    <option>4096 x 2160 (DCI 4K Scope)</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="input-label mb-1">Frame Rate</label>
                                <select class="nle-select w-100">
                                    <option selected>24.00 fps (Cinematic Film)</option>
                                    <option>29.97 fps (NTSC Broadcast)</option>
                                    <option>60.00 fps (High Frame Rate)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top border-secondary d-flex gap-3 flex-wrap">
                            <a href="{{ route('projects.export-xml', $project) }}" class="btn btn-outline-info rounded-0 px-4 font-mono small">
                                <i class="bi bi-file-earmark-code me-1"></i> EXPORT FCP XML
                            </a>
                            <button type="button" class="btn btn-white bg-white text-black fw-bold rounded-0 px-4 font-mono small" onclick="alert('Export sequence queued. Master file is rendering.')">
                                <i class="bi bi-cpu me-1"></i> RENDER ALL & EXPORT MASTER
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ================================================================= -->
    <!-- TIMELINE MULTI-TRACK CONTAINER                                    -->
    <!-- ================================================================= -->
    <div class="nle-timeline-area">
        
        <!-- Timeline Toolbar -->
        <div class="timeline-toolbar">
            <div class="tool-icon active" data-tool="select" onclick="activateTool(this)" title="Selection Mode (A)"><i class="bi bi-cursor-fill"></i></div>
            <div class="tool-icon" data-tool="blade" onclick="activateTool(this)" title="Blade / Razor (B)"><i class="bi bi-scissors"></i></div>
            <div class="tool-icon" data-tool="slip" onclick="activateTool(this)" title="Slip Edit (S)"><i class="bi bi-arrows-expand"></i></div>
            <div class="tool-divider"></div>
            <div class="tool-icon active" onclick="this.classList.toggle('active')" title="Snapping (N)"><i class="bi bi-magnet-fill"></i></div>
            <div class="tool-icon active" onclick="this.classList.toggle('active')" title="Linked Selection"><i class="bi bi-link"></i></div>
            <div class="tool-icon" onclick="addTimelineMarker()" title="Add Marker (M)"><i class="bi bi-bookmark-fill"></i></div>
            
            <div class="ms-auto d-flex align-items-center gap-2">
                <i class="bi bi-zoom-out text-secondary" style="font-size: 13px;"></i>
                <input id="timelineZoom" type="range" class="form-range" style="width: 140px; height: 2px;" min="120" max="320" value="{{ $clipWidth }}" oninput="setTimelineZoom(this.value)">
                <i class="bi bi-zoom-in text-white" style="font-size: 13px;"></i>
            </div>
        </div>

        <!-- Timeline Ruler -->
        <div class="timeline-ruler" id="timelineRuler" onclick="handleRulerClick(event)">
            @for($second = 0; $second <= $timelineSeconds; $second += $rulerStep)
                <div class="timeline-ruler-tick">{{ $formatTimecode($second) }}</div>
            @endfor
        </div>

        <!-- Timeline Tracks Scroll Area -->
        <div class="timeline-tracks-container custom-scrollbar" id="timelineScrollContainer">
            
            <!-- Headers (Sticky Left) -->
            <div class="timeline-headers">
                <div class="track-header">
                    <span class="fw-bold font-mono">V2 (Overlay)</span>
                    <div class="track-controls">
                        <div class="btn-track" onclick="this.classList.toggle('m-active')" title="Mute Track">M</div>
                        <i class="bi bi-lock ms-auto mt-1" style="font-size:10px; color:#555;"></i>
                    </div>
                </div>
                <div class="track-header" style="background: #171717;"> 
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold text-white font-mono">V1 <span class="text-secondary" style="font-weight:normal;">(Master)</span></span>
                        <i class="bi bi-eye text-white"></i>
                    </div>
                    <div class="track-controls">
                        <div class="btn-track" onclick="this.classList.toggle('m-active')">M</div>
                    </div>
                </div>
                
                <div class="track-header" style="background: #171717; height: 60px;">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold font-mono" style="color: var(--nle-audio-green);">A1 <span class="text-secondary" style="font-weight:normal;">(Score)</span></span>
                    </div>
                    <div class="track-controls">
                        <div class="btn-track" onclick="this.classList.toggle('m-active')">M</div>
                        <div class="btn-track" onclick="this.classList.toggle('s-active')" title="Solo Track">S</div>
                    </div>
                </div>
            </div>

            <!-- Grid & Clips -->
            <div class="timeline-grid" id="timelineGrid">
                
                <!-- Draggable Red Playhead Needle -->
                <div class="timeline-playhead" id="timelinePlayhead" style="left: {{ $playheadLeft }}px;">
                    <div class="timeline-playhead-head"></div>
                </div>

                <div class="track-row"></div> <!-- V2 Track -->
                
                <!-- V1 Master Track -->
                <div class="track-row">
                    @foreach($scenes as $scene)
                        <a href="{{ route('projects.videoeditor', $project) }}?active_scene={{ $scene->id }}" 
                           class="clip-block clip-video {{ $currentSceneId == $scene->id ? 'clip-active' : '' }}" 
                           title="{{ $sceneFileName($scene) }} · {{ $scene->script_segment }}"
                           data-timeline-clip
                           style="width: {{ $clipWidth }}px; background: {{ $scene->video_path ? 'var(--nle-clip-bg)' : '#333' }}; border-color: {{ $scene->video_path ? '#10386b' : '#555' }};">
                           <span style="position:relative; z-index: 2; font-family: 'JetBrains Mono', monospace; font-weight:600; font-size: 10px;">
                                SEQ_{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}
                           </span>
                           <small class="d-block" style="position:relative; z-index:2; opacity:.85; font-size: 9px;">
                                {{ $scene->video_path ? $sceneFileName($scene) : $sceneStatus($scene) }}
                           </small>
                           <small class="d-block text-truncate" style="position:relative; z-index:2; opacity:.65; font-size: 8px;">
                                {{ Str::limit($scene->script_segment ?? 'No script segment', 32) }}
                           </small>
                           <div class="clip-line"></div>
                        </a>
                    @endforeach
                </div>

                <!-- A1 Audio Track -->
                <div class="track-row" style="height: 60px; background: rgba(46, 204, 113, 0.03);">
                    @foreach($scenes as $scene)
                        @if($scene->video_path)
                            <div class="clip-block clip-audio" data-timeline-clip style="width: {{ $clipWidth }}px; height: 50px; position: relative;" title="Embedded audio">
                               <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 65%; border-top: 1px solid rgba(255,255,255,0.1); background-image: repeating-linear-gradient(90deg, transparent, transparent 3px, rgba(46, 204, 113, 0.3) 3px, rgba(46, 204, 113, 0.3) 4px);"></div>
                               <span style="position:relative; z-index: 2; color: #b0d4b8; font-size: 9px; font-family: monospace;">A_{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}</span>
                               <small class="d-block text-truncate" style="position:relative; z-index:2; color:#8fb99b; font-size: 8px;">{{ $sceneFileName($scene) }}</small>
                               <div class="clip-line" style="top: 30%;"></div>
                            </div>
                        @else
                            <div class="clip-block" data-timeline-clip style="width: {{ $clipWidth }}px; height: 50px; position: relative; background:#181818; border:1px dashed #333; color:#666; cursor:default;">
                               <span style="position:relative; z-index: 2; font-size: 9px; font-family: monospace;">A_{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }} · No media</span>
                            </div>
                        @endif
                    @endforeach
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Shortcuts Modal -->
<div class="modal fade" id="shortcutsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-black border border-secondary text-white rounded-0">
            <div class="modal-header border-secondary">
                <h6 class="modal-title font-mono small tracking-widest">NLE STUDIO SHORTCUTS</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body font-mono small">
                <table class="table table-dark table-borderless small mb-0">
                    <tbody>
                        <tr><td><kbd>Space</kbd></td><td>Play / Pause Video</td></tr>
                        <tr><td><kbd>J</kbd> / <kbd>K</kbd> / <kbd>L</kbd></td><td>Shuttle (Slow / Normal / Fast)</td></tr>
                        <tr><td><kbd>←</kbd> / <kbd>→</kbd></td><td>Step Frame (-1 / +1 Frame)</td></tr>
                        <tr><td><kbd>Home</kbd> / <kbd>End</kbd></td><td>Jump to Start / End</td></tr>
                        <tr><td><kbd>F</kbd></td><td>Toggle Fullscreen Monitor</td></tr>
                        <tr><td><kbd>M</kbd></td><td>Toggle Audio Mute</td></tr>
                        <tr><td><kbd>1</kbd> - <kbd>4</kbd></td><td>Switch Workspace (Edit, Color, Fairlight, Deliver)</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Keep menus open while the pointer travels from the heading to an action.
    const editorMenuGroups = [...document.querySelectorAll('.nle-menu-group')];
    function closeEditorMenus() {
        editorMenuGroups.forEach(group => {
            group.classList.remove('is-open');
            group.querySelector('.nle-menu-item').setAttribute('aria-expanded', 'false');
        });
    }
    editorMenuGroups.forEach((group, index) => {
        const trigger = group.querySelector('.nle-menu-item');
        const dropdown = group.querySelector('.nle-menu-dropdown');
        dropdown.id = `editor-menu-${index}`;
        trigger.setAttribute('aria-controls', dropdown.id);
        trigger.setAttribute('aria-expanded', 'false');
        const openMenu = () => {
            closeEditorMenus();
            group.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        };
        trigger.addEventListener('pointerenter', event => {
            if (event.pointerType !== 'touch') openMenu();
        });
        trigger.addEventListener('click', openMenu);
        dropdown.addEventListener('click', event => {
            if (event.target.closest('.nle-menu-link:not([disabled])')) closeEditorMenus();
        });
    });
    document.addEventListener('click', event => {
        if (!event.target.closest('.nle-menu-group')) closeEditorMenus();
    });
    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape') return;
        const openGroup = document.querySelector('.nle-menu-group.is-open');
        if (openGroup) {
            closeEditorMenus();
            openGroup.querySelector('.nle-menu-item').focus();
        }
    });

    // State
    let isPlaying = false;
    let isLooping = false;
    let currentSpeed = 1.0;
    let mixerMuted = false;
    let mixerSolo = false;
    let mixerDim = false;
    let masterVolume = 0.85;
    let meterTimer = null;
    let audioCtx = null;
    let nleAmbienceGain = null;
    let isNleAmbiencePlaying = false;
    let nleAmbienceOscillators = [];

    // 1. Workspace Switcher
    function switchWorkspace(name) {
        document.querySelectorAll('.workspace-tab').forEach(t => {
            t.classList.toggle('active', t.dataset.workspace === name);
        });
        document.querySelectorAll('.nle-workspace-view').forEach(v => {
            v.classList.toggle('active', v.id === 'workspace-' + name);
        });
    }

    // 2. Inspector Tab Switcher (5 tabs: video, vfx, titles, transitions, ai)
    function switchInspTab(tabName) {
        document.querySelectorAll('.insp-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.insp-content').forEach(c => c.classList.remove('active'));
        
        const tabMap = { video: 0, vfx: 1, titles: 2, transitions: 3, ai: 4 };
        const idx = tabMap[tabName] ?? 0;
        const tabs = document.querySelectorAll('.insp-tab');
        if (tabs[idx]) tabs[idx].classList.add('active');

        const content = document.getElementById('insp-' + tabName);
        if (content) content.classList.add('active');
    }

    // 3. Playback Controls
    function togglePlay() {
        const video = document.getElementById('mainPlayer');
        if (!video) return;
        if (video.paused) {
            video.play().then(() => setPlayIcon(true)).catch(() => {});
        } else {
            video.pause();
            setPlayIcon(false);
        }
    }

    function setPlayIcon(playing) {
        isPlaying = playing;
        const btn = document.getElementById('playPauseBtn');
        if (!btn) return;
        btn.classList.toggle('bi-play-fill', !playing);
        btn.classList.toggle('bi-pause-fill', playing);
        btn.style.color = playing ? '#0dcaf0' : '#fff';
    }

    function seekToStart() {
        const video = document.getElementById('mainPlayer');
        if (video) { video.currentTime = 0; updateTimecode(); }
    }

    function seekToEnd() {
        const video = document.getElementById('mainPlayer');
        if (video && !isNaN(video.duration)) { video.currentTime = Math.max(0, video.duration - 0.05); updateTimecode(); }
    }

    function stepFrame(dir) {
        const video = document.getElementById('mainPlayer');
        if (!video) return;
        video.pause();
        setPlayIcon(false);
        video.currentTime = Math.max(0, video.currentTime + (dir / 24));
        updateTimecode();
    }

    function toggleLoop() {
        isLooping = !isLooping;
        const video = document.getElementById('mainPlayer');
        if (video) video.loop = isLooping;
        document.getElementById('btnLoopIcon')?.classList.toggle('text-info', isLooping);
    }

    function setSpeed(speed) {
        currentSpeed = speed;
        const video = document.getElementById('mainPlayer');
        if (video) video.playbackRate = speed;
        document.querySelectorAll('.speed-btn').forEach(btn => {
            btn.classList.toggle('text-white', btn.textContent.includes(speed + 'x'));
            btn.classList.toggle('active', btn.textContent.includes(speed + 'x'));
        });
    }

    function toggleFullscreen() {
        const viewport = document.getElementById('monitorViewport');
        if (!document.fullscreenElement) {
            viewport?.requestFullscreen().catch(() => {});
        } else {
            document.exitFullscreen().catch(() => {});
        }
    }

    // 4. Scope Letterbox Masking Engine
    function setScopeMask(type) {
        const top = document.getElementById('maskTop');
        const bottom = document.getElementById('maskBottom');
        const left = document.getElementById('maskLeft');
        const right = document.getElementById('maskRight');
        const selector = document.getElementById('scopeMaskSelector');

        if (selector) selector.value = type;

        // Reset
        top.style.height = '0%';
        bottom.style.height = '0%';
        left.style.width = '0%';
        right.style.width = '0%';

        if (type === 'anamorphic') {
            // 2.39:1 Scope
            top.style.height = '12.5%';
            bottom.style.height = '12.5%';
        } else if (type === 'vertical') {
            // 9:16 Vertical Phone
            left.style.width = '35%';
            right.style.width = '35%';
        } else if (type === 'classic43') {
            // 4:3 Academy
            left.style.width = '12.5%';
            right.style.width = '12.5%';
        }
    }

    // 5. Live Title Generator
    function updateLiveTitle() {
        const headline = document.getElementById('titleText')?.value || '';
        const subtitle = document.getElementById('subtitleText')?.value || '';
        const font = document.getElementById('titleFont')?.value || "'Syncopate', sans-serif";
        const color = document.getElementById('titleColor')?.value || '#ffffff';
        const glow = document.getElementById('titleGlowRange')?.value || 10;
        const visible = document.getElementById('titleVisible')?.checked ?? true;
        const pos = document.getElementById('titlePosition')?.value || 'center';

        const glowVal = document.getElementById('titleGlowVal');
        if (glowVal) glowVal.textContent = glow + 'px';

        const overlay = document.getElementById('canvasTitleOverlay');
        const elHead = document.getElementById('overlayHeadline');
        const elSub = document.getElementById('overlaySubtitle');

        if (!overlay || !elHead || !elSub) return;

        overlay.style.display = visible ? 'block' : 'none';
        elHead.textContent = headline;
        elSub.textContent = subtitle;

        elHead.style.fontFamily = font;
        elHead.style.color = color;
        elHead.style.textShadow = `0 2px ${glow}px rgba(0,0,0,0.95), 0 0 ${glow}px ${color}`;

        // Positioning
        if (pos === 'center') {
            overlay.style.bottom = '40%';
            elHead.style.fontSize = '2rem';
        } else if (pos === 'lower_third') {
            overlay.style.bottom = '12%';
            overlay.style.textAlign = 'left';
            elHead.style.fontSize = '1.4rem';
        } else if (pos === 'subtitle') {
            overlay.style.bottom = '6%';
            overlay.style.textAlign = 'center';
            elHead.style.fontSize = '1.1rem';
        } else if (pos === 'watermark') {
            overlay.style.bottom = '85%';
            overlay.style.textAlign = 'right';
            elHead.style.fontSize = '0.9rem';
        }
    }

    // 6. Live Transitions & FX Engine
    let currentSelectedTransition = 'fade';

    function selectTransition(type, el) {
        currentSelectedTransition = type;
        document.querySelectorAll('.trans-card').forEach(c => c.classList.remove('active'));
        if (el) el.classList.add('active');
        triggerTransition(type);
    }

    function setTransCategory(cat, btn) {
        document.querySelectorAll('.trans-cat-pill').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        const cards = document.querySelectorAll('.trans-card');
        let count = 0;
        cards.forEach(c => {
            if (cat === 'all' || c.getAttribute('data-category') === cat) {
                c.style.display = 'block';
                count++;
            } else {
                c.style.display = 'none';
            }
        });
        const badge = document.getElementById('transCountBadge');
        if (badge) badge.textContent = `${count} Presets`;
    }

    function filterTransitions() {
        const query = (document.getElementById('transSearchInput')?.value || '').toLowerCase().trim();
        const cards = document.querySelectorAll('.trans-card');
        let count = 0;
        cards.forEach(c => {
            const text = c.textContent.toLowerCase();
            if (!query || text.includes(query)) {
                c.style.display = 'block';
                count++;
            } else {
                c.style.display = 'none';
            }
        });
        const badge = document.getElementById('transCountBadge');
        if (badge) badge.textContent = `${count} Presets`;
    }

    function updateTransDurationVal(val) {
        const span = document.getElementById('transDurationVal');
        if (span) span.textContent = (val / 1000).toFixed(1) + 's';
    }

    function setTransDurationPreset(ms) {
        const input = document.getElementById('transDuration');
        if (input) {
            input.value = ms;
            updateTransDurationVal(ms);
        }
    }

    function playSelectedTransition() {
        triggerTransition(currentSelectedTransition);
    }

    function applyTransitionToClip(target) {
        const toast = document.getElementById('transToast');
        const msg = document.getElementById('transToastMsg');
        if (!toast || !msg) return;

        const niceName = currentSelectedTransition.replace('_', ' ').toUpperCase();
        if (target === 'all') {
            const cuts = document.querySelectorAll('.clip-video').length || 1;
            msg.innerHTML = `<strong>${niceName}</strong> applied to all ${cuts} cuts in timeline!`;
        } else {
            msg.innerHTML = `<strong>${niceName}</strong> applied to current sequence cut!`;
        }
        toast.classList.remove('d-none');
        playSelectedTransition();

        setTimeout(() => {
            toast.classList.add('d-none');
        }, 3500);
    }

    function triggerTransition(type) {
        const video = document.getElementById('mainPlayer');
        const overlay = document.getElementById('transFxOverlay');
        if (!video) return;

        const durMs = parseInt(document.getElementById('transDuration')?.value || 800);
        const durSec = durMs / 1000;
        const dir = document.getElementById('transDirection')?.value || 'lr';
        const easing = document.getElementById('transEasing')?.value || 'ease-in-out';
        const intensity = (parseInt(document.getElementById('transIntensity')?.value || 80)) / 100;
        const syncAudio = document.getElementById('transAudioSync')?.checked;
        const audioProfile = document.getElementById('transAudioProfile')?.value || 'auto';

        // Auto-play Synced Sound FX
        if (syncAudio) {
            let soundToPlay = audioProfile;
            if (audioProfile === 'auto') {
                if (type === 'flash' || type === 'laser_streak') soundToPlay = 'laser_whoosh';
                else if (type === 'glitch' || type === 'vhs_roll' || type === 'pixelate') soundToPlay = 'vhs_glitch';
                else if (type === 'camera_shake' || type === 'cube_flip') soundToPlay = 'bass_boom';
                else if (type === 'bloom' || type === 'color_dip') soundToPlay = 'tension_riser';
                else if (type === 'film_burn') soundToPlay = 'laser_whoosh';
                else soundToPlay = 'laser_whoosh';
            }
            if (soundToPlay !== 'none') {
                playSfx(soundToPlay);
            }
        }

        video.style.transition = `all ${durSec / 2}s ${easing}`;
        if (overlay) {
            overlay.style.transition = `all ${durSec / 2}s ${easing}`;
            overlay.style.background = 'none';
            overlay.style.opacity = '0';
            overlay.style.filter = 'none';
            overlay.style.clipPath = 'none';
        }

        const halfDur = (durMs / 2);

        // Transition Animation Logic
        if (type === 'fade') {
            video.style.opacity = '0';
            setTimeout(() => { video.style.opacity = '1'; }, halfDur);
        } 
        else if (type === 'flash') {
            video.style.filter = `brightness(${200 + intensity * 200}%) contrast(${120 + intensity * 60}%)`;
            if (overlay) {
                overlay.style.background = 'rgba(255, 255, 255, 0.95)';
                overlay.style.opacity = `${intensity}`;
            }
            setTimeout(() => { 
                video.style.filter = 'none'; 
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'film_burn') {
            video.style.filter = `sepia(${intensity * 0.8}) brightness(180%) contrast(140%)`;
            if (overlay) {
                overlay.style.background = 'radial-gradient(circle at 60% 40%, rgba(255, 140, 0, 0.9) 0%, rgba(255, 40, 0, 0.7) 40%, rgba(255, 240, 100, 0.95) 70%, transparent 100%)';
                overlay.style.opacity = `${intensity * 0.95}`;
            }
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'bloom') {
            video.style.filter = `blur(${intensity * 12}px) brightness(200%) saturate(200%)`;
            if (overlay) {
                overlay.style.background = 'radial-gradient(circle, rgba(13, 202, 240, 0.5) 0%, rgba(255, 255, 255, 0.7) 50%, transparent 80%)';
                overlay.style.opacity = `${intensity * 0.8}`;
            }
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'color_dip') {
            video.style.filter = `hue-rotate(180deg) saturate(350%) brightness(150%)`;
            if (overlay) {
                overlay.style.background = 'rgba(13, 202, 240, 0.6)';
                overlay.style.opacity = `${intensity * 0.8}`;
            }
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'wipe') {
            let trans = 'translateX(-100%)';
            if (dir === 'rl') trans = 'translateX(100%)';
            else if (dir === 'tb') trans = 'translateY(-100%)';
            else if (dir === 'bt') trans = 'translateY(100%)';
            else if (dir === 'center') trans = 'scale(0.05)';

            video.style.transform = trans;
            video.style.opacity = '0.4';
            setTimeout(() => {
                video.style.transform = 'none';
                video.style.opacity = '1';
            }, halfDur);
        }
        else if (type === 'barn_door') {
            if (overlay) {
                overlay.style.background = 'linear-gradient(90deg, #000 50%, transparent 50%), linear-gradient(-90deg, #000 50%, transparent 50%)';
                overlay.style.opacity = '1';
            }
            video.style.transform = 'scale(0.92)';
            setTimeout(() => {
                video.style.transform = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'radial_iris') {
            video.style.clipPath = 'circle(0% at 50% 50%)';
            if (overlay) {
                overlay.style.background = '#000';
                overlay.style.opacity = '0.8';
            }
            setTimeout(() => {
                video.style.clipPath = 'circle(100% at 50% 50%)';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'diagonal_slice') {
            video.style.transform = 'skew(-20deg) translateX(-80%)';
            video.style.opacity = '0.3';
            setTimeout(() => {
                video.style.transform = 'none';
                video.style.opacity = '1';
            }, halfDur);
        }
        else if (type === 'venetian_blinds') {
            if (overlay) {
                overlay.style.background = 'repeating-linear-gradient(0deg, #000, #000 12px, transparent 12px, transparent 24px)';
                overlay.style.opacity = `${intensity * 0.9}`;
            }
            video.style.filter = 'brightness(140%)';
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'zoom') {
            video.style.transform = `scale(${1.2 + intensity * 0.8})`;
            video.style.filter = `blur(${intensity * 10}px)`;
            setTimeout(() => { 
                video.style.transform = 'none';
                video.style.filter = 'none';
            }, halfDur);
        }
        else if (type === 'zoom_out') {
            video.style.transform = `scale(${0.7 - intensity * 0.3})`;
            video.style.filter = `blur(${intensity * 8}px)`;
            setTimeout(() => {
                video.style.transform = 'none';
                video.style.filter = 'none';
            }, halfDur);
        }
        else if (type === 'vortex_spin') {
            video.style.transform = `rotate(360deg) scale(${0.8 - intensity * 0.2})`;
            video.style.filter = `blur(${intensity * 8}px) hue-rotate(90deg)`;
            setTimeout(() => {
                video.style.transform = 'none';
                video.style.filter = 'none';
            }, halfDur);
        }
        else if (type === 'motion_blur_push') {
            const shift = dir === 'rl' ? '70%' : '-70%';
            video.style.transform = `translateX(${shift}) scale(1.05)`;
            video.style.filter = `blur(${intensity * 14}px)`;
            setTimeout(() => {
                video.style.transform = 'none';
                video.style.filter = 'none';
            }, halfDur);
        }
        else if (type === 'camera_shake') {
            let count = 0;
            const shakeInterval = setInterval(() => {
                const rx = (Math.random() - 0.5) * 30 * intensity;
                const ry = (Math.random() - 0.5) * 20 * intensity;
                const rrot = (Math.random() - 0.5) * 6 * intensity;
                video.style.transform = `translate(${rx}px, ${ry}px) rotate(${rrot}deg)`;
                count++;
                if (count > 6) {
                    clearInterval(shakeInterval);
                    video.style.transform = 'none';
                }
            }, 40);
        }
        else if (type === 'cube_flip') {
            video.style.transform = 'perspective(900px) rotateY(90deg) scale(0.85)';
            video.style.opacity = '0.3';
            setTimeout(() => {
                video.style.transform = 'none';
                video.style.opacity = '1';
            }, halfDur);
        }
        else if (type === 'glitch') {
            video.style.filter = 'hue-rotate(180deg) saturate(350%) contrast(150%)';
            video.style.transform = `skewX(${intensity * 14}deg) scale(1.06)`;
            if (overlay) {
                overlay.style.background = 'repeating-linear-gradient(0deg, rgba(255,0,0,0.3) 0, rgba(0,255,255,0.3) 3px, transparent 4px, transparent 8px)';
                overlay.style.opacity = '1';
            }
            setTimeout(() => { 
                video.style.filter = 'none';
                video.style.transform = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'vhs_roll') {
            video.style.filter = 'grayscale(60%) contrast(160%)';
            if (overlay) {
                overlay.style.background = 'linear-gradient(180deg, rgba(255,255,255,0.8) 0%, transparent 15%, rgba(0,0,0,0.8) 85%, rgba(255,255,255,0.8) 100%)';
                overlay.style.opacity = `${intensity * 0.9}`;
            }
            video.style.transform = 'translateY(8px)';
            setTimeout(() => {
                video.style.filter = 'none';
                video.style.transform = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'pixelate') {
            video.style.filter = `blur(${intensity * 12}px) contrast(200%) saturate(150%)`;
            if (overlay) {
                overlay.style.background = 'radial-gradient(rgba(0,0,0,0.4) 15%, transparent 16%) 0 0 / 8px 8px';
                overlay.style.opacity = `${intensity}`;
            }
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'thermal_flash') {
            video.style.filter = 'invert(100%) hue-rotate(90deg) contrast(200%) saturate(300%)';
            setTimeout(() => {
                video.style.filter = 'none';
            }, halfDur);
        }
        else if (type === 'laser_streak') {
            if (overlay) {
                overlay.style.background = 'linear-gradient(180deg, transparent 40%, rgba(13, 202, 240, 0.95) 50%, rgba(255, 255, 255, 1) 51%, rgba(13, 202, 240, 0.95) 52%, transparent 60%)';
                overlay.style.opacity = '1';
            }
            video.style.filter = 'brightness(200%)';
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
        else if (type === 'prism_split') {
            if (overlay) {
                overlay.style.background = 'linear-gradient(45deg, rgba(255,0,0,0.5), rgba(255,255,0,0.5), rgba(0,255,0,0.5), rgba(0,255,255,0.5), rgba(0,0,255,0.5), rgba(255,0,255,0.5))';
                overlay.style.opacity = `${intensity * 0.85}`;
            }
            video.style.filter = 'hue-rotate(90deg) saturate(250%)';
            setTimeout(() => {
                video.style.filter = 'none';
                if (overlay) overlay.style.opacity = '0';
            }, halfDur);
        }
    }

    // 7. Synthesized Sound FX Player (Web Audio API)
    function playSfx(type) {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const now = audioCtx.currentTime;

            if (type === 'bass_boom') {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(140, now);
                osc.frequency.exponentialRampToValueAtTime(30, now + 1.2);
                gain.gain.setValueAtTime(0.8, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 1.2);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 1.2);
            } else if (type === 'laser_whoosh') {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(800, now);
                osc.frequency.exponentialRampToValueAtTime(80, now + 0.4);
                gain.gain.setValueAtTime(0.5, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 0.4);
            } else if (type === 'vhs_glitch') {
                const bufferSize = audioCtx.sampleRate * 0.3;
                const buffer = audioCtx.createBuffer(1, bufferSize, audioCtx.sampleRate);
                const data = buffer.getChannelData(0);
                for (let i = 0; i < bufferSize; i++) data[i] = Math.random() * 2 - 1;
                const noise = audioCtx.createBufferSource();
                noise.buffer = buffer;
                const gain = audioCtx.createGain();
                gain.gain.setValueAtTime(0.4, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
                noise.connect(gain);
                gain.connect(audioCtx.destination);
                noise.start(now);
            } else if (type === 'tension_riser') {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(120, now);
                osc.frequency.linearRampToValueAtTime(600, now + 1.5);
                gain.gain.setValueAtTime(0.4, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 1.5);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 1.5);
            } else if (type === 'laser_hit') {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(1200, now);
                osc.frequency.exponentialRampToValueAtTime(60, now + 0.35);
                gain.gain.setValueAtTime(0.7, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 0.35);
            } else if (type === 'footstep') {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(80, now);
                osc.frequency.exponentialRampToValueAtTime(20, now + 0.2);
                gain.gain.setValueAtTime(0.9, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.2);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start(now);
                osc.stop(now + 0.2);
            }
        } catch(e) {
            console.error('Audio FX error:', e);
        }
    }

    // 8. 4K PNG Snapshot Grabber
    function takeSnapshot() {
        const video = document.getElementById('mainPlayer');
        if (!video) {
            alert('No video available to capture snapshot.');
            return;
        }
        const canvas = document.createElement('canvas');
        canvas.width = video.videoWidth || 1920;
        canvas.height = video.videoHeight || 1080;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const a = document.createElement('a');
        a.download = `VOID_STILL_${Date.now()}.png`;
        a.href = canvas.toDataURL('image/png');
        a.click();
    }

    // 9. Live Video Transforms, Ken Burns & VFX
    function applyTransform() {
        const video = document.getElementById('mainPlayer');
        if (!video) return;
        const zoom = document.getElementById('zoomRange')?.value || 100;
        const posX = document.getElementById('posX')?.value || 0;
        const posY = document.getElementById('posY')?.value || 0;
        const rot = document.getElementById('rotateRange')?.value || 0;
        const opacity = document.getElementById('opacityRange')?.value || 100;

        document.getElementById('zoomValue').textContent = zoom + '%';
        document.getElementById('rotateValue').textContent = rot + '°';
        document.getElementById('opacityValue').textContent = opacity + '%';

        video.style.transform = `translate(${posX}px, ${posY}px) scale(${zoom/100}) rotate(${rot}deg)`;
        video.style.opacity = opacity / 100;
    }

    function resetTransform() {
        document.getElementById('zoomRange').value = 100;
        document.getElementById('posX').value = 0;
        document.getElementById('posY').value = 0;
        document.getElementById('rotateRange').value = 0;
        document.getElementById('opacityRange').value = 100;
        document.getElementById('kenBurnsPreset').value = 'none';
        const video = document.getElementById('mainPlayer');
        if (video) video.style.transition = 'none';
        applyTransform();
    }

    function applyKenBurns(preset) {
        const video = document.getElementById('mainPlayer');
        if (!video) return;
        video.style.transition = 'transform 12s cubic-bezier(0.25, 1, 0.5, 1)';
        
        if (preset === 'zoom_in') {
            video.style.transform = 'scale(1.25)';
        } else if (preset === 'zoom_out') {
            video.style.transform = 'scale(0.85)';
        } else if (preset === 'pan_lr') {
            video.style.transform = 'scale(1.15) translateX(30px)';
        } else if (preset === 'pan_rl') {
            video.style.transform = 'scale(1.15) translateX(-30px)';
        } else {
            video.style.transform = 'none';
        }
    }

    function updateVfxFilters() {
        const grainToggle = document.getElementById('vfxGrainToggle')?.checked;
        const grainStock = document.getElementById('vfxGrainStock')?.value || '35mm';
        const gateWeave = document.getElementById('vfxGateWeaveToggle')?.checked;
        const vignette = parseInt(document.getElementById('vfxVignetteRange')?.value || 0);
        const halation = parseInt(document.getElementById('vfxHalationRange')?.value || 0);
        const aberration = parseInt(document.getElementById('vfxAberrationRange')?.value || 0);
        const flare = document.getElementById('vfxFlareToggle')?.checked;
        const proMist = parseInt(document.getElementById('vfxProMistRange')?.value || 0);
        const tiltShift = document.getElementById('vfxTiltShiftToggle')?.checked;
        const crt = document.getElementById('vfxCrtToggle')?.checked;

        if (document.getElementById('vfxVignetteVal')) document.getElementById('vfxVignetteVal').textContent = vignette + '%';
        if (document.getElementById('vfxHalationVal')) document.getElementById('vfxHalationVal').textContent = halation + '%';
        if (document.getElementById('vfxAberrationVal')) document.getElementById('vfxAberrationVal').textContent = aberration + '%';
        if (document.getElementById('vfxProMistVal')) document.getElementById('vfxProMistVal').textContent = proMist + '%';

        const video = document.getElementById('mainPlayer');
        const container = document.getElementById('monitorViewport');
        const grainLayer = document.getElementById('vfxGrainLayer');
        const crtLayer = document.getElementById('vfxCrtLayer');
        const halationLayer = document.getElementById('vfxHalationLayer');
        const flareLayer = document.getElementById('vfxFlareLayer');
        const tiltShiftLayer = document.getElementById('vfxTiltShiftLayer');

        // Toggle Overlay Layers
        if (grainLayer) {
            grainLayer.className = grainToggle ? 'vfx-layer vfx-grain-active' : 'vfx-layer';
            if (grainToggle) {
                const size = grainStock === '8mm' ? '5px 5px' : (grainStock === '16mm' ? '3.5px 3.5px' : '2px 2px');
                const opacity = grainStock === '8mm' ? '0.85' : (grainStock === '16mm' ? '0.75' : '0.55');
                grainLayer.style.backgroundSize = size;
                grainLayer.style.opacity = opacity;
            }
        }

        if (crtLayer) {
            crtLayer.className = crt ? 'vfx-layer vfx-crt-active' : 'vfx-layer';
        }

        if (halationLayer) {
            halationLayer.className = halation > 0 ? 'vfx-layer vfx-halation-active' : 'vfx-layer';
            if (halation > 0) {
                halationLayer.style.boxShadow = `inset 0 0 ${halation * 1.3}px rgba(255, 45, 10, ${halation / 130})`;
            }
        }

        if (flareLayer) {
            flareLayer.className = flare ? 'vfx-layer vfx-flare-active' : 'vfx-layer';
        }

        if (tiltShiftLayer) {
            tiltShiftLayer.className = tiltShift ? 'vfx-layer vfx-tiltshift-active' : 'vfx-layer';
        }

        // Gate Weave Wobble Animation
        if (video) {
            if (gateWeave) {
                video.classList.add('gate-weave-active');
            } else {
                video.classList.remove('gate-weave-active');
            }
        }

        // Vignette Shadow on Monitor Viewport
        if (container) {
            if (vignette > 0) {
                container.style.boxShadow = `inset 0 0 ${vignette * 1.6}px rgba(0,0,0,0.95)`;
            } else {
                container.style.boxShadow = 'none';
            }
        }

        renderCompositeFilters();
    }

    function updateLetterboxMatte(val) {
        if (document.getElementById('vfxLetterboxVal')) document.getElementById('vfxLetterboxVal').textContent = val + '%';
        const top = document.getElementById('maskTop');
        const bottom = document.getElementById('maskBottom');
        if (top && bottom) {
            top.style.height = val + '%';
            bottom.style.height = val + '%';
        }
    }

    function setScopePreset(val) {
        const input = document.getElementById('vfxLetterboxRange');
        if (input) {
            input.value = val;
            updateLetterboxMatte(val);
        }
    }

    function resetVfxFilters() {
        if (document.getElementById('vfxGrainToggle')) document.getElementById('vfxGrainToggle').checked = false;
        if (document.getElementById('vfxGateWeaveToggle')) document.getElementById('vfxGateWeaveToggle').checked = false;
        if (document.getElementById('vfxVignetteRange')) document.getElementById('vfxVignetteRange').value = 0;
        if (document.getElementById('vfxHalationRange')) document.getElementById('vfxHalationRange').value = 0;
        if (document.getElementById('vfxAberrationRange')) document.getElementById('vfxAberrationRange').value = 0;
        if (document.getElementById('vfxFlareToggle')) document.getElementById('vfxFlareToggle').checked = false;
        if (document.getElementById('vfxProMistRange')) document.getElementById('vfxProMistRange').value = 0;
        if (document.getElementById('vfxTiltShiftToggle')) document.getElementById('vfxTiltShiftToggle').checked = false;
        if (document.getElementById('vfxCrtToggle')) document.getElementById('vfxCrtToggle').checked = false;
        if (document.getElementById('vfxLetterboxRange')) document.getElementById('vfxLetterboxRange').value = 0;
        updateVfxFilters();
        updateLetterboxMatte(0);
    }

    function applyVfxToAllScenes() {
        const toast = document.getElementById('transToast');
        const msg = document.getElementById('transToastMsg');
        if (toast && msg) {
            msg.innerHTML = `<strong>Cinematic VFX Suite</strong> applied to all ${document.querySelectorAll('.clip-video').length || 1} timeline clips!`;
            toast.classList.remove('d-none');
            setTimeout(() => toast.classList.add('d-none'), 3500);
        }
    }

    // 10. Color Grading Suite & 3D LUT Presets
    let currentLutPreset = 'none';

    const LUT_PRESETS = {
        'none': { name: 'Standard Rec.709', filter: '' },
        'kodak2383': { name: 'Kodak Vision3 2383', filter: 'sepia(18%) contrast(122%) brightness(102%) saturate(118%)' },
        'fuji_eterna': { name: 'Fujifilm Eterna 250D', filter: 'hue-rotate(15deg) contrast(115%) saturate(110%) brightness(104%)' },
        'teal_orange': { name: 'Teal & Orange', filter: 'contrast(135%) saturate(145%) hue-rotate(25deg) brightness(102%)' },
        'cyberpunk': { name: 'Blade Runner Cyberpunk', filter: 'hue-rotate(190deg) contrast(145%) saturate(180%) brightness(95%)' },
        'matrix': { name: 'The Matrix Green', filter: 'hue-rotate(75deg) saturate(160%) contrast(135%) brightness(98%)' },
        'bleach_bypass': { name: 'Bleach Bypass Silver', filter: 'saturate(55%) contrast(155%) brightness(96%)' },
        'dune_gold': { name: 'Dune Arrakis Gold', filter: 'sepia(35%) hue-rotate(-15deg) contrast(125%) saturate(135%) brightness(105%)' },
        'fincher': { name: 'Fincher Mood Green', filter: 'hue-rotate(50deg) saturate(75%) contrast(140%) brightness(88%)' },
        'noir': { name: 'Master Shadow Noir', filter: 'grayscale(100%) contrast(165%) brightness(92%)' },
        'kodachrome': { name: 'Kodachrome 64 Vintage', filter: 'sepia(20%) saturate(165%) contrast(128%) brightness(103%)' },
        'moonlight': { name: 'Cold Moonlight Blue', filter: 'hue-rotate(160deg) saturate(90%) contrast(130%) brightness(88%)' },
        'technicolor': { name: 'Technicolor 3-Strip', filter: 'saturate(220%) contrast(130%) brightness(102%)' }
    };

    function applyLut(type, el) {
        currentLutPreset = type;
        document.querySelectorAll('.lut-card').forEach(c => c.classList.remove('active'));
        if (el) el.classList.add('active');

        const label = document.getElementById('activeLutLabel');
        if (label && LUT_PRESETS[type]) {
            label.textContent = LUT_PRESETS[type].name;
        }

        renderCompositeFilters();
    }

    function updateColorGrade() {
        const exp = document.getElementById('cgExposure')?.value || 100;
        const con = document.getElementById('cgContrast')?.value || 100;
        const sat = document.getElementById('cgSaturation')?.value || 100;
        const temp = document.getElementById('cgTemperature')?.value || 0;
        const hue = parseInt(document.getElementById('cgHueRotation')?.value || 0);

        if (document.getElementById('cgExpVal')) document.getElementById('cgExpVal').textContent = (exp / 100).toFixed(2);
        if (document.getElementById('cgConVal')) document.getElementById('cgConVal').textContent = (con / 100).toFixed(2);
        if (document.getElementById('cgSatVal')) document.getElementById('cgSatVal').textContent = (sat / 100).toFixed(2);
        if (document.getElementById('cgTempVal')) document.getElementById('cgTempVal').textContent = (5600 + temp * 40) + 'K';
        if (document.getElementById('cgHueVal')) document.getElementById('cgHueVal').textContent = hue + '°';

        renderCompositeFilters();
    }

    function renderCompositeFilters() {
        const video = document.getElementById('mainPlayer');
        const colorVideo = document.getElementById('colorPlayer');

        const exp = document.getElementById('cgExposure')?.value || 100;
        const con = document.getElementById('cgContrast')?.value || 100;
        const sat = document.getElementById('cgSaturation')?.value || 100;
        const temp = document.getElementById('cgTemperature')?.value || 0;
        const hue = parseInt(document.getElementById('cgHueRotation')?.value || 0);

        const proMist = parseInt(document.getElementById('vfxProMistRange')?.value || 0);
        const aberration = parseInt(document.getElementById('vfxAberrationRange')?.value || 0);

        let filterParts = [];

        // 1. 3D LUT Base Filter
        if (LUT_PRESETS[currentLutPreset]?.filter) {
            filterParts.push(LUT_PRESETS[currentLutPreset].filter);
        }

        // 2. Primary Color Grade Adjustments
        const totalHue = hue + (temp * 0.4);
        filterParts.push(`brightness(${exp}%) contrast(${con}%) saturate(${sat}%)`);
        if (totalHue !== 0) {
            filterParts.push(`hue-rotate(${totalHue}deg)`);
        }

        // 3. Pro-Mist Soft Glow
        if (proMist > 0) {
            filterParts.push(`drop-shadow(0 0 ${proMist * 0.15}px rgba(255,255,255,${proMist / 200}))`);
        }

        const filterString = filterParts.join(' ').trim() || 'none';

        if (video) video.style.filter = filterString;
        if (colorVideo) colorVideo.style.filter = filterString;
    }

    function resetColorGrade() {
        if (document.getElementById('cgExposure')) document.getElementById('cgExposure').value = 100;
        if (document.getElementById('cgContrast')) document.getElementById('cgContrast').value = 100;
        if (document.getElementById('cgSaturation')) document.getElementById('cgSaturation').value = 100;
        if (document.getElementById('cgTemperature')) document.getElementById('cgTemperature').value = 0;
        if (document.getElementById('cgHueRotation')) document.getElementById('cgHueRotation').value = 0;
        applyLut('none', document.querySelector('.lut-card'));
        updateColorGrade();
    }

    function applyGradeToAllScenes() {
        const toast = document.getElementById('cgToast');
        const msg = document.getElementById('cgToastMsg');
        if (toast && msg) {
            const lutName = LUT_PRESETS[currentLutPreset]?.name || 'Rec.709';
            msg.innerHTML = `<strong>${lutName}</strong> grade batch-applied across all timeline scenes!`;
            toast.classList.remove('d-none');
            setTimeout(() => toast.classList.add('d-none'), 3500);
        }
    }

    // 11. Sidecar Subtitles (.SRT), EDL & Shotlist CSV Export
    function exportSRT() {
        let srt = '';
        const clips = document.querySelectorAll('[data-timeline-clip]');
        let index = 1;
        let curSec = 0;

        clips.forEach((c) => {
            const title = c.getAttribute('title') || '';
            const script = title.split(' · ')[1] || 'Dialogue segment';
            
            const startMin = String(Math.floor(curSec / 60)).padStart(2, '0');
            const startSec = String(Math.floor(curSec % 60)).padStart(2, '0');
            const endSecVal = curSec + 15;
            const endMin = String(Math.floor(endSecVal / 60)).padStart(2, '0');
            const endSec = String(Math.floor(endSecVal % 60)).padStart(2, '0');

            srt += `${index}\n00:${startMin}:${startSec},000 --> 00:${endMin}:${endSec},000\n${script}\n\n`;
            curSec = endSecVal;
            index++;
        });

        const blob = new Blob([srt], { type: 'text/plain' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `project_subtitles.srt`;
        a.click();
    }

    function exportNleEDL() {
        let edl = `TITLE: VOID_STUDIO_EXPORT\nFCM: NON-DROP FRAME\n\n`;
        let curSec = 0;
        const clips = document.querySelectorAll('[data-timeline-clip]');
        
        clips.forEach((c, i) => {
            const inSec = curSec;
            const outSec = curSec + 15;
            curSec = outSec;
            
            const inTC = `01:00:${String(Math.floor(inSec)).padStart(2,'0')}:00`;
            const outTC = `01:00:${String(Math.floor(outSec)).padStart(2,'0')}:00`;
            const edlIndex = String(i + 1).padStart(3, '0');
            
            edl += `${edlIndex}  AX       V     C        00:00:00:00 00:00:15:00 ${inTC} ${outTC}\n`;
            edl += `* FROM CLIP: SCENE_${edlIndex}\n\n`;
        });

        const blob = new Blob([edl], { type: 'text/plain' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `sequence.edl`;
        a.click();
    }

    function exportShotlistCSV() {
        let csv = "Sequence,Status,Duration,Prompt\n";
        const clips = document.querySelectorAll('[data-timeline-clip]');
        clips.forEach((c, i) => {
            const title = c.getAttribute('title') || '';
            const script = (title.split(' · ')[1] || '').replace(/"/g, '""');
            csv += `"${i+1}","ONLINE","15.00s","${script}"\n`;
        });

        const blob = new Blob([csv], { type: 'text/csv' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `shotlist.csv`;
        a.click();
    }

    // 12. Generative Ambient Synthesizer in Fairlight
    function toggleNleAmbience() {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') audioCtx.resume();

        const btn = document.getElementById('btnNleAmbience');
        const icon = document.getElementById('nleAmbienceIcon');
        const text = document.getElementById('nleAmbienceText');
        const preset = document.getElementById('nleAmbiencePreset')?.value || 'space';

        if (isNleAmbiencePlaying) {
            nleAmbienceOscillators.forEach(o => o.stop());
            nleAmbienceOscillators = [];
            isNleAmbiencePlaying = false;
            icon.className = 'bi bi-play-fill me-1';
            text.textContent = 'PLAY AMBIENCE';
            btn.className = 'btn btn-sm btn-info text-black font-mono py-0 px-2 fw-bold';
        } else {
            nleAmbienceGain = audioCtx.createGain();
            nleAmbienceGain.gain.setValueAtTime(0.25, audioCtx.currentTime);
            nleAmbienceGain.connect(audioCtx.destination);

            let freqs = [40, 80, 120];
            if (preset === 'noir') freqs = [55, 110, 164.81];
            if (preset === 'tension') freqs = [46.25, 92.50, 138.59];

            freqs.forEach(f => {
                const osc = audioCtx.createOscillator();
                osc.type = preset === 'noir' ? 'sawtooth' : 'sine';
                osc.frequency.setValueAtTime(f, audioCtx.currentTime);
                osc.connect(nleAmbienceGain);
                osc.start();
                nleAmbienceOscillators.push(osc);
            });

            isNleAmbiencePlaying = true;
            icon.className = 'bi bi-stop-fill me-1';
            text.textContent = 'STOP AMBIENCE';
            btn.className = 'btn btn-sm btn-danger text-white font-mono py-0 px-2 fw-bold';
        }
    }

    // 13. Audio & Metering
    function applyPlayerVolume() {
        const video = document.getElementById('mainPlayer');
        const state = document.getElementById('mixerState');
        if (!video) {
            if (state) { state.textContent = 'OFFLINE'; state.className = 'font-mono text-secondary'; }
            return;
        }

        const effectiveVolume = mixerMuted ? 0 : masterVolume * (mixerDim ? 0.35 : 1);
        video.volume = Math.max(0, Math.min(1, effectiveVolume));
        video.muted = mixerMuted;

        if (state) {
            state.textContent = mixerMuted ? 'MUTED' : (mixerDim ? 'DIM' : 'ONLINE');
            state.className = `font-mono ${mixerMuted ? 'text-danger' : (mixerDim ? 'text-warning' : 'text-success')}`;
        }
    }

    function setMasterVolume(value) {
        masterVolume = Number(value) / 100;
        document.getElementById('volumeReadout').textContent = `${(20 * Math.log10(Math.max(0.01, masterVolume))).toFixed(1)} dB / ${value}%`;
        applyPlayerVolume();
    }

    function toggleMute() {
        mixerMuted = !mixerMuted;
        document.getElementById('muteBtn')?.classList.toggle('active-muted', mixerMuted);
        applyPlayerVolume();
    }

    function toggleSolo() {
        mixerSolo = !mixerSolo;
        document.getElementById('soloBtn')?.classList.toggle('active-solo', mixerSolo);
    }

    function toggleDim() {
        mixerDim = !mixerDim;
        document.getElementById('dimBtn')?.classList.toggle('active-solo', mixerDim);
        applyPlayerVolume();
    }

    function updateMeters() {
        const video = document.getElementById('mainPlayer');
        if (!video || video.paused || mixerMuted) {
            setMeterHeight(4, 4);
            return;
        }
        const base = (35 + Math.sin(video.currentTime * 6) * 15 + Math.random() * 20) * masterVolume * (mixerDim ? 0.35 : 1);
        setMeterHeight(Math.min(92, base), Math.min(92, base * 0.95));
    }

    function setMeterHeight(left, right) {
        const meterLeft = document.getElementById('meterLeft');
        const meterRight = document.getElementById('meterRight');
        const peakLeft = document.getElementById('peakLeft');
        const peakRight = document.getElementById('peakRight');
        if (meterLeft) meterLeft.style.height = `${left}%`;
        if (meterRight) meterRight.style.height = `${right}%`;
        if (peakLeft) peakLeft.style.bottom = `${Math.max(4, left - 2)}%`;
        if (peakRight) peakRight.style.bottom = `${Math.max(4, right - 2)}%`;
    }

    function updateTimecode() {
        const video = document.getElementById('mainPlayer');
        const timecode = document.getElementById('timecode');
        if (!video || !timecode) return;
        const totalFrames = Math.floor(video.currentTime * 24);
        const seconds = Math.floor(totalFrames / 24);
        const frames = totalFrames % 24;
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        timecode.textContent = `01:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}:${String(frames).padStart(2, '0')}`;
    }

    // 14. AI Enhancer Helpers
    function enhancePrompt() {
        const textarea = document.getElementById('aiPromptTextarea');
        if (!textarea) return;
        const suffixes = [
            ", 35mm anamorphic film, volumetric cinematic lighting, 8k masterpiece",
            ", moody high-contrast atmospheric haze, shallow depth of field, photorealistic",
            ", octane render, golden hour rim lighting, ultra detailed textures"
        ];
        textarea.value += suffixes[Math.floor(Math.random() * suffixes.length)];
    }

    function randomizeSeed() {
        const seedInput = document.getElementById('aiSeedInput');
        if (seedInput) seedInput.value = Math.floor(Math.random() * 99999999);
    }

    // 15. Timeline Tools & Zoom
    function activateTool(el) {
        document.querySelectorAll('.tool-icon[data-tool]').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
    }

    function setTimelineZoom(val) {
        document.querySelectorAll('[data-timeline-clip]').forEach(clip => {
            clip.style.width = `${val}px`;
        });
    }

    function handleRulerClick(e) {
        const ruler = document.getElementById('timelineRuler');
        const rect = ruler.getBoundingClientRect();
        const clickX = e.clientX - rect.left - 150;
        if (clickX >= 0) {
            const playhead = document.getElementById('timelinePlayhead');
            if (playhead) playhead.style.left = `${clickX}px`;
        }
    }

    function addTimelineMarker() {
        alert('Marker added at current playhead timecode.');
    }

    function openShortcutsModal() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('shortcutsModal')).show();
    }

    // Keyboard Shortcuts
    document.addEventListener('keydown', (e) => {
        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
        
        if (e.code === 'Space') { e.preventDefault(); togglePlay(); }
        else if (e.key === 'j' || e.key === 'J') { setSpeed(0.5); }
        else if (e.key === 'k' || e.key === 'K') { setSpeed(1.0); }
        else if (e.key === 'l' || e.key === 'L') { setSpeed(2.0); }
        else if (e.key === '←' || e.code === 'ArrowLeft') { e.preventDefault(); stepFrame(-1); }
        else if (e.key === '→' || e.code === 'ArrowRight') { e.preventDefault(); stepFrame(1); }
        else if (e.key === 'Home') { e.preventDefault(); seekToStart(); }
        else if (e.key === 'End') { e.preventDefault(); seekToEnd(); }
        else if (e.key === 'f' || e.key === 'F') { toggleFullscreen(); }
        else if (e.key === 'm' || e.key === 'M') { toggleMute(); }
        else if (e.key === '1') { switchWorkspace('edit'); }
        else if (e.key === '2') { switchWorkspace('color'); }
        else if (e.key === '3') { switchWorkspace('fairlight'); }
        else if (e.key === '4') { switchWorkspace('deliver'); }
        else if (e.key === '?') { openShortcutsModal(); }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const video = document.getElementById('mainPlayer');
        if (video) {
            video.addEventListener('timeupdate', updateTimecode);
            video.addEventListener('play', () => {
                setPlayIcon(true);
                if (!meterTimer) meterTimer = window.setInterval(updateMeters, 100);
            });
            video.addEventListener('pause', () => {
                setPlayIcon(false);
                window.clearInterval(meterTimer);
                meterTimer = null;
                updateMeters();
            });
            video.addEventListener('ended', () => {
                setPlayIcon(false);
                window.clearInterval(meterTimer);
                meterTimer = null;
                updateMeters();
            });
            applyPlayerVolume();
            updateTimecode();
        }
        updateLiveTitle();
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
