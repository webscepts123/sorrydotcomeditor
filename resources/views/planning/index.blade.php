@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">PRODUCTION / PLANNING</h6>
        <h2 class="fw-light mb-0" style="font-family: 'Syncopate'; letter-spacing: 4px;">STORY & SCHEDULE</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-black border border-success text-success rounded-0 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger bg-black border border-danger text-danger rounded-0 mb-4">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if($projects->isEmpty())
        <div class="border border-secondary bg-black p-5 text-center">
            <p class="text-secondary tracking-widest uppercase">No project found. Create a project before planning.</p>
            <a href="{{ route('projects.create') }}" class="btn btn-outline-light rounded-0 px-4 py-2 small tracking-widest uppercase">
                Initialize Project
            </a>
        </div>
    @else
        <form method="GET" action="{{ route('planning.index') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <label class="form-label text-secondary small tracking-widest uppercase">Planning Project</label>
                    <select name="project_id" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2" onchange="this.form.submit()">
                        @foreach($projects as $item)
                            <option value="{{ $item->id }}" {{ $project && $project->id === $item->id ? 'selected' : '' }}>
                                {{ strtoupper($item->title) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if($project)
                    <div class="col-lg-7 text-lg-end">
                        <a href="{{ route('projects.timeline', $project) }}" class="btn btn-outline-secondary rounded-0 px-4 py-2 small tracking-widest uppercase">
                            Timeline
                        </a>
                        <a href="{{ route('projects.videoeditor', $project) }}" class="btn btn-outline-info rounded-0 px-4 py-2 small tracking-widest uppercase">
                            Video Editor
                        </a>
                    </div>
                @endif
            </div>
        </form>

        @if($project && $plan)
            <form action="{{ route('planning.update') }}" method="POST">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                <div class="row g-4">
                    <div class="col-xl-7">
                        <div class="bg-black border border-secondary p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center gap-3 mb-4 border-bottom border-secondary pb-2">
                                <h5 class="text-white small tracking-widest uppercase mb-0">Full Story & Script Plan</h5>
                                <button id="generate-story-button" type="button" class="btn btn-outline-info btn-sm rounded-0 tracking-widest uppercase" onclick="generateStoryContent()">
                                    Generate with AI
                                </button>
                            </div>
                            <div id="generate-story-status" class="text-secondary small mb-3" aria-live="polite"></div>

                            <div class="mb-4">
                                <label class="form-label text-secondary small tracking-widest uppercase">Logline</label>
                                <input id="logline" type="text" name="logline" value="{{ old('logline', $plan->logline) }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" placeholder="One sentence that defines the movie.">
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-secondary small tracking-widest uppercase">Full Story Script</label>
                                <textarea id="full_story" name="full_story" rows="12" class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 font-monospace" placeholder="Write the complete story, plot, characters, turns, ending, and emotional arc here.">{{ old('full_story', $plan->full_story) }}</textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label text-secondary small tracking-widest uppercase">Script Outline / Acts</label>
                                <textarea id="script_outline" name="script_outline" rows="8" class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 font-monospace" placeholder="Act 1, Act 2, Act 3, turning points, midpoint, climax...">{{ old('script_outline', $plan->script_outline) }}</textarea>
                            </div>

                            <div>
                                <label class="form-label text-secondary small tracking-widest uppercase">Scene Breakdown</label>
                                <textarea id="scene_breakdown" name="scene_breakdown" rows="8" class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 font-monospace" placeholder="Scene 001: location, cast, action, required props, render notes...">{{ old('scene_breakdown', $plan->scene_breakdown) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="bg-black border border-secondary p-4 mb-4">
                            <h5 class="text-white small tracking-widest uppercase mb-4 border-bottom border-secondary pb-2">Schedule & Time Planning</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small tracking-widest uppercase">Start Date</label>
                                    <input type="date" name="schedule_start_date" value="{{ old('schedule_start_date', optional($plan->schedule_start_date)->format('Y-m-d')) }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small tracking-widest uppercase">End Date</label>
                                    <input type="date" name="schedule_end_date" value="{{ old('schedule_end_date', optional($plan->schedule_end_date)->format('Y-m-d')) }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small tracking-widest uppercase">Shooting Days</label>
                                    <input type="number" name="shooting_days" min="0" value="{{ old('shooting_days', $plan->shooting_days) }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small tracking-widest uppercase">Daily Call Time</label>
                                    <input type="time" name="daily_call_time" value="{{ old('daily_call_time', $plan->daily_call_time ? substr($plan->daily_call_time, 0, 5) : '') }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small tracking-widest uppercase">Render Deadline</label>
                                    <input type="date" name="render_deadline" value="{{ old('render_deadline', optional($plan->render_deadline)->format('Y-m-d')) }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary small tracking-widest uppercase">Release Target</label>
                                    <input type="date" name="release_target" value="{{ old('release_target', optional($plan->release_target)->format('Y-m-d')) }}" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2">
                                </div>
                            </div>
                        </div>

                        <div class="bg-black border border-secondary p-4">
                            <h5 class="text-white small tracking-widest uppercase mb-4 border-bottom border-secondary pb-2">Production Notes</h5>
                            <textarea name="production_notes" rows="10" class="form-control bg-transparent border border-secondary rounded-0 text-white p-3 font-monospace" placeholder="Crew notes, budget reminders, location planning, render batch notes...">{{ old('production_notes', $plan->production_notes) }}</textarea>

                            <button type="submit" class="btn btn-white bg-white text-black rounded-0 px-5 py-3 fw-bold tracking-widest uppercase mt-4 w-100">
                                Save Planning
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    @endif
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.18em; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    .form-control:focus, .form-select:focus {
        background-color: transparent;
        border-color: #fff !important;
        box-shadow: none;
        color: #fff;
    }
    .form-select option { background: #000; color: #fff; }
    .btn-white:hover { background: #ddd !important; }
</style>

@if($project && $plan)
<script>
async function generateStoryContent() {
    const button = document.getElementById('generate-story-button');
    const status = document.getElementById('generate-story-status');

    if (!confirm('Generate and replace the logline, story, outline, and scene breakdown fields?')) return;

    button.disabled = true;
    button.textContent = 'GENERATING...';
    status.textContent = 'AI is writing the story plan. This may take a minute...';

    try {
        const response = await fetch(@json(route('planning.generate-story')), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token()),
            },
            body: JSON.stringify({
                project_id: @json($project->id),
                logline: document.getElementById('logline').value,
            }),
        });
        const data = await response.json();

        if (!response.ok) throw new Error(data.message || 'Story generation failed.');

        ['logline', 'full_story', 'script_outline', 'scene_breakdown'].forEach((field) => {
            document.getElementById(field).value = data[field];
        });
        status.textContent = 'Story content generated. Review it, then click Save Planning.';
    } catch (error) {
        status.textContent = error.message;
    } finally {
        button.disabled = false;
        button.textContent = 'GENERATE WITH AI';
    }
}
</script>
@endif
@endsection
