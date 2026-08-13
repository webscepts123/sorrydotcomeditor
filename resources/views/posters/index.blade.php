@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size:10px;">ART DEPARTMENT / KEY ART</h6>
        <h2 class="fw-light" style="font-family:'Syncopate'; letter-spacing:4px;">MOVIE POSTER STUDIO</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-black border-success text-success rounded-0">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger bg-black border-danger text-danger rounded-0">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger bg-black border-danger text-danger rounded-0">{{ $errors->first() }}</div>
    @endif

    <div class="row g-5">
        <div class="col-lg-5">
            <form action="{{ route('posters.generate') }}" method="POST" class="bg-black border border-secondary p-4">
                @csrf
                <h5 class="small tracking-widest uppercase mb-4">Generate Key Art</h5>

                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">Movie / Project</label>
                    <select id="poster-project" name="project_id" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2" required>
                        <option value="">Select a movie</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest uppercase">Tagline</label>
                    <input id="poster-tagline" name="tagline" value="{{ old('tagline') }}" maxlength="180" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" placeholder="Some shadows never die.">
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label text-secondary small tracking-widest uppercase mb-0">Creative Direction</label>
                        <button id="poster-ideas-button" type="button" onclick="generatePosterIdeas()" class="btn btn-outline-info btn-sm rounded-0 tracking-widest uppercase">Generate Ideas with AI</button>
                    </div>
                    <textarea id="poster-direction" name="direction" rows="7" maxlength="2000" class="form-control bg-transparent border border-secondary rounded-0 text-white p-3" placeholder="Describe the subjects, setting, mood, colors, lighting, and composition...">{{ old('direction') }}</textarea>
                </div>

                <div id="poster-ideas-status" class="text-secondary small mb-3" aria-live="polite"></div>

                <button id="poster-submit" class="btn btn-white bg-white text-black rounded-0 w-100 py-3 fw-bold tracking-widest uppercase" type="submit">
                    Generate AI Poster
                </button>
                <small class="text-secondary d-block mt-3">Generation may take a few minutes. The current poster is replaced only after a new image succeeds.</small>
            </form>
        </div>

        <div class="col-lg-7">
            <div class="row g-4">
                @forelse($projects as $project)
                    <div class="col-md-6">
                        <div class="border border-secondary bg-black h-100">
                            @if($project->poster_image)
                                <img src="{{ asset('storage/' . $project->poster_image) }}" alt="{{ $project->title }} poster" class="w-100 poster-image">
                            @else
                                <div class="poster-placeholder d-flex align-items-center justify-content-center text-secondary">
                                    <div class="text-center"><i class="bi bi-image fs-1"></i><div class="mt-2 small tracking-widest">NO POSTER</div></div>
                                </div>
                            @endif
                            <div class="p-3 d-flex justify-content-between align-items-center">
                                <strong class="small tracking-widest">{{ strtoupper($project->title) }}</strong>
                                @if($project->poster_image)
                                    <a href="{{ asset('storage/' . $project->poster_image) }}" download class="text-info text-decoration-none small">DOWNLOAD</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 border border-secondary p-5 text-center text-secondary">Create a project before generating a movie poster.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
.uppercase { text-transform: uppercase; }
.poster-image, .poster-placeholder { aspect-ratio: 2 / 3; object-fit: cover; background:#080808; }
.form-control:focus, .form-select:focus { background-color:#000; border-color:#fff !important; box-shadow:none; color:#fff; }
.form-control::placeholder { color:#666; }
.form-select option { background:#000; color:#fff; }
</style>

<script>
async function generatePosterIdeas() {
    const projectId = document.getElementById('poster-project').value;
    const button = document.getElementById('poster-ideas-button');
    const status = document.getElementById('poster-ideas-status');

    if (!projectId) {
        status.textContent = 'Select a movie first.';
        return;
    }

    button.disabled = true;
    button.textContent = 'GENERATING...';
    status.textContent = 'AI is developing a poster concept...';

    try {
        const response = await fetch(@json(route('posters.generate-ideas')), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token()),
            },
            body: JSON.stringify({ project_id: projectId }),
        });
        const data = await response.json();

        if (!response.ok) throw new Error(data.message || 'Idea generation failed.');

        document.getElementById('poster-tagline').value = data.tagline;
        document.getElementById('poster-direction').value = data.direction;
        status.textContent = 'Tagline and creative direction generated.';
    } catch (error) {
        status.textContent = error.message;
    } finally {
        button.disabled = false;
        button.textContent = 'GENERATE IDEAS WITH AI';
    }
}

document.querySelector('form[action="{{ route('posters.generate') }}"]').addEventListener('submit', function () {
    const button = document.getElementById('poster-submit');
    button.disabled = true;
    button.textContent = 'GENERATING POSTER...';
});
</script>
@endsection
