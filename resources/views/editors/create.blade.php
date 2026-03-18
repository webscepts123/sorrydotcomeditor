@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">STUDIO / STAFFING</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">ADD NEW EDITOR</h2>
    </div>

    <div class="row">
        <div class="col-md-7">
            <form action="{{ route('editors.store') }}" method="POST" class="bg-black border border-secondary p-5 shadow-sm">
                @csrf

                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">Full Name</label>
                    <input type="text" name="name" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2 fs-5 @error('name') border-danger @enderror" placeholder="E.G. KASUN PERERA" required autofocus>
                    @error('name')
                        <small class="text-danger mt-1">{{ $message }}</small>
                    @enderror
                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <label class="form-label text-secondary small tracking-widest uppercase">Specialization</label>
                        <select name="specialization" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                            <option value="VFX / AI Generation">VFX / AI Generation</option>
                            <option value="Sound Design">Sound Design</option>
                            <option value="Color Grading">Color Grading</option>
                            <option value="Script Supervision">Script Supervision</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary small tracking-widest uppercase">Access Level</label>
                        <select name="access_level" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                            <option value="editor">Standard Editor</option>
                            <option value="lead">Lead Producer</option>
                            <option value="viewer">Viewer Only</option>
                        </select>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label text-secondary small tracking-widest uppercase">Staff Notes / Bio</label>
                    <textarea name="bio" rows="3" class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 italic" placeholder="Previous experience with cinematic noir or AI prompting..."></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3">
                    <a href="{{ route('editors.index') }}" class="text-secondary text-decoration-none small tracking-widest uppercase hover-white">Cancel</a>
                    <button type="submit" class="btn btn-white bg-white text-black rounded-0 px-5 py-3 fw-bold tracking-widest uppercase shadow-lg">
                        REGISTER STAFF
                    </button>
                </div>
            </form>
        </div>

        <div class="col-md-5">
            <div class="p-4 border border-secondary text-secondary small h-100">
                <h6 class="text-white tracking-widest mb-3 uppercase">Staff Management</h6>
                <p class="mb-3">● Once registered, editors can be assigned to specific **Projects** (Movies) via the Project Dossier.</p>
                <p class="mb-3">● For AI-driven projects like **Void Shadow**, ensure editors are familiar with Seedance 2.0 prompting protocols.</p>
                <p class="mb-0">● Access levels determine if the staff member can delete clips or only view the timeline.</p>
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
    .btn-white:hover { background: #e0e0e0 !important; transform: scale(1.02); transition: 0.2s; }
    .hover-white:hover { color: #fff !important; }
</style>
@endsection