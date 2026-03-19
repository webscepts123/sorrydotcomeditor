@extends('layouts.admin')

@section('content')
<div class="container-fluid bg-black text-white p-0" style="min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-secondary bg-dark">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 fw-light tracking-widest" style="font-family: 'Syncopate';">VOID SHADOW EDITOR <span class="text-secondary small ms-2">v1.0</span></h5>
            <span class="badge bg-danger ms-3 rounded-0 small tracking-widest uppercase">LIVE: {{ $project->title }}</span>
        </div>
        <div class="d-flex gap-3">
            <button class="btn btn-outline-secondary btn-sm rounded-0 tracking-widest uppercase">Export XML</button>
            <button id="render-batch" class="btn btn-white bg-white text-black btn-sm rounded-0 fw-bold tracking-widest px-4">BATCH RENDER (ALL SCENES)</button>
        </div>
    </div>

    <div class="row g-0" style="height: calc(100vh - 70px);">
        
        <div class="col-md-2 border-end border-secondary p-3 overflow-auto">
            <h6 class="text-secondary small tracking-widest mb-4 uppercase">Cast & Seeds</h6>
            @foreach($project->characters as $char)
            <div class="p-2 border border-secondary mb-2 bg-dark d-flex align-items-center">
                <div class="bg-secondary rounded-circle me-2" style="width: 10px; height: 10px;"></div>
                <span class="small uppercase fw-bold">{{ $char->name }}</span>
                <code class="ms-auto text-info" style="font-size: 10px;">{{ $char->ai_tag }}</code>
            </div>
            @endforeach
            
            <hr class="border-secondary my-4">
            
            <h6 class="text-secondary small tracking-widest mb-3 uppercase">Style DNA</h6>
            <div class="small p-2 bg-dark border border-secondary text-secondary italic uppercase tracking-widest" style="font-size: 11px;">
                {{ $project->style_preset }} <br>
                <span style="font-size: 10px;">ASPECT: {{ $project->aspect_ratio }}</span>
            </div>
        </div>

        <div class="col-md-7 p-4 overflow-auto border-end border-secondary">
            <h6 class="text-secondary small tracking-widest mb-4 uppercase">Production Timeline ({{ $project->scenes->count() }} Segments)</h6>
            
            @foreach($project->scenes->sortBy('order_index') as $scene)
            <div class="scene-row mb-4 p-3 border border-secondary bg-black transition-hover shadow-sm">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="text-secondary small fw-bold tracking-widest">SCENE #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}</span>
                        @if($scene->status == 'Processing')
                            <span class="ms-3 badge border border-warning text-warning small uppercase rounded-0">Processing <span class="spinner-grow spinner-grow-sm ms-1" style="width: 0.5rem; height: 0.5rem;"></span></span>
                        @else
                            <span class="ms-3 badge border border-secondary small text-secondary uppercase rounded-0">{{ $scene->status ?? 'Draft' }}</span>
                        @endif
                    </div>
                    
                    <div class="dropdown">
                        <button class="btn btn-link text-white p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-dark rounded-0 border-secondary shadow-lg">
                        <li>
                            <a class="dropdown-item small text-info uppercase tracking-widest py-2" href="{{ route('projects.videoeditor', $project->id) }}?active_scene={{ $scene->id }}">
                                <i class="bi bi-film me-2"></i> Open Video Editor
                            </a>
                        </li>
                            
                            <li>
                                <a class="dropdown-item small text-white uppercase tracking-widest py-2" href="{{ route('scenes.edit', $scene) }}">
                                    <i class="bi bi-sliders me-2"></i> Reconfigure Params
                                </a>
                            </li>
                            
                            <li><hr class="dropdown-divider border-secondary"></li>
                            
                            <li>
                                <form action="{{ route('scenes.render', $scene) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item small text-warning uppercase tracking-widest py-2 bg-transparent border-0 w-100 text-start">
                                        <i class="bi bi-cpu-fill me-2"></i> Regenerate Seed
                                    </button>
                                </form>
                            </li>
                            
                            <li>
                                <form action="{{ route('scenes.destroy', $scene) }}" method="POST" onsubmit="return confirm('CRITICAL: Permanently purge this sequence from the timeline?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item small text-danger uppercase tracking-widest py-2 bg-transparent border-0 w-100 text-start">
                                        <i class="bi bi-trash3 me-2"></i> Delete Segment
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4 d-flex flex-column">
                        <div class="ratio ratio-16x9 bg-dark border border-secondary d-flex align-items-center justify-content-center overflow-hidden mb-2">
                            @if($scene->video_path)
                                <video src="{{ asset('storage/'.$scene->video_path) }}" controls class="w-100"></video>
                            @else
                                <div class="text-center">
                                    <i class="bi bi-play-circle text-secondary fs-3"></i>
                                    <p class="text-secondary mb-0 mt-2 tracking-widest uppercase" style="font-size: 10px;">AWAITING RENDER</p>
                                </div>
                            @endif
                        </div>
                        
                        <a href="{{ route('projects.editor', $project->id) }}?active_scene={{ $scene->id }}" class="btn btn-outline-secondary btn-sm w-100 rounded-0 uppercase tracking-widest mt-auto" style="font-size: 10px;">
                            <i class="bi bi-film me-1"></i> ADVANCED VIDEO EDIT
                        </a>
                    </div>
                    
                    <div class="col-md-8">
                        <label class="text-secondary small tracking-widest mb-1 uppercase" style="font-size: 10px;">Script / AI Prompt</label>
                        
                        <form action="{{ route('scenes.update', $scene) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="project_id" value="{{ $project->id }}">
                            <input type="hidden" name="order_index" value="{{ $scene->order_index }}">
                            <input type="hidden" name="status" value="{{ $scene->status }}">

                            <textarea name="script_segment" class="form-control bg-transparent border-secondary text-info font-monospace small rounded-0 p-3 mb-2" rows="3" placeholder="Describe the movement and action here...">{{ $scene->script_segment }}</textarea>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-light btn-sm rounded-0 px-4 uppercase small tracking-widest">SAVE</button>
                                <button type="button" class="btn btn-dark btn-sm rounded-0 border-secondary small flex-grow-1 uppercase tracking-widest">Preview Prompt</button>
                            </div>
                        </form>

                        <form action="{{ route('scenes.render', $scene) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-info w-100 btn-sm rounded-0 small uppercase tracking-widest fw-bold py-2">
                                <i class="bi bi-cpu-fill me-1"></i> RENDER CLIP
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="col-md-3 bg-dark p-3">
            <h6 class="text-secondary small tracking-widest mb-4 uppercase">System Queue</h6>
            <div id="render-queue" class="p-3 border border-secondary bg-black mb-4 d-flex align-items-center justify-content-center" style="min-height: 200px;">
                <div class="text-center text-secondary small italic">
                    <div class="spinner-border spinner-border-sm mb-2 text-secondary" role="status"></div>
                    <br>Engine Idle.
                </div>
            </div>

            <h6 class="text-secondary small tracking-widest mb-3 uppercase">Master Audio Layer</h6>
            <div class="p-3 border border-secondary bg-black d-flex align-items-center gap-2">
                <i class="bi bi-waveform text-info fs-5"></i>
                <div>
                    <span class="small d-block fw-bold tracking-widest uppercase" style="font-size: 10px;">Main_Score_Noir.wav</span>
                    <span class="small text-secondary font-monospace" style="font-size: 10px;">DURATION: 2:30:00</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .transition-hover:hover { border-color: #555 !important; background: #050505 !important; }
    .uppercase { text-transform: uppercase; }
    .italic { font-style: italic; }
    .tracking-widest { letter-spacing: 0.15em; }
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-thumb { background: #333; }
    .dropdown-item:hover { background-color: #fff !important; color: #000 !important; }
</style>
@endsection