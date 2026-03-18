@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">PRODUCTION / INITIALIZATION</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">CREATE CHARACTER</h2>
    </div>

    <form action="{{ route('characters.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="bg-black border border-secondary p-5 shadow-sm">
                    <h5 class="text-white small tracking-widest uppercase mb-4">Core Identity</h5>
                    
                    <div class="mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">Character Name</label>
                        <input type="text" name="name" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2 fs-5" placeholder="E.G. SILAS VANCE" required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <label class="form-label text-secondary small tracking-widest uppercase">Seedance AI Tag</label>
                            <input type="text" name="ai_tag" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" placeholder="@actor_v1_9982">
                            <small class="text-secondary mt-1 d-block">This tag locks the face consistency.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small tracking-widest uppercase">Narrative Role</label>
                            <select name="role" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                <option value="protagonist">Protagonist (Lead)</option>
                                <option value="antagonist">Antagonist (Villain)</option>
                                <option value="supporting">Supporting Cast</option>
                                <option value="extra">Atmospheric Extra</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-secondary small tracking-widest uppercase">Visual Prompt Base (Global)</label>
                        <textarea name="description" rows="4" class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 italic" 
                                  placeholder="Describe eyes, hair, scars, or clothing that must appear in every scene..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('characters.index') }}" class="text-secondary text-decoration-none small tracking-widest uppercase hover-white">Back to Cast</a>
                        <button type="submit" class="btn btn-white bg-white text-black rounded-0 px-5 py-3 fw-bold tracking-widest uppercase">
                            SAVE IDENTITY
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="border border-secondary bg-black p-4 mb-4">
                    <h6 class="text-white small tracking-widest uppercase mb-3">Reference Portrait</h6>
                    <div class="border border-dashed border-secondary p-5 text-center mb-3">
                        <i class="bi bi-cloud-upload text-secondary fs-2 mb-2"></i>
                        <input type="file" name="reference_image" class="form-control bg-dark border-secondary text-secondary small">
                    </div>
                    <p class="text-secondary small italic mb-0">Upload the most successful AI generation of this character as a master reference.</p>
                </div>

                <div class="p-4 border border-secondary text-secondary small">
                    <h6 class="text-white tracking-widest mb-3 uppercase">Consistency Protocol</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">● Use <strong>fixed seeds</strong> for primary characters.</li>
                        <li class="mb-2">● The "Visual Prompt Base" will be automatically appended to every scene prompt involving this character.</li>
                        <li class="mb-0">● Avoid generic descriptions like "man" or "woman"—be specific to help the AI.</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.2em; }
    .form-control:focus, .form-select:focus { 
        background-color: transparent; 
        border-bottom-color: #fff !important; 
        box-shadow: none; 
        color: #fff;
    }
    .btn-white:hover { background: #ccc !important; transform: scale(1.02); transition: 0.2s; }
</style>
@endsection