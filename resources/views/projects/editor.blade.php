@extends('layouts.admin')

@section('content')
<div class="container-fluid bg-black text-white p-0" style="min-height: 100vh;">
    <div class="d-flex justify-content-between align-items-center p-3 border-bottom border-secondary bg-dark">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 fw-light tracking-widest" style="font-family: 'Syncopate';">SORRYDOTCOM EDITOR <span class="text-secondary small ms-2">v1.0</span></h5>
            <span class="badge bg-danger ms-3 rounded-0 small">LIVE: {{ strtoupper($project->title) }}</span>
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
                <span class="small">{{ strtoupper($char->name) }}</span>
                <code class="ms-auto text-info" style="font-size: 10px;">{{ $char->ai_tag }}</code>
            </div>
            @endforeach
            
            <hr class="border-secondary my-4">
            
            <h6 class="text-secondary small tracking-widest mb-3 uppercase">Style DNA</h6>
            <div class="small p-2 bg-dark border border-secondary text-secondary italic">
                {{ strtoupper($project->style_preset) }} <br>
                <span style="font-size: 10px;">ASPECT: {{ $project->aspect_ratio }}</span>
            </div>
        </div>

        <div class="col-md-7 p-4 overflow-auto border-end border-secondary">
            <h6 class="text-secondary small tracking-widest mb-4 uppercase">Production Timeline ({{ $project->scenes->count() }} Segments)</h6>
            
            @foreach($project->scenes->sortBy('order_index') as $scene)
            <div class="scene-row mb-4 p-3 border border-secondary bg-black transition-hover">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="text-secondary small fw-bold">SCENE #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}</span>
                        <span class="ms-3 badge border border-secondary small text-secondary uppercase">{{ $scene->status ?? 'Draft' }}</span>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-link text-white p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li><a class="dropdown-item small text-info" href="{{ route('scenes.edit', $scene) }}">Open Video Editor</a></li>
                            <li><a class="dropdown-item small" href="#">Regenerate Seed</a></li>
                            <li><a class="dropdown-item small text-danger" href="#">Delete Segment</a></li>
                        </ul>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="ratio ratio-16x9 bg-dark border border-secondary d-flex align-items-center justify-content-center overflow-hidden">
                            @if($scene->video_path)
                                <video src="{{ asset('storage/'.$scene->video_path) }}" controls class="w-100"></video>
                            @else
                                <div class="text-center">
                                    <i class="bi bi-play-circle text-secondary fs-3"></i>
                                    <p class="text-secondary mb-0 mt-2" style="font-size: 10px;">AWAITING RENDER</p>
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('scenes.edit', $scene) }}" class="btn btn-outline-secondary btn-sm w-100 mt-2 rounded-0 uppercase tracking-widest" style="font-size: 10px;">
                            <i class="bi bi-film me-1"></i> ADVANCED VIDEO EDIT
                        </a>
                    </div>
                    <div class="col-md-8">
                        <label class="text-secondary small tracking-widest mb-1 uppercase" style="font-size: 10px;">Script / AI Prompt</label>
                        <form action="{{ route('scenes.update', $scene) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <textarea name="script_segment" class="form-control bg-transparent border-secondary text-white small rounded-0" rows="3" 
                                      placeholder="Describe the movement and action here...">{{ $scene->script_segment }}</textarea>
                            <div class="d-flex gap-2 mt-2">
                                <button type="submit" class="btn btn-outline-light btn-sm rounded-0 px-3 uppercase small">SAVE</button>
                                
                                <button type="button" class="btn btn-dark btn-sm rounded-0 border-secondary small flex-grow-1 uppercase">Preview Prompt</button>
                                <button type="button" class="btn btn-outline-info btn-sm rounded-0 small px-3"><i class="bi bi-cpu me-1"></i> RENDER CLIP</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="col-md-3 bg-dark p-3">
            <h6 class="text-secondary small tracking-widest mb-4 uppercase">System Queue</h6>
            <div id="render-queue" class="p-3 border border-secondary bg-black mb-4" style="min-height: 200px;">
                <div class="d-flex align-items-center text-secondary small mb-2 italic">
                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                    Engine Idle. Awaiting Instructions...
                </div>
            </div>

            <h6 class="text-secondary small tracking-widest mb-3 uppercase">Master Audio Layer</h6>
            <div class="p-2 border border-secondary bg-black d-flex align-items-center gap-2">
                <i class="bi bi-waveform text-info"></i>
                <span class="small">Main_Score_Noir.wav</span>
                <span class="ms-auto small text-secondary">2:30:00</span>
            </div>
        </div>
    </div>
</div>

<style>
    .transition-hover:hover { border-color: #fff !important; background: #0a0a0a !important; }
    .uppercase { text-transform: uppercase; }
    .italic { font-style: italic; }
    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-thumb { background: #333; }
</style>
@endsection