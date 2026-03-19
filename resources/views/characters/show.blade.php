@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5 border-bottom border-secondary pb-4">
        <div>
            <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">PRODUCTION / CASTING / DOSSIER</h6>
            <h2 class="fw-light mb-0" style="font-family: 'Syncopate'; letter-spacing: 4px;">{{ strtoupper($character->name) }}</h2>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('characters.index') }}" class="btn btn-outline-secondary rounded-0 px-4 py-2 small uppercase tracking-widest">
                <i class="bi bi-arrow-left me-2"></i> BACK TO ROSTER
            </a>
            <a href="#" class="btn btn-outline-light rounded-0 px-4 py-2 small uppercase tracking-widest">
                <i class="bi bi-sliders me-2"></i> EDIT IDENTITY
            </a>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-md-4">
            <div class="border border-secondary bg-black p-3 mb-4">
                <div class="ratio ratio-1x1 bg-dark mb-3 border border-secondary">
                    @if($character->reference_image)
                        <img src="{{ asset('storage/' . $character->reference_image) }}" alt="{{ $character->name }}" class="w-100 h-100 object-fit-cover">
                    @else
                        <div class="d-flex flex-column justify-content-center align-items-center text-secondary h-100">
                            <i class="bi bi-person-bounding-box mb-2" style="font-size: 3rem;"></i>
                            <span class="small tracking-widest uppercase" style="font-size: 10px;">NO REFERENCE UPLOADED</span>
                        </div>
                    @endif
                </div>
                
                <label class="text-secondary small tracking-widest uppercase d-block mb-1" style="font-size: 10px;">System AI Seed Tag</label>
                <div class="p-2 border border-secondary bg-dark text-info text-center font-monospace small mb-3">
                    {{ $character->ai_tag ?? 'AWAITING_TAG' }}
                </div>

                <label class="text-secondary small tracking-widest uppercase d-block mb-1" style="font-size: 10px;">Assigned Archetype</label>
                <div class="p-2 border border-secondary bg-dark text-white text-center uppercase tracking-widest small">
                    {{ $character->role ?? 'SUPPORTING' }}
                </div>
            </div>
            
            <button class="btn btn-outline-info w-100 rounded-0 py-3 small uppercase tracking-widest transition-hover mb-2">
                <i class="bi bi-person-bounding-box me-2"></i> SEND TO SYNC FACE
            </button>
            <form action="#" method="POST" onsubmit="return confirm('CRITICAL: Purge this character identity?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100 rounded-0 py-3 small uppercase tracking-widest transition-hover">
                    <i class="bi bi-trash3 me-2"></i> TERMINATE IDENTITY
                </button>
            </form>
        </div>

        <div class="col-md-8">
            <div class="bg-black border border-secondary p-5 h-100">
                
                <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-4">Visual & Prompt Description</h6>
                <p class="text-white italic mb-5" style="line-height: 1.8; font-size: 14px;">
                    {{ $character->description ?? 'No visual description provided for this entity.' }}
                </p>

                <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-4">Confirmed Scene Appearances ({{ $character->scenes->count() }})</h6>
                
                <div class="list-group rounded-0">
                    @forelse($character->scenes as $scene)
                        <a href="{{ route('scenes.edit', $scene) }}" class="list-group-item bg-transparent text-white border border-secondary mb-2 transition-hover p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-dark border border-secondary text-white rounded-0 me-3 tracking-widest">SEQ #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}</span>
                                <span class="small italic text-secondary">"{{ Str::limit($scene->script_segment, 60) }}"</span>
                            </div>
                            <i class="bi bi-chevron-right text-secondary"></i>
                        </a>
                    @empty
                        <div class="p-4 border border-dashed border-secondary text-center text-secondary small tracking-widest uppercase">
                            Character has not been cast in any scenes yet.
                        </div>
                    @endforelse
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
    .object-fit-cover { object-fit: cover; }
    .transition-hover:hover { background: #111 !important; border-color: #fff !important; cursor: pointer; transform: translateY(-2px); transition: all 0.2s ease; }
</style>
@endsection