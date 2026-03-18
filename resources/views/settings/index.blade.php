@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">SYSTEM / CORE</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">STUDIO SETTINGS</h2>
    </div>

    <div class="row g-5">
        <div class="col-lg-6">
            <div class="bg-black border border-secondary p-4 h-100">
                <h5 class="text-white small tracking-widest uppercase mb-4 border-bottom border-secondary pb-2">AI ENGINE (SEEDANCE 2.0)</h5>
                
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">API MASTER KEY</label>
                        <input type="password" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" value="sk_void_shadow_test_key_0982">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">DEFAULT RENDER RESOLUTION</label>
                        <select class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                            <option value="1080p">1080p (Production Standard)</option>
                            <option value="4k" selected>4K (Cinematic Master)</option>
                            <option value="720p">720p (Draft Speed)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-outline-light rounded-0 px-4 py-2 small tracking-widest uppercase">Save Engine Config</button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="bg-black border border-secondary p-4 h-100">
                <h5 class="text-white small tracking-widest uppercase mb-4 border-bottom border-secondary pb-2">INFRASTRUCTURE</h5>
                
                <div class="mb-4">
                    <label class="text-secondary small tracking-widest d-block mb-1">STORAGE NODE STATUS</label>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success rounded-circle p-1 me-2" style="width: 10px; height: 10px;"> </span>
                        <span class="text-white small">CONTABO-MYSQL-CONNECTED (127.0.0.1)</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-secondary small tracking-widest d-block mb-1">LOCAL WORKSTATION</label>
                    <span class="text-secondary small italic">MacBook Pro M-Series / macOS 2026</span>
                </div>

                <div class="mt-5 pt-3">
                    <h6 class="text-danger small tracking-widest uppercase mb-2">Danger Zone</h6>
                    <button class="btn btn-outline-danger rounded-0 px-3 py-1 small tracking-widest">WIPE TEMP CACHE</button>
                </div>
            </div>
        </div>
    </div>
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
</style>
@endsection