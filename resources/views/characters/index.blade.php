@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">PRODUCTION / CAST</h6>
            <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">CHARACTERS</h2>
        </div>
        <a href="{{ route('characters.create') }}" class="btn btn-white bg-white text-black rounded-0 px-4 fw-bold small">
            NEW CHARACTER +
        </a>
    </div>

    <div class="row g-4">
        @forelse($characters as $character)
        <div class="col-md-3">
            <div class="card bg-black border border-secondary text-white rounded-0 shadow-sm">
                <div class="ratio ratio-1x1 bg-dark border-bottom border-secondary d-flex align-items-center justify-content-center">
                    @if($character->image_path)
                        <img src="{{ asset('storage/' . $character->image_path) }}" class="card-img-top rounded-0" style="object-fit: cover;">
                    @else
                        <span class="text-secondary small tracking-widest">NO REFERENCE</span>
                    @endif
                </div>
                
                <div class="card-body p-3">
                    <h5 class="fw-bold mb-1">{{ strtoupper($character->name) }}</h5>
                    <code class="text-info small">{{ $character->ai_tag ?? '@actor_seed_01' }}</code>
                    <hr class="border-secondary my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-secondary">{{ $character->role ?? 'Protagonist' }}</small>
                        <a href="{{ route('characters.show', $character) }}" class="text-white small text-decoration-none">DETAILS</a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5 border border-secondary border-dashed">
            <p class="text-secondary tracking-widest">THE CAST IS EMPTY. INITIALIZE CHARACTERS FOR VOID SHADOW.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection