@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">PRODUCTION / TIMELINE</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">RECONFIGURE SEQUENCE</h2>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger bg-black border border-danger text-danger rounded-0 mb-4 p-4 shadow-sm">
            <h6 class="tracking-widest uppercase small fw-bold mb-3"><i class="bi bi-exclamation-triangle me-2"></i> SYSTEM VALIDATION FAILED</h6>
            <ul class="mb-0 small font-monospace">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('scenes.update', $scene) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="bg-black border border-secondary p-5 shadow-sm">
                    <h5 class="text-white small tracking-widest uppercase mb-4">Sequence Parameters</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-8 mb-4 mb-md-0">
                            <label class="form-label text-secondary small tracking-widest uppercase">Target Production</label>
                            <select name="project_id" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2" required>
                                @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}" {{ $scene->project_id == $proj->id ? 'selected' : '' }}>
                                        {{ strtoupper($proj->title) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label text-secondary small tracking-widest uppercase">Timeline Index</label>
                            <input type="number" name="order_index" value="{{ $scene->order_index }}" min="1" step="1" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2 font-monospace" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">Sequence Status</label>
                        <select name="status" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                            <option value="Draft" {{ $scene->status == 'Draft' ? 'selected' : '' }}>Draft</option>
                            <option value="Ready" {{ $scene->status == 'Ready' ? 'selected' : '' }}>Ready</option>
                            <option value="Processing" {{ $scene->status == 'Processing' ? 'selected' : '' }}>Processing</option>
                            <option value="failed" {{ $scene->status == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-secondary small tracking-widest uppercase">Action / Script Segment</label>
                        <textarea name="script_segment" rows="5" class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 italic" required>{{ $scene->script_segment }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('projects.timeline', $scene->project_id) }}" class="text-secondary text-decoration-none small tracking-widest uppercase hover-white">Cancel Changes</a>
                        <button type="submit" class="btn btn-white bg-white text-black rounded-0 px-5 py-3 fw-bold tracking-widest uppercase">
                            UPDATE SEQUENCE
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="border border-secondary bg-black p-4 h-100">
                    <h6 class="text-white small tracking-widest uppercase mb-3">Modify Cast Assignment</h6>
                    <p class="text-secondary small italic mb-4">Update the characters appearing in this sequence.</p>
                    
                    <div class="list-group rounded-0">
                        @forelse($characters as $character)
                        <label class="list-group-item bg-dark border-secondary text-white d-flex align-items-center mb-2 transition-hover p-2">
                            <input class="form-check-input me-3 bg-black border-secondary" type="checkbox" name="characters[]" value="{{ $character->id }}"
                                {{ $scene->characters->contains($character->id) ? 'checked' : '' }}>
                            
                            @if($character->reference_image)
                                <img src="{{ asset('storage/' . $character->reference_image) }}" alt="{{ $character->name }}" class="rounded-circle border border-secondary me-3 object-fit-cover" style="width: 40px; height: 40px;">
                            @else
                                <div class="rounded-circle bg-black border border-secondary d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person text-secondary"></i>
                                </div>
                            @endif

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="d-block small fw-bold uppercase">{{ $character->name }}</span>
                                    <span class="badge border border-secondary text-secondary uppercase" style="font-size: 8px;">{{ $character->role }}</span>
                                </div>
                                <code class="text-info" style="font-size: 10px;">{{ $character->ai_tag }}</code>
                            </div>
                        </label>
                        @empty
                        <div class="text-center p-3 border border-dashed border-secondary">
                            <span class="text-secondary small uppercase tracking-widest">No Cast Initialized.</span>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.2em; }
    .italic { font-style: italic; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    .object-fit-cover { object-fit: cover; }
    .form-control:focus, .form-select:focus { 
        background-color: transparent; 
        border-bottom-color: #fff !important; 
        box-shadow: none; 
        color: #fff;
    }
    .transition-hover:hover { border-color: #fff !important; background: #111 !important; cursor: pointer; }
    .btn-white:hover { background: #ccc !important; transform: scale(1.02); transition: 0.2s; }
</style>
@endsection