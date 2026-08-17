@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">PRODUCTION / CAST</h6>
            <h2 class="fw-light mb-0" style="font-family: 'Syncopate'; letter-spacing: 4px;">CHARACTERS</h2>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button type="button"
                    class="btn btn-outline-light rounded-0 px-4 py-2 small tracking-widest uppercase"
                    data-bs-toggle="modal"
                    data-bs-target="#importCharactersModal">
                <i class="bi bi-upload me-1"></i> IMPORT
            </button>

            <a href="{{ route('characters.export-all') }}" class="btn btn-outline-light rounded-0 px-4 py-2 small tracking-widest uppercase">
                <i class="bi bi-download me-1"></i> EXPORT ALL
            </a>

            <a href="{{ route('characters.create') }}" class="btn btn-white bg-white text-black rounded-0 px-4 py-2 fw-bold small tracking-widest uppercase">
                NEW CHARACTER +
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

    <div class="row g-4">
        @forelse($characters as $character)
            <div class="col-md-3">
                <div class="card bg-black border border-secondary text-white rounded-0 shadow-sm h-100 character-card">
                    <div class="ratio ratio-1x1 bg-dark border-bottom border-secondary d-flex align-items-center justify-content-center">
                        @if($character->image_path)
                            <img src="{{ asset('storage/' . $character->image_path) }}"
                                 class="card-img-top rounded-0 w-100 h-100"
                                 style="object-fit: cover;"
                                 alt="{{ $character->name }}">
                        @else
                            <div class="d-flex flex-column align-items-center justify-content-center text-secondary h-100">
                                <i class="bi bi-person-bounding-box mb-2" style="font-size: 2.5rem;"></i>
                                <span class="small tracking-widest uppercase">NO REFERENCE</span>
                            </div>
                        @endif
                    </div>

                    <div class="card-body p-3">
                        <h5 class="fw-bold mb-1">{{ strtoupper($character->name) }}</h5>

                        <code class="text-info small d-block mb-2">
                            {{ $character->ai_tag ?? '@actor_seed_01' }}
                        </code>

                        <div class="mb-2">
                            <span class="badge bg-dark border border-secondary text-white rounded-0 small tracking-widest uppercase">
                                {{ $character->role ?? 'Protagonist' }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <small class="text-secondary tracking-widest uppercase d-block mb-1" style="font-size: 9px;">
                                Project
                            </small>
                            <span class="text-white small">
                                {{ $character->project->title ?? 'Unassigned' }}
                            </span>
                        </div>

                        @if($character->description)
                            <p class="text-secondary small mb-3" style="line-height: 1.6;">
                                {{ Str::limit($character->description, 85) }}
                            </p>
                        @else
                            <p class="text-secondary small mb-3 fst-italic">
                                No character description added.
                            </p>
                        @endif

                        <hr class="border-secondary my-2">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-secondary">
                                {{ $character->created_at ? $character->created_at->format('M d, Y') : 'Draft' }}
                            </small>

                            <a href="{{ route('characters.show', $character) }}"
                               class="text-white small text-decoration-none tracking-widest uppercase">
                                DETAILS
                            </a>
                        </div>

                        @if($character->image_path)
                            <a href="{{ asset('storage/' . $character->image_path) }}"
                               download="{{ Str::slug($character->name) }}-character-image"
                               class="btn btn-outline-info w-100 rounded-0 small tracking-widest uppercase mb-2">
                                <i class="bi bi-download me-1"></i> Download Image
                            </a>
                        @else
                            <button type="button"
                                    class="btn btn-outline-secondary w-100 rounded-0 small tracking-widest uppercase mb-2"
                                    disabled>
                                <i class="bi bi-image me-1"></i> No Image
                            </button>
                        @endif

                        <a href="{{ route('characters.export', $character) }}"
                           class="btn btn-outline-light w-100 rounded-0 small tracking-widest uppercase">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Export JSON
                        </a>

                        <form action="{{ route('characters.transfer', $character) }}" method="POST" class="mt-2">
                            @csrf

                            <div class="input-group input-group-sm transfer-control">
                                <select name="project_id"
                                        class="form-select bg-black border-info text-white rounded-0 small tracking-widest uppercase"
                                        required>
                                    <option value="">Move to project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}"
                                            {{ $character->project_id == $project->id ? 'disabled' : '' }}>
                                            {{ $project->title }}
                                        </option>
                                    @endforeach
                                </select>

                                <button type="submit"
                                        class="btn btn-outline-info rounded-0 small tracking-widest uppercase"
                                        title="Transfer character to selected project">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 border border-secondary border-dashed">
                <i class="bi bi-person-plus text-secondary mb-3" style="font-size: 3rem;"></i>

                <p class="text-secondary tracking-widest uppercase mb-3">
                    THE CAST IS EMPTY. INITIALIZE CHARACTERS FOR VOID SHADOW.
                </p>

                <a href="{{ route('characters.create') }}" class="btn btn-outline-light rounded-0 px-4 py-2 small tracking-widest uppercase">
                    Create First Character
                </a>
            </div>
        @endforelse
    </div>

    @if(method_exists($characters, 'links'))
        <div class="mt-5">
            {{ $characters->links() }}
        </div>
    @endif
</div>

<div class="modal fade" id="importCharactersModal" tabindex="-1" aria-labelledby="importCharactersModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-black border border-secondary rounded-0 text-white">
            <form action="{{ route('characters.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title tracking-widest uppercase" id="importCharactersModalLabel" style="font-size: 14px;">
                        Import Characters
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small mb-4">
                        Upload a character export file (<code>.json</code>) generated from Export All or Export JSON. Accepts a single character or a full roster export.
                    </p>

                    <div class="mb-3">
                        <label class="text-secondary small tracking-widest uppercase d-block mb-1" style="font-size: 10px;">
                            Destination Project
                        </label>
                        <select name="project_id" class="form-select bg-black border-secondary text-white rounded-0" required>
                            <option value="">Select a project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="text-secondary small tracking-widest uppercase d-block mb-1" style="font-size: 10px;">
                            Export File
                        </label>
                        <input type="file" name="import_file" accept=".json,application/json" class="form-control bg-black border-secondary text-white rounded-0" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary rounded-0 small tracking-widest uppercase" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-white bg-white text-black rounded-0 fw-bold small tracking-widest uppercase">
                        Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .uppercase {
        text-transform: uppercase;
    }

    .tracking-widest {
        letter-spacing: 0.15em;
    }

    .border-dashed {
        border-style: dashed !important;
    }

    .character-card {
        transition: all 0.25s ease;
    }

    .character-card:hover {
        border-color: #fff !important;
        transform: translateY(-4px);
    }

    .card-img-top {
        object-fit: cover;
    }

    .btn-outline-info,
    .btn-outline-secondary,
    .transfer-control .form-select {
        font-size: 10px;
        letter-spacing: 0.12em;
    }

    .transfer-control .form-select:focus {
        background-color: #000;
        border-color: #0dcaf0;
        box-shadow: none;
        color: #fff;
    }

    .transfer-control option {
        background: #000;
        color: #fff;
    }

    .transfer-control .btn {
        width: auto;
        margin-top: 0;
    }

    @media (max-width: 768px) {
        .d-flex.justify-content-between.align-items-end {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 20px;
        }

        .btn {
            width: 100%;
        }
    }
</style>
@endsection
