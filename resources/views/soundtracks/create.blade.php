@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">AUDIO / SCORE</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">INITIALIZE AUDIO STEM</h2>
    </div>

    <form action="{{ route('soundtracks.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="bg-black border border-secondary p-5 shadow-sm">
                    <h5 class="text-white small tracking-widest uppercase mb-4">Track Metadata</h5>
                    
                    <div class="mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">Track Designation</label>
                        <input type="text" name="title" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2 fs-5" placeholder="E.G. MAIN_THEME_NOIR_V2" required autofocus>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <label class="form-label text-secondary small tracking-widest uppercase">Composer / Source</label>
                            <input type="text" name="composer" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" placeholder="AI Stem / Hans Z." required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary small tracking-widest uppercase">Stem Classification</label>
                            <select name="type" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                <option value="cinematic">Cinematic Score</option>
                                <option value="ambient">Ambient / Background</option>
                                <option value="foley">Foley / SFX</option>
                                <option value="dialogue">AI Dialogue Track</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-secondary small tracking-widest uppercase">Usage Notes</label>
                        <textarea name="notes" rows="3" class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 italic" placeholder="Intended for the final climax sequence..."></textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('soundtracks.index') }}" class="text-secondary text-decoration-none small tracking-widest uppercase hover-white">Cancel</a>
                        <button type="submit" class="btn btn-white bg-white text-black rounded-0 px-5 py-3 fw-bold tracking-widest uppercase">
                            UPLOAD TO SERVER
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="border border-secondary bg-black p-4 h-100 d-flex flex-column">
                    <h6 class="text-white small tracking-widest uppercase mb-3">Master Audio File</h6>
                    <p class="text-secondary small italic mb-4">Upload the `.wav` or `.mp3` master file. For "Void Shadow", high-fidelity `.wav` stems are recommended for the final Contabo render.</p>
                    
                    <div class="border border-dashed border-secondary p-5 text-center flex-grow-1 d-flex flex-column justify-content-center align-items-center transition-hover bg-dark">
                        <i class="bi bi-cloud-arrow-up text-secondary mb-3" style="font-size: 3rem;"></i>
                        <input type="file" name="audio_file" class="form-control bg-transparent border-secondary text-secondary small w-75 mx-auto" accept="audio/mp3,audio/wav" required>
                        <small class="text-secondary mt-3 uppercase tracking-widest" style="font-size: 9px;">MAX FILE SIZE: 50MB</small>
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
    .form-control:focus, .form-select:focus { 
        background-color: transparent; 
        border-bottom-color: #fff !important; 
        box-shadow: none; 
        color: #fff;
    }
    .transition-hover:hover { border-color: #fff !important; transition: 0.3s; }
    .btn-white:hover { background: #ccc !important; transform: scale(1.02); transition: 0.2s; }
    .hover-white:hover { color: #fff !important; }
</style>
@endsection