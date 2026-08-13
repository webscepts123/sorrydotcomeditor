@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container-fluid bg-black min-vh-100 p-0">
    <div class="p-4 border-bottom border-secondary bg-dark d-flex flex-wrap gap-3 justify-content-between align-items-center">
        <div>
            <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">PRODUCTION TOOLS / ENGINE</h6>
            <h2 class="fw-light mb-0" style="font-family: 'Syncopate'; letter-spacing: 4px;">SCRIPT PROCESSOR</h2>
        </div>

        <div class="d-flex flex-wrap gap-3">
            <form method="GET" action="{{ route('tools.script') }}">
                <select name="project_id"
                        class="form-select bg-black border-secondary text-white rounded-0 small tracking-widest uppercase"
                        style="width: 280px; font-size: 12px;"
                        onchange="this.form.submit()">
                    @forelse($projects as $project)
                        <option value="{{ $project->id }}" @selected($selectedProject?->id === $project->id)>
                            {{ $project->title }}
                        </option>
                    @empty
                        <option value="">NO PROJECTS FOUND</option>
                    @endforelse
                </select>
            </form>

            <button form="script-parser-form"
                    type="submit"
                    class="btn btn-outline-info rounded-0 px-4 py-2 small tracking-widest uppercase fw-bold transition-hover"
                    @disabled(! $selectedProject)>
                <i class="bi bi-cpu me-2"></i> PARSE TO SCENES
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-0 border-0 mb-0 py-2 px-4 small tracking-widest uppercase">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger rounded-0 border-0 mb-0 py-2 px-4 small tracking-widest uppercase">{{ session('error') }}</div>
    @endif

    <div class="border-bottom border-secondary bg-black d-flex text-center">
        <div class="flex-fill p-2 border-end border-secondary">
            <span class="text-secondary small tracking-widest uppercase d-block" style="font-size: 9px;">Est. Word Count</span>
            <span class="text-white fw-bold font-monospace">{{ number_format($wordCount) }}</span>
        </div>
        <div class="flex-fill p-2 border-end border-secondary">
            <span class="text-secondary small tracking-widest uppercase d-block" style="font-size: 9px;">Detected Scenes</span>
            <span class="text-info fw-bold font-monospace">{{ number_format($detectedSceneCount) }}</span>
        </div>
        <div class="flex-fill p-2">
            <span class="text-secondary small tracking-widest uppercase d-block" style="font-size: 9px;">Est. Screen Time</span>
            <span class="text-white fw-bold font-monospace">{{ $estimatedScreenTime }}</span>
        </div>
    </div>

    <div class="row g-0" style="height: calc(100vh - 165px);">
        <div class="col-md-7 border-end border-secondary d-flex flex-column h-100">
            <div class="bg-dark border-bottom border-secondary p-2 d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-0 uppercase tracking-widest editor-btn" data-insert="INT. LOCATION - DAY&#10;&#10;">SCENE</button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-0 uppercase tracking-widest editor-btn" data-insert="Action description here.&#10;">ACTION</button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-0 uppercase tracking-widest editor-btn" data-insert="CHARACTER NAME&#10;">CHARACTER</button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-0 uppercase tracking-widest editor-btn" data-insert="    Dialogue line here.&#10;">DIALOGUE</button>
                <div class="vr bg-secondary mx-2"></div>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-0" id="auto-format-btn">
                    <i class="bi bi-magic"></i> Auto-Format
                </button>
            </div>

            <form id="script-parser-form"
                  method="POST"
                  action="{{ route('tools.script.parse') }}"
                  class="flex-grow-1 d-flex flex-column">
                @csrf
                <input type="hidden" name="project_id" value="{{ $selectedProject?->id }}">

                <div class="flex-grow-1 p-4 bg-black position-relative">
                    @if($selectedProject)
                        <textarea id="script-textarea"
                                  name="script_text"
                                  class="form-control bg-transparent border-0 text-white rounded-0 w-100 h-100 script-font"
                                  style="resize: none; outline: none; box-shadow: none;"
                                  placeholder="Write or paste the full script here. Use headings like INT. COLOMBO STREET - NIGHT or SCENE 001 so the parser can create timeline scenes.">{{ old('script_text', $scriptText) }}</textarea>
                    @else
                        <div class="h-100 d-flex align-items-center justify-content-center text-secondary tracking-widest uppercase">
                            Create a project first, then return to the script processor.
                        </div>
                    @endif
                </div>
            </form>
        </div>

        <div class="col-md-5 bg-dark d-flex flex-column h-100 overflow-auto">
            <div class="p-3 border-bottom border-secondary bg-black sticky-top">
                <h6 class="text-white small tracking-widest uppercase mb-0"><i class="bi bi-radar me-2 text-info"></i> Extraction Log</h6>
            </div>

            <div class="p-4">
                <div class="mb-5">
                    <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-3">Detected Cast</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($detectedCast as $character)
                            <span class="badge border border-secondary bg-black text-white rounded-0 p-2">
                                <i class="bi bi-person text-info me-1"></i> {{ $character->name }}
                            </span>
                        @empty
                            <span class="text-secondary small">No project characters detected in this script yet.</span>
                        @endforelse
                    </div>
                </div>

                <div class="mb-5">
                    <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-3">Identified Locations</h6>
                    <ul class="list-unstyled mb-0">
                        @forelse($detectedLocations as $location)
                            <li class="text-white small mb-2"><i class="bi bi-geo-alt text-secondary me-2"></i> {{ $location }}</li>
                        @empty
                            <li class="text-secondary small">Scene headings with INT. or EXT. will appear here.</li>
                        @endforelse
                    </ul>
                </div>

                <div>
                    <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-3">Pending Clip Prompts</h6>

                    @forelse($clipPrompts as $clip)
                        <div class="p-3 border border-secondary bg-black mb-2 transition-hover">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge bg-secondary rounded-0 text-black fw-bold" style="font-size: 10px;">{{ $clip['label'] }}</span>
                                    <span class="badge border border-secondary bg-transparent text-secondary rounded-0" style="font-size: 10px;">{{ $clip['status'] }}</span>
                                </div>
                                <span class="text-secondary small font-monospace">{{ $clip['duration'] }}</span>
                            </div>
                            <p class="text-white small italic mb-0">"{{ \Illuminate\Support\Str::limit($clip['text'], 220) }}"</p>
                        </div>
                    @empty
                        <div class="p-3 border border-secondary bg-black text-secondary small">
                            Parsed scene prompts will appear after you add script text.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .tracking-widest { letter-spacing: 0.2em; }
    .uppercase { text-transform: uppercase; }
    .italic { font-style: italic; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    .script-font {
        font-family: 'Courier Prime', 'Courier New', Courier, monospace;
        font-size: 14px;
        line-height: 1.6;
    }
    .editor-btn { font-size: 10px; }
    .editor-btn:hover { background: #fff; color: #000; }
    .transition-hover:hover { border-color: #fff !important; cursor: pointer; }
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #0a0a0a; }
    ::-webkit-scrollbar-thumb { background: #333; }
    ::-webkit-scrollbar-thumb:hover { background: #555; }
</style>

<script>
    const scriptTextarea = document.getElementById('script-textarea');

    document.querySelectorAll('[data-insert]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!scriptTextarea) return;

            const insertText = button.dataset.insert;
            const start = scriptTextarea.selectionStart;
            const end = scriptTextarea.selectionEnd;
            const current = scriptTextarea.value;

            scriptTextarea.value = current.slice(0, start) + insertText + current.slice(end);
            scriptTextarea.focus();
            scriptTextarea.selectionStart = scriptTextarea.selectionEnd = start + insertText.length;
        });
    });

    document.getElementById('auto-format-btn')?.addEventListener('click', () => {
        if (!scriptTextarea) return;

        scriptTextarea.value = scriptTextarea.value
            .replace(/\r\n/g, '\n')
            .replace(/\n{3,}/g, '\n\n')
            .split('\n')
            .map((line) => line.trimEnd())
            .join('\n')
            .trim();
    });
</script>
@endsection
