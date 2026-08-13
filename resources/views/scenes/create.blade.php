@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">PRODUCTION / TIMELINE</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">INITIALIZE SCENE</h2>
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

    <form action="{{ route('scenes.store') }}" method="POST">
        @csrf
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="bg-black border border-secondary p-5 shadow-sm">
                    <h5 class="text-white small tracking-widest uppercase mb-4">Sequence Parameters</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-8 mb-4 mb-md-0">
                            <label class="form-label text-secondary small tracking-widest uppercase">Target Production</label>
                            <select id="scene-project" name="project_id" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2" required>
                                <option value="">SELECT PROJECT...</option>
                                @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}" {{ (int) old('project_id', $selectedProjectId) === $proj->id ? 'selected' : '' }}>
                                        {{ strtoupper($proj->title) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label text-secondary small tracking-widest uppercase">Timeline Index</label>
                            <input type="number" name="order_index" value="{{ old('order_index') }}" min="1" step="1" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2 font-monospace" placeholder="1" required>
                            <small class="text-secondary mt-1 d-block" style="font-size: 10px;">Position in the 2.5hr cut (e.g. 1).</small>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-secondary small tracking-widest uppercase">Action / Script Segment</label>
                        <textarea name="script_segment" rows="5" class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 italic" 
                                  placeholder="Describe the action. E.g., 'Camera pans across the neon-lit streets of Colombo as Silas lights a cigarette...'" required>{{ old('script_segment') }}</textarea>
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
                    
                    <div id="cast-list" class="list-group rounded-0">
                        @forelse($characters as $character)
                        <label class="cast-item list-group-item bg-dark border-secondary text-white d-flex align-items-center mb-2 transition-hover p-2" data-project-id="{{ $character->project_id }}">
                            <input class="form-check-input me-3 bg-black border-secondary" type="checkbox" name="characters[]" value="{{ $character->id }}"
                                {{ (is_array(old('characters')) && in_array($character->id, old('characters'))) ? 'checked' : '' }}>
                            
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
                                    @if($character->role)
                                        <span class="badge border border-secondary text-secondary uppercase" style="font-size: 8px;">{{ $character->role }}</span>
                                    @endif
                                </div>
                                <code class="text-info" style="font-size: 10px;">{{ $character->ai_tag ?? 'NO_TAG_ASSIGNED' }}</code>
                            </div>
                        </label>
                        @empty
                        <div id="cast-empty" class="text-center p-3 border border-dashed border-secondary">
                            <span class="text-secondary small uppercase tracking-widest">No Cast Initialized in System.</span>
                            <a href="{{ route('characters.create', ['project' => $selectedProjectId]) }}" class="btn btn-outline-light btn-sm rounded-0 d-block mt-3">CREATE CHARACTER</a>
                        </div>
                        @endforelse
                    </div>
                    <div id="cast-filter-empty" class="text-center p-3 border border-dashed border-secondary d-none">
                        <span class="text-secondary small uppercase tracking-widest">No cast assigned to this project.</span>
                        <a id="create-character-link" href="{{ route('characters.create', ['project' => $selectedProjectId]) }}" data-base-url="{{ route('characters.create') }}" class="btn btn-outline-light btn-sm rounded-0 d-block mt-3">CREATE CHARACTER</a>
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

<script>
const projectSelect = document.getElementById('scene-project');

function filterCastByProject() {
    const projectId = projectSelect.value;
    const castItems = [...document.querySelectorAll('.cast-item')];
    let visibleCount = 0;

    castItems.forEach((item) => {
        const belongsTo = item.dataset.projectId;
        const visible = projectId && (belongsTo === projectId || belongsTo === '');
        item.classList.toggle('d-none', !visible);
        item.querySelector('input').disabled = !visible;
        if (visible) visibleCount++;
    });

    const emptyState = document.getElementById('cast-filter-empty');
    if (emptyState) emptyState.classList.toggle('d-none', visibleCount > 0 || !projectId);

    const createLink = document.getElementById('create-character-link');
    if (createLink) createLink.href = createLink.dataset.baseUrl + '?project=' + encodeURIComponent(projectId);
}

projectSelect.addEventListener('change', filterCastByProject);
filterCastByProject();
</script>
@endsection
