@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5 border-bottom border-secondary pb-4">
        <div>
            <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">AUDIO / SCORE</h6>
            <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">MASTER SOUNDTRACKS</h2>
        </div>
        <a href="{{ route('soundtracks.create') }}" class="btn btn-white bg-white text-black rounded-0 px-4 py-2 fw-bold small tracking-widest uppercase transition-hover">
            UPLOAD AUDIO +
        </a>
    </div>

    <div class="row mb-5 g-4">
        <div class="col-md-4">
            <div class="p-4 border border-secondary bg-black">
                <h6 class="text-secondary small tracking-widest uppercase mb-2">Total Project Runtime</h6>
                <h3 class="fw-light text-white mb-0">--:--:--</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 border border-secondary bg-black">
                <h6 class="text-secondary small tracking-widest uppercase mb-2">Active Stems</h6>
                <h3 class="fw-light text-info mb-0">{{ $tracks->count() }} TRACKS</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="p-4 border border-secondary bg-black d-flex align-items-center justify-content-center">
                <i class="bi bi-soundwave text-secondary fs-1 opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="bg-black border border-secondary p-3">
        <div class="list-group list-group-flush rounded-0">
            
            @forelse($tracks as $track)
            <div class="list-group-item bg-transparent text-white border-bottom border-secondary p-4 transition-row">
                <div class="row align-items-center">
                    
                    <div class="col-md-1 text-center">
                        <button class="btn btn-outline-light rounded-circle p-2 play-btn" style="width: 40px; height: 40px;" data-file="{{ asset('storage/' . $track->file_path) }}">
                            <i class="bi bi-play-fill"></i>
                        </button>
                    </div>
                    
                    <div class="col-md-4">
                        <h6 class="mb-1 fw-bold tracking-widest">{{ strtoupper($track->title) }}</h6>
                        <span class="text-secondary small uppercase" style="font-size: 10px;">Composers: {{ $track->composer ?? 'Unknown' }}</span>
                    </div>
                    
                    <div class="col-md-3">
                        <span class="badge border border-{{ $track->type == 'cinematic' ? 'info' : 'secondary' }} text-{{ $track->type == 'cinematic' ? 'info' : 'secondary' }} rounded-0 small uppercase tracking-widest" style="font-size: 9px;">
                            {{ strtoupper($track->type) }}
                        </span>
                    </div>
                    
                    <div class="col-md-2 text-secondary small font-monospace">
                        --:--
                    </div>
                    <a href="{{ route('soundtracks.edit', $track) }}" class="btn btn-link text-secondary hover-white p-0 me-2" title="Settings">
                        <i class="bi bi-sliders"></i>
                    </a>
                    <div class="col-md-2 text-end d-flex justify-content-end gap-2">
                        <button class="btn btn-link text-secondary hover-white p-0 me-2" title="Settings"><i class="bi bi-sliders"></i></button>
                        
                        <form action="{{ route('soundtracks.destroy', $track) }}" method="POST" onsubmit="return confirm('CRITICAL: Purge this audio stem? This deletes the file from the server.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link text-danger hover-white p-0">
                                <i class="bi bi-trash3 fs-5"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 border border-dashed border-secondary mx-3 my-2">
                <p class="text-secondary tracking-widest uppercase mb-0" style="font-size: 11px;">NO AUDIO STEMS DETECTED IN STORAGE.</p>
            </div>
            @endforelse

        </div>
    </div>
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.2em; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    .transition-hover:hover { background: #ccc !important; transform: scale(1.02); }
    .transition-row:hover { background-color: #0a0a0a !important; border-left: 2px solid #fff; }
    .hover-white:hover { color: #fff !important; transition: 0.3s; }
    .play-btn:hover { background: #fff; color: #000; }
</style>
@endsection