@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">PRODUCTION TOOLS</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">SYNC FACE ENGINE</h2>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="border border-secondary bg-black p-4 h-100">
                <h6 class="text-white small tracking-widest uppercase mb-4">Source Reference (Seed)</h6>
                <div class="border border-dashed border-secondary p-5 text-center transition-hover bg-dark">
                    <i class="bi bi-person-bounding-box text-secondary mb-3" style="font-size: 2rem;"></i>
                    <p class="text-secondary small uppercase tracking-widest" style="font-size: 10px;">UPLOAD REFERENCE FACE (.JPG/.PNG)</p>
                    <input type="file" class="form-control bg-transparent border-secondary text-secondary small mt-3">
                </div>
            </div>
        </div>
        <div class="col-md-2 d-flex align-items-center justify-content-center">
            <i class="bi bi-arrow-right text-info" style="font-size: 2rem;"></i>
        </div>
        <div class="col-md-5">
            <div class="border border-secondary bg-black p-4 h-100">
                <h6 class="text-white small tracking-widest uppercase mb-4">Target Clip (Destination)</h6>
                <div class="border border-dashed border-secondary p-5 text-center transition-hover bg-dark">
                    <i class="bi bi-film text-secondary mb-3" style="font-size: 2rem;"></i>
                    <p class="text-secondary small uppercase tracking-widest" style="font-size: 10px;">UPLOAD RENDERED SCENE (.MP4)</p>
                    <input type="file" class="form-control bg-transparent border-secondary text-secondary small mt-3">
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-5 text-center">
        <button class="btn btn-outline-info rounded-0 px-5 py-3 fw-bold tracking-widest uppercase">INITIALIZE SYNC PROCESS</button>
    </div>
</div>
<style>
    .tracking-widest { letter-spacing: 0.2em; }
    .uppercase { text-transform: uppercase; }
    .transition-hover:hover { border-color: #fff !important; cursor: pointer; }
</style>
@endsection