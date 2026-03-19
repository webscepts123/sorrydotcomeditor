@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">STUDIO / MANAGEMENT</h6>
        <h2 class="fw-light uppercase" style="font-family: 'Syncopate'; letter-spacing: 4px;">EDIT STAFF IDENTITY</h2>
    </div>

    <div class="col-md-6">
        <form action="{{ route('editors.update', $editor) }}" method="POST" class="bg-black border border-secondary p-5">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="form-label text-secondary small tracking-widest uppercase">FULL NAME</label>
                <input type="text" name="name" value="{{ $editor->name }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" required>
            </div>

            <div class="mb-4">
                <label class="form-label text-secondary small tracking-widest uppercase">EMAIL ADDRESS</label>
                <input type="email" name="email" value="{{ $editor->email }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" required>
            </div>

            <div class="mb-5">
                <label class="form-label text-secondary small tracking-widest uppercase">SPECIALIZATION</label>
                <select name="role" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                    <option value="vfx_artist" {{ $editor->role == 'vfx_artist' ? 'selected' : '' }}>AI VFX Artist</option>
                    <option value="sound_engineer" {{ $editor->role == 'sound_engineer' ? 'selected' : '' }}>Sound Designer</option>
                    <option value="director" {{ $editor->role == 'director' ? 'selected' : '' }}>Assistant Director</option>
                </select>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('editors.index') }}" class="text-secondary text-decoration-none small tracking-widest uppercase">Cancel</a>
                <button type="submit" class="btn btn-white bg-white text-black rounded-0 px-5 py-3 fw-bold tracking-widest uppercase">
                    UPDATE RECORD
                </button>
            </div>
        </form>
    </div>
</div>
@endsection