@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom border-secondary pb-3 mt-2">
        <div>
            <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">HORIZONTAL SEQUENCE</h6>
            <h2 class="fw-light tracking-widest mb-0" style="font-family: 'Syncopate';">{{ strtoupper($project->title) }}</h2>
            <div class="mt-3" style="max-width:360px;">
                <label for="timeline-project-selector" class="text-secondary d-block mb-1 tracking-widest uppercase" style="font-size:8px;">Choose Project Timeline</label>
                <select id="timeline-project-selector" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2 tracking-widest" onchange="if(this.value) window.location.href=this.value;">
                    @foreach($timelineProjects as $timelineProject)
                        <option value="{{ route('projects.timeline', $timelineProject) }}" {{ $timelineProject->id === $project->id ? 'selected' : '' }}>
                            {{ strtoupper($timelineProject->title) }}{{ $timelineProject->id === $project->id ? ' / CURRENT' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('scenes.create', ['project' => $project->id]) }}" class="btn btn-outline-light rounded-0 px-4 small uppercase tracking-widest">ADD SCENE +</a>
            <form action="{{ route('projects.render-batch', $project) }}" method="POST" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerText='QUEUEING...';">
                @csrf
                <button type="submit" class="btn btn-white bg-white text-black rounded-0 px-4 fw-bold small uppercase tracking-widest transition-hover" {{ $project->scenes->isEmpty() ? 'disabled' : '' }}>
                    <i class="bi bi-cpu me-2"></i> BATCH RENDER ALL
                </button>
            </form>
        </div>
    </div>

    <div class="timeline-grid pb-4 custom-scrollbar">
        @forelse($project->scenes as $scene)
        <div class="timeline-block bg-black border border-secondary p-3 d-flex flex-column transition-hover shadow-sm">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="badge bg-dark border border-secondary text-white rounded-0 tracking-widest px-2 py-1">
                    SEQ #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}
                </div>
                <span class="badge border border-{{ strtolower($scene->status) == 'ready' ? 'success' : 'warning' }} text-{{ strtolower($scene->status) == 'ready' ? 'success' : 'warning' }} rounded-0 small uppercase tracking-widest" style="font-size: 9px;">
                    {{ $scene->status ?? 'DRAFT' }}
                </span>
            </div>
            
            <div class="ratio ratio-16x9 bg-dark mb-3 border border-secondary position-relative group-hover">
                @if($scene->video_path) 
                    <video
                        src="{{ asset('storage/' . $scene->video_path) }}"
                        class="w-100 h-100 object-fit-cover bg-black timeline-preview"
                        preload="metadata"
                        muted
                    >
                        Your browser does not support HTML5 video playback.
                    </video>
                    <button
                        type="button"
                        class="btn btn-light rounded-circle position-absolute top-50 start-50 translate-middle play-video-button"
                        data-video-src="{{ asset('storage/' . $scene->video_path) }}"
                        data-scene-label="SEQ #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}"
                        aria-label="Play scene {{ $scene->order_index }}"
                    >
                        <i class="bi bi-play-fill fs-4"></i>
                    </button>
                    @if(strtolower($scene->status) === 'processing')
                        <div class="position-absolute top-0 start-0 end-0 bg-warning text-black text-center py-1 fw-bold tracking-widest" style="z-index:4;font-size:8px;">
                            OLD PREVIEW · REPLACEMENT GENERATING
                        </div>
                    @endif
                @else
                    <div class="d-flex flex-column align-items-center justify-content-center text-secondary h-100 bg-black">
                        <i class="bi bi-camera-video-off mb-2 fs-4"></i>
                        <span class="small tracking-widest uppercase" style="font-size: 9px;">AWAITING RENDER</span>
                    </div>
                @endif
                <div class="position-absolute bottom-0 end-0 m-2 badge bg-black border border-secondary rounded-0 font-monospace" style="font-size: 10px;">15s</div>
            </div>

            <div class="flex-grow-1 mb-4">
                <p class="text-white small italic mb-0" style="font-size: 12px; line-height: 1.6;">
                    "{{ Str::limit($scene->script_segment, 75, '...') }}"
                </p>
                @if($scene->characters->isNotEmpty())
                    <div class="d-flex flex-wrap gap-1 mt-3">
                        @foreach($scene->characters as $character)
                            <span class="badge border border-info text-info rounded-0" style="font-size:8px;">
                                {{ strtoupper($character->name) }}{{ $character->image_path ? ' · IMAGE REF' : '' }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($scene->generation_error)
                <div class="text-danger mb-3" style="font-size:10px;">{{ Str::limit($scene->generation_error, 130) }}</div>
            @endif

            @if($scene->generation_job_id && strtolower($scene->status) === 'processing')
                <form action="{{ route('scenes.sync-render', $scene) }}" method="POST" class="mb-2">
                    @csrf
                    <button class="btn btn-outline-warning btn-sm rounded-0 w-100 tracking-widest" type="submit" style="font-size:10px;">CHECK STATUS / IMPORT</button>
                </form>
            @endif

            <div class="mt-auto border-top border-secondary pt-3 d-flex justify-content-between align-items-center gap-2">
                <a href="{{ route('scenes.edit', $scene) }}" class="btn btn-sm btn-outline-light rounded-0 uppercase tracking-widest flex-grow-1" style="font-size: 10px;">
                    <i class="bi bi-sliders me-1"></i> CONFIG
                </a>
                
                <div class="dropdown flex-grow-1">
                    <button class="btn btn-sm btn-dark border-secondary rounded-0 uppercase tracking-widest w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size: 10px;">
                        OPTIONS
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark rounded-0 border-secondary shadow-lg mt-1" style="min-width: 200px;">
                        <li><h6 class="dropdown-header text-secondary tracking-widest" style="font-size: 9px;">CORE ENGINE</h6></li>
                        <li>
                            <form action="{{ route('scenes.render', $scene) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item small tracking-widest uppercase text-info py-2" style="font-size:10px;"><i class="bi bi-cpu me-2 fs-6 align-middle"></i> {{ $scene->video_path ? 'Regenerate Variation' : 'Render Single' }}</button>
                            </form>
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
        @empty
        <div class="timeline-empty border border-dashed border-secondary text-center bg-black">
            <i class="bi bi-film text-secondary d-block mb-3" style="font-size:3rem;"></i>
            <h5 class="text-white tracking-widest uppercase">{{ $project->title }} Timeline Is Empty</h5>
            <p class="text-secondary small mb-4">Scenes and generated videos belong to one project only. Create this project's first scene to begin its timeline.</p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="{{ route('scenes.create', ['project' => $project->id]) }}" class="btn btn-white bg-white text-black rounded-0 px-4 py-2 fw-bold small tracking-widest uppercase">Create First Scene +</a>
                @php($projectWithScenes = \App\Models\Project::where('user_id', auth()->id())->whereHas('scenes')->whereKeyNot($project->id)->latest('updated_at')->first())
                @if($projectWithScenes)
                    <a href="{{ route('projects.timeline', $projectWithScenes) }}" class="btn btn-outline-info rounded-0 px-4 py-2 small tracking-widest uppercase">Open {{ $projectWithScenes->title }}</a>
                @endif
            </div>
        </div>
        @endforelse
    </div>
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.15em; }
    .italic { font-style: italic; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    .form-select:focus { background-color:#000; border-color:#fff !important; box-shadow:none; color:#fff; }
    .form-select option { background:#000; color:#fff; }
    
    /* Cinematic Hover Effects */
    .transition-hover { transition: all 0.3s ease; }
    .transition-hover:hover { border-color: #fff !important; transform: translateY(-4px); background-color: #0a0a0a !important; }
    
    .timeline-block .ratio > video.timeline-preview {
        display: block;
        position: absolute !important;
        inset: 0;
        width: 100% !important;
        height: 100% !important;
        max-width: 100%;
        object-fit: cover;
        z-index: 2;
    }
    .play-video-button { width: 48px; height: 48px; padding: 0; z-index: 3; opacity: .9; }
    .play-video-button:hover { opacity: 1; transform: translate(-50%, -50%) scale(1.08) !important; }
    .timeline-preview { pointer-events: none; }
    .timeline-grid {
        display: grid;
        gap: 24px;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
        align-items: stretch;
    }
    .timeline-empty {
        grid-column: 1 / -1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 360px;
        padding: clamp(24px, 6vw, 64px);
        width: 100%;
    }
    .timeline-empty p { max-width: 620px; line-height: 1.7; }
    .timeline-block { min-width: 0; width: 100%; max-width: 460px; }
    .timeline-block .ratio {
        aspect-ratio: 16 / 9;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        position: relative;
    }
    .timeline-block .ratio::before { padding-top: 56.25%; }

    @media (max-width: 991.98px) {
        .container-fluid > .d-flex.justify-content-between { align-items: flex-start !important; flex-direction: column; gap: 16px; }
        .container-fluid > .d-flex.justify-content-between > .d-flex { width: 100%; flex-wrap: wrap; }
        .container-fluid > .d-flex.justify-content-between > .d-flex > * { flex: 1 1 190px; }
        .timeline-block { max-width: none; }
    }

    @media (max-width: 575.98px) {
        .timeline-grid { grid-template-columns: 1fr; gap: 16px; }
        .timeline-block { padding: 12px !important; }
        h2 { font-size: 1.35rem; overflow-wrap: anywhere; }
        .play-video-button { width: 42px; height: 42px; }
    }

    #timelineVideoModal .modal-dialog { width: min(96vw, 1140px); margin-left: auto; margin-right: auto; }
    #timelineVideoModal .modal-content { width: 100%; overflow: hidden; }
    #timelineVideoModal .ratio { width: 100%; max-height: calc(100vh - 120px); }
    #timelineModalPlayer { max-width: 100%; max-height: calc(100vh - 120px); background: #000; }
    
    /* Sleek Void System Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { height: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #000; border-top: 1px solid #333; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #444; border-radius: 0; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #fff; }
</style>

<div class="modal fade" id="timelineVideoModal" tabindex="-1" aria-labelledby="timelineVideoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content bg-black border border-secondary rounded-0">
            <div class="modal-header border-secondary">
                <h5 class="modal-title small tracking-widest" id="timelineVideoModalLabel">SCENE PLAYER</h5>
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

<script>
document.querySelectorAll('.play-video-button').forEach((button) => {
    button.addEventListener('click', () => {
        const player = document.getElementById('timelineModalPlayer');
        document.getElementById('timelineVideoModalLabel').textContent = button.dataset.sceneLabel + ' / VIDEO PLAYER';
        player.src = button.dataset.videoSrc;
        bootstrap.Modal.getOrCreateInstance(document.getElementById('timelineVideoModal')).show();
        player.play().catch(() => {});
    });
});

document.getElementById('timelineVideoModal').addEventListener('hidden.bs.modal', () => {
    const player = document.getElementById('timelineModalPlayer');
    player.pause();
    player.removeAttribute('src');
    player.load();
});
</script>
@endsection
