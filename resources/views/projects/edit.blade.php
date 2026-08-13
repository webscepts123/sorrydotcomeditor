@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5">
        <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">PRODUCTION / CONFIG</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">UPDATE PROJECT</h2>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger bg-black border border-danger text-danger rounded-0 mb-4 p-4">
            <h6 class="tracking-widest uppercase small fw-bold mb-3">SYSTEM VALIDATION FAILED</h6>
            <ul class="mb-0 small font-monospace">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card bg-black border border-secondary rounded-0 shadow-sm">
                <div class="card-body p-5">
                    <form action="{{ route('projects.update', $project) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-5">
                            <label class="form-label text-secondary small tracking-widest uppercase">Project Title</label>
                            <input type="text"
                                   name="title"
                                   class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2 fs-4 @error('title') border-danger @enderror"
                                   value="{{ old('title', $project->title) }}"
                                   required
                                   autofocus>
                        </div>

                        <div class="row mb-5">
                            <div class="col-md-4 mb-4">
                                <label class="form-label text-secondary small tracking-widest uppercase">Aspect Ratio</label>
                                <select name="aspect_ratio" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                    @foreach(['2.39:1' => '2.39:1 (Cinemascope)', '16:9' => '16:9 (Widescreen)', '1.85:1' => '1.85:1 (Flat)', '1:1' => '1:1 (Square/Social)'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('aspect_ratio', $project->aspect_ratio) === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label text-secondary small tracking-widest uppercase">Style Preset</label>
                                <select name="style_preset" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                    @foreach(['high-contrast-noir' => 'High-Contrast Noir', 'cinematic-dark' => 'Cinematic Dark', 'grainy-16mm' => 'Grainy 16mm', 'cyberpunk-neon' => 'Cyberpunk Neon'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('style_preset', $project->style_preset) === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label text-secondary small tracking-widest uppercase">Status</label>
                                <select name="status" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                    @foreach(['draft', 'generating', 'stitching', 'completed', 'failed'] as $status)
                                        <option value="{{ $status }}" {{ old('status', $project->status) === $status ? 'selected' : '' }}>
                                            {{ strtoupper($status) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label text-secondary small tracking-widest uppercase">Production Logline</label>
                            <textarea name="description"
                                      rows="4"
                                      class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2"
                                      placeholder="A 2.5-hour cinematic dark thriller set in Sri Lanka...">{{ old('description', $project->description) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-4">
                            <a href="{{ route('projects.show', $project) }}" class="text-secondary text-decoration-none small tracking-widest uppercase hover-white">
                                <i class="bi bi-arrow-left me-2"></i> Cancel
                            </a>

                            <button type="submit" class="btn btn-white bg-white text-black fw-bold rounded-0 px-5 py-3 tracking-widest uppercase shadow-lg">
                                Save Config
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="p-4 border border-secondary text-secondary small">
                <h6 class="text-white tracking-widest mb-3 uppercase">Configuration</h6>
                <p class="mb-3">Changes here update the project metadata used across the dashboard, editor, and timeline.</p>
                <p class="mb-0">The slug is regenerated from the title when you save.</p>
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
    .form-select option { background: #000; color: #fff; }
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.15em; }
    .hover-white:hover { color: #fff !important; }
    .btn-white:hover { background: #e0e0e0; transform: translateY(-2px); transition: 0.3s; }
</style>
@endsection
