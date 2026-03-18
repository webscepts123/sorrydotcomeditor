@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">STUDIO / ARCHIVE</h6>
            <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">PROJECTS</h2>
        </div>
        <a href="{{ route('projects.create') }}" class="btn btn-outline-light rounded-0 px-4 tracking-widest small">
            INITIALIZE NEW +
        </a>
    </div>

    <div class="row g-4">
        @forelse($projects as $project)
        <div class="col-md-4">
            <div class="card bg-black border border-secondary text-white rounded-0 transition-hover h-100">
                <div class="card-body p-4 d-flex flex-column">
                    
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <span class="badge border border-secondary text-secondary rounded-0 small uppercase tracking-widest">
                            {{ $project->status ?? 'DRAFT' }}
                        </span>
                        <small class="text-secondary tracking-widest">{{ $project->created_at->format('Y.m.d') }}</small>
                    </div>
                    
                    <h4 class="fw-bold mb-2 tracking-tighter">{{ strtoupper($project->title) }}</h4>
                    <p class="text-secondary small mb-5 flex-grow-1" style="height: 45px; overflow: hidden; line-height: 1.6;">
                        {{ Str::limit($project->description, 90, '...') }}
                    </p>

                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top border-dark">
                        <div class="d-flex gap-3">
                            <a href="{{ route('projects.show', $project) }}" class="text-white text-decoration-none small tracking-widest border-bottom border-white pb-1 hover-gray">
                                VIEW DATA
                            </a>
                            <a href="{{ route('projects.edit', $project) }}" class="text-secondary text-decoration-none small tracking-widest border-bottom border-secondary pb-1 hover-white">
                                EDIT
                            </a>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <div class="text-secondary small me-1 uppercase" style="font-size: 10px; letter-spacing: 1px;">
                                {{ $project->scenes_count ?? 0 }} SCENES
                            </div>

                            <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('CRITICAL: Purging this project will delete all associated scenes, AI prompt data, and assets from the Void System. Proceed?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link p-0 text-danger text-decoration-none small hover-white transition-03" title="PURGE PROJECT">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 border border-dashed border-secondary">
            <p class="text-secondary tracking-widest uppercase">No active productions found in Void System.</p>
        </div>
        @endforelse 
    </div>
</div>

<style>
    /* Cinematic UI Enhancements */
    .transition-hover { 
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); 
    }
    
    .transition-hover:hover { 
        border-color: #fff !important; 
        transform: translateY(-8px); 
        background: #0a0a0a !important; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    .uppercase { text-transform: uppercase; }
    
    .hover-gray:hover { color: #999 !important; border-bottom-color: #999 !important; }
    .hover-white:hover { color: #fff !important; border-bottom-color: #fff !important; }
    
    .transition-03 { transition: 0.3s; }
    
    /* Custom Scrollbar for the Void look */
    ::-webkit-scrollbar { width: 4px; }
    ::-webkit-scrollbar-track { background: #000; }
    ::-webkit-scrollbar-thumb { background: #333; }
    ::-webkit-scrollbar-thumb:hover { background: #555; }
</style>
@endsection