@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">PRODUCTION / TIMELINE</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">INITIALIZE SCENE</h2>
    </div>

    <form action="{{ route('scenes.store') }}" method="POST">
        @csrf
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="bg-black border border-secondary p-5 shadow-sm">
                    <h5 class="text-white small tracking-widest uppercase mb-4">Sequence Parameters</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-8 mb-4 mb-md-0">
                            <label class="form-label text-secondary small tracking-widest uppercase">Target Production</label>
                            <select name="project_id" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2" required>
                                <option value="">SELECT PROJECT...</option>
                                @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}" {{ request('project') == $proj->id ? 'selected' : '' }}>
                                        {{ strtoupper($proj->title) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label text-secondary small tracking-widest uppercase">Timeline Index</label>
                            <input type="number" name="order_index" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2 font-monospace" placeholder="e.g. 001" required>
                            <small class="text-secondary mt-1 d-block" style="font-size: 10px;">Position in the 2.5hr cut.</small>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-secondary small tracking-widest uppercase">Action / Script Segment</label>
                        <textarea name="script_segment" rows="5" class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 italic" 
                                  placeholder="Describe the action. E.g., 'Camera pans across the neon-lit streets of Colombo as Silas lights a cigarette...'" required></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="javascript:history.back()" class="text-secondary text-decoration-none small tracking-widest uppercase hover-white">Cancel</a>
                        <button type="submit" class="btn btn-white bg-white text-black rounded-0 px-5 py-3 fw-bold tracking-widest uppercase">
                            LOCK SEQUENCE
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="border border-secondary bg-black p-4 h-100">
                    <h6 class="text-white small tracking-widest uppercase mb-3">AI Cast Assignment</h6>
                    <p class="text-secondary small italic mb-4">Select the characters appearing in this scene. Their AI Seeds will be automatically injected into the Seedance 2.0 prompt.</p>
                    
                    <div class="list-group rounded-0">
                        @forelse($characters as $character)
                        <label class="list-group-item bg-dark border-secondary text-white d-flex align-items-center mb-2 transition-hover">
                            <input class="form-check-input me-3 bg-black border-secondary" type="checkbox" name="characters[]" value="{{ $character->id }}">
                            <div>
                                <span class="d-block small fw-bold uppercase">{{ $character->name }}</span>
                                <code class="text-info" style="font-size: 10px;">{{ $character->ai_tag ?? 'NO_TAG_ASSIGNED' }}</code>
                            </div>
                        </label>
                        @empty
                        <div class="text-center p-3 border border-dashed border-secondary">
                            <span class="text-secondary small uppercase tracking-widest">No Cast Initialized in System.</span>
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
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
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