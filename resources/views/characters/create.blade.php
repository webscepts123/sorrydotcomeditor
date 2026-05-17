@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5">
        <h6 class="text-secondary mb-1 uppercase" style="letter-spacing: 3px;">PRODUCTION / CASTING</h6>
        <h2 class="fw-light uppercase" style="font-family: 'Syncopate'; letter-spacing: 4px;">INITIALIZE CHARACTER</h2>
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
            <form action="{{ route('characters.store') }}" method="POST" enctype="multipart/form-data" class="bg-black border border-secondary p-5">
                @csrf

                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">Character Name</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name') }}"
                           class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2"
                           placeholder="E.G. THE WATCHER"
                           required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">AI Seed Tag</label>
                        <input type="text"
                               id="ai_tag"
                               name="ai_tag"
                               value="{{ old('ai_tag') }}"
                               class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2"
                               placeholder="@actor_v1_seed_9921">
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">Role / Archetype</label>
                        <select id="role"
                                name="role"
                                class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2"
                                required>
                            <option value="protagonist" {{ old('role') == 'protagonist' ? 'selected' : '' }}>Protagonist</option>
                            <option value="antagonist" {{ old('role') == 'antagonist' ? 'selected' : '' }}>Antagonist</option>
                            <option value="supporting" {{ old('role') == 'supporting' ? 'selected' : '' }}>Supporting</option>
                            <option value="mentor" {{ old('role') == 'mentor' ? 'selected' : '' }}>Mentor</option>
                            <option value="comic_relief" {{ old('role') == 'comic_relief' ? 'selected' : '' }}>Comic Relief</option>
                            <option value="love_interest" {{ old('role') == 'love_interest' ? 'selected' : '' }}>Love Interest</option>
                            <option value="anti_hero" {{ old('role') == 'anti_hero' ? 'selected' : '' }}>Anti Hero</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">Visual Description</label>
                    <textarea id="description"
                              name="description"
                              rows="4"
                              class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2"
                              placeholder="Describe face, outfit, body shape, age, hair, expression, style...">{{ old('description') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">Personality / Behavior</label>
                    <textarea id="personality"
                              name="personality"
                              rows="3"
                              class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2"
                              placeholder="Cold, calm, fearless, emotional, funny, mysterious...">{{ old('personality') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">Dialogue Style</label>
                    <textarea id="dialogue_style"
                              name="dialogue_style"
                              rows="3"
                              class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2"
                              placeholder="Speaks slowly, powerful voice, short lines, poetic tone...">{{ old('dialogue_style') }}</textarea>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label text-secondary small tracking-widest uppercase mb-0">Generated Character Prompt</label>
                        <button type="button" onclick="generatePrompt()" class="btn btn-outline-light btn-sm rounded-0 tracking-widest uppercase">
                            Generate Prompt
                        </button>
                    </div>

                    <textarea id="prompt"
                              name="prompt"
                              rows="8"
                              class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 font-monospace"
                              placeholder="Click Generate Prompt or write manually...">{{ old('prompt') }}</textarea>
                </div>

                <div class="mb-5">
                    <label class="form-label text-secondary small tracking-widest uppercase">
                        Reference Image <span class="text-muted">(Optional)</span>
                    </label>

                    <input type="file"
                           name="reference_image"
                           class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-secondary p-2"
                           accept="image/jpeg,image/png,image/webp">

                    <small class="text-secondary mt-1 d-block" style="font-size: 10px;">
                        Optional. You can create character without image.
                    </small>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('characters.index') }}" class="text-secondary text-decoration-none small tracking-widest uppercase">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-white bg-white text-black rounded-0 px-5 py-3 fw-bold tracking-widest uppercase">
                        Save Identity
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="p-4 border border-secondary text-secondary small bg-black">
                <h6 class="text-white tracking-widest mb-3 uppercase">Character Prompt Guide</h6>

                <p>
                    Image is optional. You can create character using only name, role, visual description, personality, and prompt.
                </p>

                <hr class="border-secondary">

                <h6 class="text-white small uppercase tracking-widest">Example</h6>

                <div class="font-monospace small text-secondary">
                    Name: The Watcher<br>
                    Role: Antagonist<br>
                    AI Tag: @actor_v1_shadow_9921<br>
                    Style: Dark cinematic villain
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .uppercase {
        text-transform: uppercase;
    }

    .tracking-widest {
        letter-spacing: 0.15em;
    }

    .font-monospace {
        font-family: 'Courier New', Courier, monospace;
    }

    .form-control:focus,
    .form-select:focus {
        background-color: transparent;
        border-color: #fff !important;
        box-shadow: none;
        color: #fff;
    }

    .form-control::placeholder,
    textarea::placeholder {
        color: #666 !important;
    }

    .form-select option {
        background: #000;
        color: #fff;
    }

    input[type="file"]::file-selector-button {
        background: #fff;
        color: #000;
        border: 0;
        padding: 8px 14px;
        margin-right: 12px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    @media (max-width: 768px) {
        form.bg-black {
            padding: 25px !important;
        }

        .btn {
            width: 100%;
            margin-top: 10px;
        }

        .d-flex.justify-content-between {
            flex-direction: column;
            align-items: stretch !important;
        }
    }
</style>

<script>
function generatePrompt() {
    let name = document.getElementById('name').value.trim() || 'Unnamed Character';
    let aiTag = document.getElementById('ai_tag').value.trim() || '@character_seed_default';
    let role = document.getElementById('role').value;
    let description = document.getElementById('description').value.trim() || 'Realistic cinematic character with consistent facial identity, detailed outfit, and strong screen presence.';
    let personality = document.getElementById('personality').value.trim() || 'Strong personality, believable emotions, natural body language, memorable presence.';
    let dialogueStyle = document.getElementById('dialogue_style').value.trim() || 'Speaks naturally with cinematic tone and emotional weight.';

    let roleText = '';

    if (role === 'protagonist') {
        roleText = 'Main protagonist, heroic presence, emotionally strong, determined, carries the main story.';
    } else if (role === 'antagonist') {
        roleText = 'Main antagonist, powerful presence, mysterious, intimidating, creates conflict in the story.';
    } else if (role === 'supporting') {
        roleText = 'Supporting character, believable personality, helps develop the story and main characters.';
    } else if (role === 'mentor') {
        roleText = 'Wise mentor character, calm presence, guides the hero, speaks with experience and emotional depth.';
    } else if (role === 'comic_relief') {
        roleText = 'Comic relief character, energetic, funny, expressive, brings humor while staying realistic.';
    } else if (role === 'love_interest') {
        roleText = 'Love interest character, emotionally warm, charming, meaningful connection to the protagonist.';
    } else if (role === 'anti_hero') {
        roleText = 'Anti-hero character, morally complex, intense, unpredictable, conflicted between good and bad choices.';
    }

    let prompt =
`Character Name: ${name}
AI Seed Tag: ${aiTag}
Role / Archetype: ${role.toUpperCase()}

Role Definition:
${roleText}

Visual Description:
${description}

Personality / Behavior:
${personality}

Dialogue Style:
${dialogueStyle}

Final AI Character Prompt:
Create a cinematic AI character named ${name}. Maintain the same face, hairstyle, body shape, skin tone, outfit style, personality, and emotional identity across every scene. The character is a ${role} with strong cinematic screen presence. Use realistic lighting, professional movie trailer quality, detailed facial expressions, natural movement, and consistent identity. Use AI seed tag ${aiTag} for character consistency.`;

    document.getElementById('prompt').value = prompt;
}
</script>
@endsection