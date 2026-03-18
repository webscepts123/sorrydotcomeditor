@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom border-secondary pb-3 mt-2">
        <div>
            <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">HORIZONTAL SEQUENCE</h6>
            <h2 class="fw-light tracking-widest mb-0" style="font-family: 'Syncopate';">{{ strtoupper($project->title) }}</h2>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('scenes.create', ['project' => $project->id]) }}" class="btn btn-outline-light rounded-0 px-4 small uppercase tracking-widest">ADD SCENE +</a>
            <button class="btn btn-white bg-white text-black rounded-0 px-4 fw-bold small uppercase tracking-widest transition-hover" id="render-all">
                <i class="bi bi-cpu me-2"></i> BATCH RENDER ALL
            </button>
        </div>
    </div>

    <div class="d-flex overflow-auto pb-4 gap-4 px-2 custom-scrollbar" style="min-height: 450px;">
        @forelse($project->scenes as $scene)
        <div class="timeline-block bg-black border border-secondary p-3 d-flex flex-column transition-hover shadow-sm" style="min-width: 320px; max-width: 320px;">
            
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
                    <video src="{{ asset('storage/' . $scene->video_path) }}" class="w-100 object-fit-cover" muted></video>
                    <div class="position-absolute top-50 start-50 translate-middle play-overlay">
                        <i class="bi bi-play-circle-fill text-white fs-1 opacity-75"></i>
                    </div>
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
            </div>

            <div class="mt-auto border-top border-secondary pt-3 d-flex justify-content-between align-items-center gap-2">
                <a href="{{ route('scenes.edit', $scene) }}" class="btn btn-sm btn-outline-light rounded-0 uppercase tracking-widest flex-grow-1" style="font-size: 10px;">
                    <i class="bi bi-sliders me-1"></i> CONFIG
                </a>
                
                <div class="dropdown flex-grow-1">
                    <button class="btn btn-sm btn-dark border-secondary rounded-0 uppercase tracking-widest w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size: 10px;">
                        OPTIONS
                    </button>
                    <ul class="dropdown-menu dropdown-menu-dark rounded-0 border-secondary shadow-lg mt-1">
                        <li><a class="dropdown-item small tracking-widest uppercase text-info py-2" style="font-size: 10px;" href="#"><i class="bi bi-cpu me-2 fs-6 align-middle"></i> Render Single</a></li>
                        <li><a class="dropdown-item small tracking-widest uppercase py-2" style="font-size: 10px;" href="#"><i class="bi bi-arrow-repeat me-2 fs-6 align-middle"></i> Swap Seed</a></li>
                        <li><hr class="dropdown-divider border-secondary"></li>
                        <li>
                            <form action="{{ route('scenes.destroy', $scene) }}" method="POST" onsubmit="return confirm('CRITICAL: Purge this sequence and its generated assets?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item small tracking-widest uppercase text-danger py-2" style="font-size: 10px;">
                                    <i class="bi bi-trash3 me-2 fs-6 align-middle"></i> Delete
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
        @empty
        <div class="w-100 d-flex justify-content-center align-items-center border border-dashed border-secondary text-secondary p-5 tracking-widest uppercase small bg-black">
            Timeline uninitialized. Add scenes to begin sequencing.
        </div>
        @endforelse
    </div>
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.15em; }
    .italic { font-style: italic; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    
    /* Cinematic Hover Effects */
    .transition-hover { transition: all 0.3s ease; }
    .transition-hover:hover { border-color: #fff !important; transform: translateY(-4px); background-color: #0a0a0a !important; }
    
    /* Play Button Reveal on Hover */
    .group-hover:hover .play-overlay { opacity: 1 !important; transition: 0.2s; cursor: pointer; }
    .play-overlay { opacity: 0; transition: 0.2s; }
    
    /* Sleek Void System Scrollbar */
    .custom-scrollbar::-webkit-scrollbar { height: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #000; border-top: 1px solid #333; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #444; border-radius: 0; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #fff; }
</style>
@endsection