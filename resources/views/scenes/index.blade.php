@extends('layouts.admin')

@section('content')
<div class="container-fluid scenes-page">
    <div class="d-flex justify-content-between align-items-end gap-3 flex-wrap mb-5 border-bottom border-secondary pb-4">
        <div>
            <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size:10px;">PRODUCTION / SEQUENCES</h6>
            <h2 class="fw-light mb-0" style="font-family:'Syncopate'; letter-spacing:4px;">SCENES</h2>
        </div>
        <a href="{{ route('scenes.create', $selectedProject ? ['project' => $selectedProject->id] : []) }}" class="btn btn-white bg-white text-black rounded-0 px-4 py-3 fw-bold small tracking-widest uppercase">Add Scene +</a>
    </div>

    @if($projects->isNotEmpty())
        <form method="GET" action="{{ route('scenes.index') }}" class="mb-4 project-filter">
            <label class="text-secondary d-block mb-1 tracking-widest uppercase" style="font-size:8px;">Choose Project</label>
            <select name="project_id" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2 tracking-widest" onchange="this.form.submit()">
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ $selectedProject?->id === $project->id ? 'selected' : '' }}>{{ strtoupper($project->title) }}</option>
                @endforeach
            </select>
        </form>
    @endif

    <div class="scene-grid">
        @forelse($scenes as $scene)
            <article class="scene-card bg-black border border-secondary p-3 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="badge bg-dark border border-secondary text-white rounded-0 tracking-widest">SEQ #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}</span>
                    <span class="badge border border-{{ strtolower($scene->status) === 'ready' ? 'success' : 'warning' }} text-{{ strtolower($scene->status) === 'ready' ? 'success' : 'warning' }} rounded-0 tracking-widest" style="font-size:8px;">{{ strtoupper($scene->status) }}</span>
                </div>

                <div class="scene-media ratio ratio-16x9 bg-dark border border-secondary mb-3">
                    @if($scene->video_path)
                        <video src="{{ asset('storage/' . $scene->video_path) }}" muted preload="metadata" class="scene-preview"></video>
                        <button type="button" class="scene-play btn btn-light rounded-circle position-absolute top-50 start-50 translate-middle" data-src="{{ asset('storage/' . $scene->video_path) }}" data-label="SEQ #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}"><i class="bi bi-play-fill fs-4"></i></button>
                    @else
                        <div class="d-flex flex-column justify-content-center align-items-center text-secondary"><i class="bi bi-camera-video-off fs-3"></i><small class="mt-2 tracking-widest">AWAITING RENDER</small></div>
                    @endif
                </div>

                <p class="text-white small fst-italic flex-grow-1">“{{ Str::limit($scene->script_segment, 120) }}”</p>
                @if($scene->characters->isNotEmpty())
                    <div class="d-flex flex-wrap gap-1 mb-3">
                        @foreach($scene->characters as $character)
                            <span class="badge border border-info text-info rounded-0" style="font-size:8px;">{{ strtoupper($character->name) }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="d-grid scene-actions gap-2 border-top border-secondary pt-3">
                    <a href="{{ route('scenes.show', $scene) }}" class="btn btn-sm btn-outline-info rounded-0 tracking-widest">VIEW</a>
                    <a href="{{ route('scenes.edit', $scene) }}" class="btn btn-sm btn-outline-light rounded-0 tracking-widest">EDIT</a>
                    <form action="{{ route('scenes.destroy', $scene) }}" method="POST" onsubmit="return confirm('Delete this scene and its generated assets permanently?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger rounded-0 tracking-widest w-100">DELETE</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="scene-empty border border-secondary text-center p-5">
                <i class="bi bi-film fs-1 text-secondary"></i>
                <h5 class="mt-3 tracking-widest">NO SCENES IN {{ strtoupper($selectedProject?->title ?? 'THIS PROJECT') }}</h5>
                <a href="{{ route('scenes.create', $selectedProject ? ['project' => $selectedProject->id] : []) }}" class="btn btn-outline-info rounded-0 mt-3 px-4">CREATE FIRST SCENE</a>
            </div>
        @endforelse
    </div>
    <div class="mt-4">{{ $scenes->links() }}</div>
</div>

<div class="modal fade" id="scenePlayerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content bg-black border-secondary rounded-0">
        <div class="modal-header border-secondary"><h5 id="scenePlayerLabel" class="modal-title small tracking-widest">SCENE PLAYER</h5><button class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-0"><div class="ratio ratio-16x9"><video id="scenePlayer" controls playsinline class="w-100 h-100 object-fit-contain bg-black"></video></div></div>
    </div></div>
</div>

<style>
.uppercase{text-transform:uppercase}.project-filter{max-width:420px}.form-select:focus{background:#000;border-color:#fff!important;box-shadow:none;color:#fff}.form-select option{background:#000;color:#fff}
.scene-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,320px),1fr));gap:24px}.scene-card{min-width:0;max-width:460px}.scene-media{overflow:hidden;position:relative}.scene-preview{position:absolute!important;inset:0;width:100%!important;height:100%!important;object-fit:cover}.scene-play{width:48px;height:48px;padding:0;z-index:3}.scene-actions{grid-template-columns:repeat(3,1fr)}.scene-empty{grid-column:1/-1;min-height:320px;display:flex;flex-direction:column;justify-content:center;align-items:center}
@media(max-width:575.98px){.scene-grid{grid-template-columns:1fr}.scene-card{max-width:none}.scene-actions{grid-template-columns:1fr}.scenes-page h2{font-size:1.5rem}}
</style>
<script>
document.querySelectorAll('.scene-play').forEach(button=>button.addEventListener('click',()=>{const player=document.getElementById('scenePlayer');player.src=button.dataset.src;document.getElementById('scenePlayerLabel').textContent=button.dataset.label+' / VIDEO PLAYER';bootstrap.Modal.getOrCreateInstance(document.getElementById('scenePlayerModal')).show();player.play().catch(()=>{});}));
document.getElementById('scenePlayerModal').addEventListener('hidden.bs.modal',()=>{const player=document.getElementById('scenePlayer');player.pause();player.removeAttribute('src');player.load();});
</script>
@endsection
