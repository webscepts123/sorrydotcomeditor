@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom border-secondary pb-3">
        <h2 class="fw-light tracking-widest" style="font-family: 'Syncopate';">TIMELINE: VOID SHADOW</h2>
        <button class="btn btn-danger rounded-0 px-4 small uppercase tracking-widest" id="render-all">
            RENDER ENTIRE SEQUENCE
        </button>
    </div>

    <div class="d-flex overflow-auto pb-4 gap-3" style="min-height: 400px; scrollbar-width: thin;">
        @foreach($project->scenes as $scene)
        <div class="timeline-block border border-secondary bg-black p-2" style="min-width: 300px;">
            <div class="d-flex justify-content-between small text-secondary mb-2">
                <span>SCENE #{{ $scene->order_index }}</span>
                <span class="text-success">READY</span>
            </div>
            
            <div class="ratio ratio-16x9 bg-dark mb-2 border border-secondary">
                @if($scene->videoClips->first())
                    <video src="{{ asset('storage/' . $scene->videoClips->first()->file_path) }}" muted></video>
                @else
                    <div class="d-flex align-items-center justify-content-center small text-secondary">NO RENDER</div>
                @endif
            </div>

            <p class="small text-secondary italic mb-3" style="font-size: 11px;">
                {{ Str::limit($scene->script_segment, 50) }}
            </p>

            <div class="d-grid">
                <button class="btn btn-outline-light btn-sm rounded-0 uppercase small">Edit Prompt</button>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection