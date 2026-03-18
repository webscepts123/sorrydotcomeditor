@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">AUDIO / SCORE</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">RECONFIGURE STEM</h2>
    </div>

    <form action="{{ route('soundtracks.update', $soundtrack) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row g-5">
            <div class="col-lg-7">
                <div class="bg-black border border-secondary p-5 shadow-sm">
                    <h5 class="text-white small tracking-widest uppercase mb-4">Update Metadata</h5>
                    
                    <div class="mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">Track Designation</label>
                        <input type="text" name="title" value="{{ $soundtrack->title }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2 fs-5" required>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <label class="form-label text-secondary small tracking-widest uppercase">Composer / Source</label>
                            <input type="text" name="composer" value="{{ $soundtrack->composer }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary small tracking-widest uppercase">Stem Classification</label>
                            <select name="type" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                <option value="cinematic" {{ $soundtrack->type == 'cinematic' ? 'selected' : '' }}>Cinematic Score</option>
                                <option value="ambient" {{ $soundtrack->type == 'ambient' ? 'selected' : '' }}>Ambient / Background</option>
                                <option value="foley" {{ $soundtrack->type == 'foley' ? 'selected' : '' }}>Foley / SFX</option>
                                <option value="dialogue" {{ $soundtrack->type == 'dialogue' ? 'selected' : '' }}>AI Dialogue Track</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-secondary small tracking-widest uppercase">Usage Notes</label>
                        <textarea name="notes" rows="3" class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 italic">{{ $soundtrack->notes }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('soundtracks.index') }}" class="text-secondary text-decoration-none small tracking-widest uppercase hover-white">Cancel</a>
                        <button type="submit" class="btn btn-outline-info rounded-0 px-5 py-3 fw-bold tracking-widest uppercase">
                            UPDATE SYSTEM
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="border border-secondary bg-black p-4 h-100 d-flex flex-column">
                    <h6 class="text-white small tracking-widest uppercase mb-3">Replace Audio File (Optional)</h6>
                    <p class="text-secondary small italic mb-4">Current file: <code class="text-info">{{ basename($soundtrack->file_path) }}</code>. Upload a new `.wav` or `.mp3` below to overwrite it.</p>
                    
                    <div class="border border-dashed border-secondary p-5 text-center flex-grow-1 d-flex flex-column justify-content-center align-items-center bg-dark">
                        <i class="bi bi-music-note-list text-secondary mb-3" style="font-size: 3rem;"></i>
                        <input type="file" name="audio_file" class="form-control bg-transparent border-secondary text-secondary small w-75 mx-auto" accept="audio/mp3,audio/wav">
                        <small class="text-secondary mt-3 uppercase tracking-widest" style="font-size: 9px;">LEAVE BLANK TO KEEP CURRENT AUDIO</small>
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
    .form-control:focus, .form-select:focus { background-color: transparent; border-bottom-color: #fff !important; box-shadow: none; color: #fff; }
    .hover-white:hover { color: #fff !important; }
</style>
@endsection