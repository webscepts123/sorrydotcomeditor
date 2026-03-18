@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5">
        <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">PRODUCTION / NEW</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">INITIALIZE PROJECT</h2>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card bg-black border border-secondary rounded-0 shadow-sm">
                <div class="card-body p-5">
                    
                    <form action="{{ route('projects.store') }}" method="POST">
                        @csrf

                        <div class="mb-5">
                            <label class="form-label text-secondary small tracking-widest uppercase">Project Title</label>
                            <input type="text" name="title" 
                                   class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2 fs-4 @error('title') border-danger @enderror" 
                                   placeholder="VOID SHADOW" value="{{ old('title') }}" required autofocus>
                            @error('title')
                                <small class="text-danger mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-6 mb-4">
                                <label class="form-label text-secondary small tracking-widest uppercase">Aspect Ratio</label>
                                <select name="aspect_ratio" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                    <option value="2.39:1">2.39:1 (Cinemascope)</option>
                                    <option value="16:9">16:9 (Widescreen)</option>
                                    <option value="1.85:1">1.85:1 (Flat)</option>
                                    <option value="1:1">1:1 (Square/Social)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-secondary small tracking-widest uppercase">Style Preset</label>
                                <select name="style_preset" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                    <option value="high-contrast-noir">High-Contrast Noir</option>
                                    <option value="cinematic-dark">Cinematic Dark</option>
                                    <option value="grainy-16mm">Grainy 16mm</option>
                                    <option value="cyberpunk-neon">Cyberpunk Neon</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label text-secondary small tracking-widest uppercase">Production Logline</label>
                            <textarea name="description" rows="3" 
                                      class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" 
                                      placeholder="A 2.5-hour cinematic dark thriller set in Sri Lanka...">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-4">
                            <a href="{{ route('projects.index') }}" class="text-secondary text-decoration-none small tracking-widest uppercase hover-white">
                                <i class="bi bi-arrow-left me-2"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-white bg-white text-black fw-bold rounded-0 px-5 py-3 tracking-widest uppercase shadow-lg">
                                Create Production
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="p-4 border border-secondary text-secondary small">
                <h6 class="text-white tracking-widest mb-3 uppercase">Production Tips</h6>
                <p class="mb-3">● <strong>Cinemascope (2.39:1)</strong> is recommended for the "Void Shadow" aesthetic.</p>
                <p class="mb-3">● The <strong>Style Preset</strong> will act as the base prompt modifier for all Seedance 2.0 AI generations.</p>
                <p class="mb-0">● Once initialized, you can begin adding scenes and assigning editors.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .form-control:focus, .form-select:focus { 
        background: transparent; 
        border-bottom-color: #fff !important; 
        box-shadow: none; 
        color: #fff;
    }
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.15em; }
    .hover-white:hover { color: #fff !important; }
    .btn-white:hover { background: #e0e0e0; transform: translateY(-2px); transition: 0.3s; }
</style>
@endsection