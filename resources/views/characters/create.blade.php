@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5">
        <h6 class="text-secondary mb-1 uppercase" style="letter-spacing: 3px;">PRODUCTION / CASTING</h6>
        <h2 class="fw-light uppercase" style="font-family: 'Syncopate'; letter-spacing: 4px;">INITIALIZE CHARACTER</h2>
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

    <div class="row">
        <div class="col-md-7">
            <form action="{{ route('characters.store') }}" method="POST" enctype="multipart/form-data" class="bg-black border border-secondary p-5">
                @csrf

                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">CHARACTER NAME</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" placeholder="E.G. THE WATCHER" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">AI SEED TAG</label>
                        <input type="text" name="ai_tag" value="{{ old('ai_tag') }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" placeholder="@actor_v1_seed_9921">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">ROLE / ARCHETYPE</label>
                        <select name="role" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                            <option value="protagonist" {{ old('role') == 'protagonist' ? 'selected' : '' }}>Protagonist</option>
                            <option value="antagonist" {{ old('role') == 'antagonist' ? 'selected' : '' }}>Antagonist</option>
                            <option value="supporting" {{ old('role') == 'supporting' ? 'selected' : '' }}>Supporting</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">VISUAL DESCRIPTION (FOR PROMPTING)</label>
                    <textarea name="description" rows="3" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" placeholder="Describe key facial features for consistency...">{{ old('description') }}</textarea>
                </div>

                <div class="mb-5">
                    <label class="form-label text-secondary small tracking-widest uppercase">REFERENCE IMAGE (.JPG / .PNG)</label>
                    <input type="file" name="reference_image" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-secondary p-2" accept="image/jpeg,image/png,image/webp">
                    <small class="text-secondary mt-1 d-block" style="font-size: 10px;">This image will be used in the Sync Face tool and Casting Board.</small>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('characters.index') }}" class="text-secondary text-decoration-none small tracking-widest uppercase">CANCEL</a>
                    <button type="submit" class="btn btn-white bg-white text-black rounded-0 px-5 py-3 fw-bold tracking-widest uppercase">
                        SAVE IDENTITY
                    </button>
                </div>
            </form>
        </div>
        
        <div class="col-md-5">
            <div class="p-4 border border-secondary text-secondary small">
                <h6 class="text-white tracking-widest mb-3 uppercase">CONSISTENCY NOTE</h6>
                <p>The **AI Seed Tag** is passed directly to the video generation engine. Ensure you have tested the seed in your local environment before locking it into the production database.</p>
                <p class="mt-3">The **Reference Image** is strictly for your visual organization in the Command Center and as a base reference for deep-faking.</p>
            </div>
        </div>
    </div>
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.15em; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    .form-control:focus, .form-select:focus { 
        background-color: transparent; 
        border-bottom-color: #fff !important; 
        box-shadow: none; 
        color: #fff;
    }
</style>
@endsection