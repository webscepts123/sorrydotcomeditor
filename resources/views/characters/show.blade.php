@extends('layouts.admin')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;

    $hasImage = $character->image_path && Storage::disk('public')->exists($character->image_path);
    $imageUrl = $hasImage ? Storage::url($character->image_path) : null;
@endphp

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

            <a href="{{ route('characters.edit', $character) }}" class="btn btn-outline-light rounded-0 px-4 py-2 small uppercase tracking-widest">
                <i class="bi bi-sliders me-2"></i> EDIT IDENTITY
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-black border border-success text-success rounded-0 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger bg-black border border-danger text-danger rounded-0 mb-4">
            {{ session('error') }}
        </div>
    @endif

    @if($character->image_path && !$hasImage)
        <div class="alert alert-warning bg-black border border-warning text-warning rounded-0 mb-4">
            Image path exists in database, but file was not found in storage:
            <code>{{ $character->image_path }}</code>
            <br>
            Run: <code>php artisan storage:link</code> and confirm file exists in
            <code>storage/app/public/{{ $character->image_path }}</code>
        </div>
    @endif

    <div class="row g-5">
        <div class="col-md-4">
            <div class="border border-secondary bg-black p-3 mb-4">
                <div class="ratio ratio-1x1 bg-dark mb-3 border border-secondary overflow-hidden">
                    @if($hasImage)
                        <img src="{{ $imageUrl }}"
                             alt="{{ $character->name }}"
                             class="w-100 h-100 object-fit-cover"
                             loading="lazy">
                    @else
                        <div class="d-flex flex-column justify-content-center align-items-center text-secondary h-100">
                            <i class="bi bi-person-bounding-box mb-2" style="font-size: 3rem;"></i>
                            <span class="small tracking-widest uppercase" style="font-size: 10px;">NO REFERENCE UPLOADED</span>
                        </div>
                    @endif
                </div>

                @if($hasImage)
                    <div class="small text-secondary font-monospace mb-3 text-break">
                        {{ $imageUrl }}
                    </div>
                @endif

                <label class="text-secondary small tracking-widest uppercase d-block mb-1" style="font-size: 10px;">System AI Seed Tag</label>
                <div class="p-2 border border-secondary bg-dark text-info text-center font-monospace small mb-3">
                    {{ $character->ai_tag ?? 'AWAITING_TAG' }}
                </div>

                <label class="text-secondary small tracking-widest uppercase d-block mb-1" style="font-size: 10px;">Assigned Archetype</label>
                <div class="p-2 border border-secondary bg-dark text-white text-center uppercase tracking-widest small">
                    {{ $character->role ?? 'SUPPORTING' }}
                </div>
            </div>

            <form action="{{ route('characters.generate-image', $character) }}" method="POST">
                @csrf
                <button type="submit"
                    class="btn btn-outline-warning w-100 rounded-0 py-3 small uppercase tracking-widest transition-hover mb-2">
                    <i class="bi bi-magic me-2"></i> GENERATE REAL CHARACTER
                </button>
            </form>

            @if($hasImage)
                <a href="{{ $imageUrl }}"
                   download="{{ Str::slug($character->name) }}-character.png"
                   class="btn btn-outline-success w-100 rounded-0 py-3 small uppercase tracking-widest transition-hover mb-2">
                    <i class="bi bi-download me-2"></i> DOWNLOAD IMAGE
                </a>
            @else
                <button type="button"
                    class="btn btn-outline-secondary w-100 rounded-0 py-3 small uppercase tracking-widest mb-2"
                    disabled>
                    <i class="bi bi-image me-2"></i> NO IMAGE TO DOWNLOAD
                </button>
            @endif
            <form action="{{ route('characters.sync-face', $character) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-info w-100 rounded-0 py-3 small uppercase tracking-widest transition-hover mb-2">
                    <i class="bi bi-person-bounding-box me-2"></i> SEND TO SYNC FACE
                </button>
            </form>

            <form action="{{ route('characters.destroy', $character) }}" method="POST" onsubmit="return confirm('CRITICAL: Purge this character identity?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger w-100 rounded-0 py-3 small uppercase tracking-widest transition-hover">
                    <i class="bi bi-trash3 me-2"></i> TERMINATE IDENTITY
                </button>
            </form>
        </div>

        <div class="col-md-8">
            <div class="bg-black border border-secondary p-5 h-100">

                <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-4">
                    Visual Description
                </h6>
                <p class="text-white italic mb-5" style="line-height: 1.8; font-size: 14px;">
                    {{ $character->description ?? 'No visual description provided for this entity.' }}
                </p>

                <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-4">
                    Personality / Behavior
                </h6>
                <p class="text-white italic mb-5">
                    {{ $character->personality ?? 'No personality defined.' }}
                </p>

                <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-4">
                    Dialogue Style
                </h6>
                <p class="text-white italic mb-5">
                    {{ $character->dialogue_style ?? 'No dialogue style defined.' }}
                </p>

                <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-4">
                    AI Prompt
                </h6>
                <pre class="text-info font-monospace small p-3 border border-secondary bg-dark mb-5" style="white-space: pre-wrap;">{{ $character->prompt ?? 'No prompt generated.' }}</pre>

                <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-4">
                    Confirmed Scene Appearances ({{ $character->scenes->count() }})
                </h6>

                <div class="list-group rounded-0">
                    @forelse($character->scenes as $scene)
                        <a href="{{ route('scenes.edit', $scene) }}"
                           class="list-group-item bg-transparent text-white border border-secondary mb-2 transition-hover p-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-dark border border-secondary text-white rounded-0 me-3 tracking-widest">
                                    SEQ #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}
                                </span>

                                <span class="small italic text-secondary">
                                    "{{ Str::limit($scene->script_segment, 60) }}"
                                </span>
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
    .border-dashed { border-style: dashed !important; }

    .transition-hover {
        transition: all 0.2s ease;
    }

    .transition-hover:hover {
        background: #111 !important;
        border-color: #fff !important;
        cursor: pointer;
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .d-flex.justify-content-between.align-items-end {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 15px;
        }

        .d-flex.gap-3 {
            flex-direction: column;
            width: 100%;
        }

        .d-flex.gap-3 .btn {
            width: 100%;
        }

        .bg-black.border.border-secondary.p-5 {
            padding: 25px !important;
        }
    }
</style>
@endsection