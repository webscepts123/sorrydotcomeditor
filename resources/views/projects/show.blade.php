@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <div class="d-flex justify-content-between align-items-end">
            <div>
                <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">PROJECT / {{ $project->status ?? 'DRAFT' }}</h6>
                <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">{{ strtoupper($project->title) }}</h2>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('projects.editor', $project) }}" class="btn btn-white bg-white text-black rounded-0 px-4 small fw-bold tracking-widest">OPEN AI EDITOR</a>
                <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-secondary rounded-0 px-4 small tracking-widest">CONFIG</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 border-end border-secondary pe-4">
            <div class="mb-5">
                <label class="text-secondary small tracking-widest uppercase mb-2 d-block">Aspect Ratio</label>
                <h5 class="fw-bold">{{ $project->aspect_ratio ?? '16:9' }}</h5>
            </div>

            <div class="mb-5">
                <label class="text-secondary small tracking-widest uppercase mb-2 d-block">Style Preset</label>
                <h5 class="fw-bold text-info">{{ strtoupper($project->style_preset ?? 'CINEMATIC_DARK_THRILLER') }}</h5>
            </div>

            <a href="{{ route('projects.editor', $project) }}" class="btn btn-outline-light rounded-0 w-100 py-3 small tracking-widest uppercase">
                ADD NEW SCENE +
            </a>
        </div>

        <div class="col-md-9 ps-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="tracking-widest fw-light uppercase">Production Timeline</h5>
                <a href="{{ route('projects.timeline', $project) }}" class="text-secondary small text-decoration-none border-bottom border-secondary">VIEW FULL TIMELINE</a>
            </div>
            
            @forelse($project->scenes->sortBy('order_index') as $scene)
                <div class="mb-3 p-4 border border-secondary bg-black d-flex justify-content-between align-items-center transition-hover">
                    <div>
                        <span class="text-secondary small me-3">#{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-white">{{ Str::limit($scene->script_segment, 80) }}</span>
                    </div>
                    <a href="{{ route('projects.editor', $project) }}" class="text-info small text-decoration-none">EDIT CLIP</a>
                </div>
            @empty
                <div class="py-5 text-center border border-dashed border-secondary">
                    <p class="text-secondary tracking-widest mb-0 uppercase" style="font-size: 11px;">No scenes initialized for this timeline.</p>
                </div>
            @endforelse 
        </div>
    </div>
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 3px; }
    .transition-hover:hover { border-color: #fff !important; cursor: pointer; }
</style>
@endsection