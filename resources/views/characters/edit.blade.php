@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5">
        <h6 class="text-secondary mb-1 uppercase" style="letter-spacing: 3px;">PRODUCTION / CASTING</h6>
        <h2 class="fw-light uppercase" style="font-family: 'Syncopate'; letter-spacing: 4px;">
            EDIT CHARACTER
        </h2>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger bg-black border border-danger text-danger rounded-0 mb-4 p-4 shadow-sm">
            <h6 class="tracking-widest uppercase small fw-bold mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i> SYSTEM VALIDATION FAILED
            </h6>
            <ul class="mb-0 small font-monospace">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">

            {{-- ✅ ONLY CHANGE: UPDATE ROUTE --}}
            <form action="{{ route('characters.update', $character) }}" method="POST" enctype="multipart/form-data" class="bg-black border border-secondary p-5">
                @csrf
                @method('PUT')

                {{-- NAME --}}
                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">Character Name</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $character->name) }}"
                           class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2"
                           required>
                </div>

                <div class="row">

                    {{-- AI TAG --}}
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">AI Seed Tag</label>
                        <input type="text"
                               id="ai_tag"
                               name="ai_tag"
                               value="{{ old('ai_tag', $character->ai_tag) }}"
                               class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2">
                    </div>

                    {{-- ROLE --}}
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">Role / Archetype</label>
                        <select id="role"
                                name="role"
                                class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2"
                                required>

                            @foreach(['protagonist','antagonist','supporting','mentor','comic_relief','love_interest','anti_hero'] as $role)
                                <option value="{{ $role }}"
                                    {{ old('role', $character->role) == $role ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_',' ', $role)) }}
                                </option>
                            @endforeach

                        </select>
                    </div>
                </div>

                {{-- DESCRIPTION --}}
                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">Visual Description</label>
                    <textarea id="description"
                              name="description"
                              rows="4"
                              class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2">{{ old('description', $character->description) }}</textarea>
                </div>

                {{-- PERSONALITY --}}
                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">Personality / Behavior</label>
                    <textarea id="personality"
                              name="personality"
                              rows="3"
                              class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2">{{ old('personality', $character->personality) }}</textarea>
                </div>

                {{-- DIALOGUE --}}
                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">Dialogue Style</label>
                    <textarea id="dialogue_style"
                              name="dialogue_style"
                              rows="3"
                              class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2">{{ old('dialogue_style', $character->dialogue_style) }}</textarea>
                </div>

                {{-- PROMPT --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label text-secondary small tracking-widest uppercase mb-0">
                            Generated Character Prompt
                        </label>

                        <button type="button"
                                onclick="generatePrompt()"
                                class="btn btn-outline-light btn-sm rounded-0 tracking-widest uppercase">
                            Generate Prompt
                        </button>
                    </div>

                    <textarea id="prompt"
                              name="prompt"
                              rows="8"
                              class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 font-monospace">{{ old('prompt', $character->prompt) }}</textarea>
                </div>

                {{-- IMAGE --}}
                <div class="mb-5">
                    <label class="form-label text-secondary small tracking-widest uppercase">
                        Reference Image <span class="text-muted">(Optional)</span>
                    </label>

                    {{-- SHOW CURRENT IMAGE --}}
                    @if($character->image_path)
                        <div class="mb-3">
                            <img src="{{ asset('storage/'.$character->image_path) }}"
                                 class="img-fluid border border-secondary"
                                 style="max-height:200px;">
                        </div>
                    @endif

                    <input type="file"
                           name="reference_image"
                           class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-secondary p-2"
                           accept="image/jpeg,image/png,image/webp">
                </div>

                {{-- ACTION --}}
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('characters.show', $character) }}"
                       class="text-secondary text-decoration-none small tracking-widest uppercase">
                        Cancel
                    </a>

                    <button type="submit"
                            class="btn btn-white bg-white text-black rounded-0 px-5 py-3 fw-bold tracking-widest uppercase">
                        UPDATE IDENTITY
                    </button>
                </div>
            </form>
        </div>

        {{-- SIDE PANEL SAME --}}
        <div class="col-lg-5">
            <div class="p-4 border border-secondary text-secondary small bg-black">
                <h6 class="text-white tracking-widest mb-3 uppercase">EDIT NOTE</h6>

                <p>
                    Update character identity, personality, and AI prompt.
                </p>

                <p class="mt-3">
                    Uploading a new image will replace the existing reference.
                </p>
            </div>
        </div>
    </div>
</div>

{{-- KEEP SAME STYLE + SCRIPT --}}
<style>
.uppercase { text-transform: uppercase; }
.tracking-widest { letter-spacing: 0.15em; }
.font-monospace { font-family: 'Courier New', Courier, monospace; }

.form-control:focus,
.form-select:focus {
    background-color: transparent;
    border-color: #fff !important;
    box-shadow: none;
    color: #fff;
}

.form-select option {
    background: #000;
    color: #fff;
}
</style>

<script>
function generatePrompt() {
    let name = document.getElementById('name').value.trim() || 'Unnamed Character';
    let aiTag = document.getElementById('ai_tag').value.trim() || '@character_seed_default';
    let role = document.getElementById('role').value;
    let description = document.getElementById('description').value.trim() || '';
    let personality = document.getElementById('personality').value.trim() || '';
    let dialogueStyle = document.getElementById('dialogue_style').value.trim() || '';

    let prompt = `Character Name: ${name}
AI Seed Tag: ${aiTag}
Role: ${role}

Description:
${description}

Personality:
${personality}

Dialogue:
${dialogueStyle}

Create cinematic consistent character.`;

    document.getElementById('prompt').value = prompt;
}
</script>
@endsection