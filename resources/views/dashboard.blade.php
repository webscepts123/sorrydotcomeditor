@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">SYSTEM / CORE</h6>
            <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">OPERATIONS</h2>
        </div>
        <div class="text-end">
            <span class="badge border border-secondary rounded-0 px-3 py-2 text-uppercase">Server: {{ request()->getHost() }}</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card bg-black border border-secondary text-white rounded-0 h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="text-secondary small tracking-widest">ACTIVE PRODUCTION</h6>
                        <span class="text-{{ $activeProject ? 'success' : 'secondary' }} small">● {{ $activeProject ? 'LIVE' : 'IDLE' }}</span>
                    </div>
                    
                    @if($activeProject)
                        <h3 class="fw-bold mb-1 text-uppercase">{{ $activeProject->title }}</h3>
                        <p class="text-secondary small mb-4">Style: {{ strtoupper($activeProject->style_preset ?? 'Unassigned') }}</p>

                        <div class="d-grid gap-2 mb-4">
                            <a href="{{ route('projects.videoeditor', $activeProject) }}" class="btn btn-outline-info rounded-0 py-2 small tracking-widest text-decoration-none">
                                <i class="bi bi-film me-2"></i> OPEN VIDEO EDITOR
                            </a>
                            <a href="{{ route('projects.editor', $activeProject) }}" class="btn btn-outline-light rounded-0 py-2 small tracking-widest text-decoration-none">
                                <i class="bi bi-camera-reels me-2"></i> OPEN AI EDITOR
                            </a>
                        </div>
                        
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between mb-1 small">
                                <span>TIMELINE PROGRESS</span>
                                <span>{{ number_format($totalMinutes, 1) }} / 150 MIN</span>
                            </div>
                            <div class="progress bg-dark rounded-0" style="height: 2px;">
                                <div class="progress-bar bg-white" style="width: {{ $progressPercent }}%"></div>
                            </div>
                        </div>
                    @else
                        <h5 class="fw-light text-secondary mt-4">NO ACTIVE PROJECT</h5>
                        <a href="{{ route('projects.create') }}" class="btn btn-outline-secondary btn-sm mt-3 rounded-0 tracking-widest">INITIALIZE +</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-black border border-secondary text-white rounded-0 h-100">
                <div class="card-body">
                    <h6 class="text-secondary small mb-4 tracking-widest">AI RENDER QUEUE</h6>
                    
                    @if($renderingScene)
                        <div class="d-flex align-items-center mb-3">
                            <div class="spinner-grow spinner-grow-sm text-info me-3" role="status"></div>
                            <div class="small">
                                <div class="fw-bold text-uppercase">SEQ {{ str_pad($renderingScene->order_index, 3, '0', STR_PAD_LEFT) }}</div>
                                <div class="text-secondary" style="font-size: 0.7rem;">WAN / COMFYUI - GENERATING CLIP...</div>
                            </div>
                        </div>
                    @else
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-check2-circle text-secondary fs-4 me-3"></i>
                            <div class="small text-secondary">
                                <div class="fw-bold">QUEUE EMPTY</div>
                                <div style="font-size: 0.7rem;">ENGINE STANDBY</div>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex align-items-center mb-0 opacity-50 mt-4">
                        <div class="vr me-3" style="height: 20px; color: #333;"></div>
                        <div class="small text-secondary text-uppercase">
                            Next: {{ $nextScene ? 'SEQ ' . str_pad($nextScene->order_index, 3, '0', STR_PAD_LEFT) . ' (Queued)' : 'None' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div id="runpod-resource-card" data-status-url="{{ route('dashboard.runpod-status') }}" class="card bg-black border border-secondary text-white rounded-0 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                        <h6 class="text-secondary small mb-0 tracking-widest">RUNPOD RESOURCES</h6>
                        <span id="runpod-online" class="badge rounded-0 border border-{{ $runpod['online'] ? 'success text-success' : 'danger text-danger' }}">
                            {{ $runpod['online'] ? 'ONLINE' : 'OFFLINE' }}
                        </span>
                    </div>
                    <div class="small mb-3">
                        <div id="runpod-name" class="fw-bold text-break">{{ $runpod['pod_name'] ?: 'RUNPOD' }}</div>
                        <div id="runpod-id" class="text-secondary text-break" style="font-size: .65rem;">{{ $runpod['pod_id'] ?: 'POD ID NOT CONFIGURED' }}</div>
                        <div id="runpod-gpu" class="text-secondary mt-1" style="font-size: .65rem;">
                            {{ $runpod['gpu_name'] ?: 'GPU unavailable' }}
                            @if($runpod['hourly_cost'] !== null) · ${{ number_format((float) $runpod['hourly_cost'], 2) }}/HR @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="small text-secondary d-block mb-1">STORAGE (NVMe)</label>
                        <div class="progress bg-dark rounded-0" style="height: 4px;">
                            <div class="progress-bar bg-white" style="width: {{ $storagePercent }}%"></div>
                        </div>
                        <span class="text-secondary text-uppercase" style="font-size: 0.65rem;">{{ $usedGB }}GB / {{ $totalGB }}GB USED</span>
                    </div>
                    <div class="mb-3">
                        <label class="small text-secondary d-block mb-1">GPU VRAM</label>
                        <div class="progress bg-dark rounded-0" style="height: 4px;">
                            <div id="runpod-vram-bar" class="progress-bar bg-info" style="width: {{ $runpod['vram_percent'] ?? 0 }}%"></div>
                        </div>
                        <span id="runpod-vram-text" class="text-secondary" style="font-size: 0.65rem;">
                            @if($runpod['vram_total_gb'])
                                {{ $runpod['vram_used_gb'] }}GB / {{ $runpod['vram_total_gb'] }}GB ({{ $runpod['vram_percent'] }}%)
                            @else
                                USAGE UNAVAILABLE
                            @endif
                        </span>
                    </div>
                    <div class="mb-2">
                        <label class="small text-secondary d-block mb-1">SYSTEM RAM</label>
                        <div class="progress bg-dark rounded-0" style="height: 4px;">
                            <div id="runpod-ram-bar" class="progress-bar bg-white" style="width: {{ $runpod['ram_percent'] ?? 0 }}%"></div>
                        </div>
                        <span id="runpod-ram-text" class="text-secondary" style="font-size: 0.65rem;">
                            @if($runpod['ram_total_gb'])
                                {{ $runpod['ram_used_gb'] }}GB / {{ $runpod['ram_total_gb'] }}GB ({{ $runpod['ram_percent'] }}%)
                            @else
                                USAGE UNAVAILABLE
                            @endif
                        </span>
                    </div>
                    <div id="runpod-runtime" class="text-secondary" style="font-size: .65rem;">
                        COMFYUI {{ $runpod['comfyui_version'] ?: 'UNAVAILABLE' }} · RUNNING {{ $runpod['running_jobs'] }} · QUEUED {{ $runpod['pending_jobs'] }}
                    </div>
                    <div id="runpod-updated" class="text-secondary mt-1" style="font-size: .6rem;">LIVE AUTO-REFRESH</div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card bg-black border border-secondary text-white rounded-0 h-100">
                <div class="card-header bg-transparent border-secondary py-3">
                    <h6 class="m-0 small tracking-widest">PRODUCTION TOOLS</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <a href="{{ route('scenes.create') }}" class="btn btn-outline-light w-100 rounded-0 py-3 small text-decoration-none">
                                <i class="bi bi-camera-reels d-block mb-2 fs-4"></i> NEW SHOT
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            @if($activeProject)
                                <a href="{{ route('projects.videoeditor', $activeProject) }}" class="btn btn-outline-info w-100 rounded-0 py-3 small text-decoration-none">
                                    <i class="bi bi-film d-block mb-2 fs-4"></i> VIDEO EDITOR
                                </a>
                            @else
                                <button type="button" class="btn btn-outline-secondary w-100 rounded-0 py-3 small" disabled>
                                    <i class="bi bi-film d-block mb-2 fs-4"></i> VIDEO EDITOR
                                </button>
                            @endif
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('tools.sync-face') }}" class="btn btn-outline-light w-100 rounded-0 py-3 small text-decoration-none">
                                <i class="bi bi-person-bounding-box d-block mb-2 fs-4"></i> SYNC FACE
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('tools.gen-score') }}" class="btn btn-outline-light w-100 rounded-0 py-3 small text-decoration-none">
                                <i class="bi bi-music-note-beamed d-block mb-2 fs-4"></i> GEN SCORE
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('tools.script') }}" class="btn btn-outline-light w-100 rounded-0 py-3 small text-decoration-none">
                                <i class="bi bi-file-earmark-pdf d-block mb-2 fs-4"></i> SCRIPT
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-black border border-secondary text-white rounded-0 h-100">
                <div class="card-header bg-transparent border-secondary py-3">
                    <h6 class="m-0 small tracking-widest">TEAM / CAST</h6>
                </div>
                <ul class="list-group list-group-flush bg-transparent">
                    @forelse($editors as $editor)
                    <li class="list-group-item bg-transparent text-white border-secondary d-flex align-items-center py-3">
                        <div class="rounded-circle bg-white text-black text-center me-3" style="width: 30px; height: 30px; line-height: 30px; font-weight: bold; font-size: 0.7rem;">
                            {{ substr($editor->name, 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="small fw-bold">{{ strtoupper($editor->name) }}</div>
                            <div class="text-secondary" style="font-size: 0.65rem;">{{ strtoupper($editor->role ?? 'SYSTEM EDITOR') }}</div>
                        </div>
                        <span class="badge bg-success rounded-circle p-1" style="width: 8px; height: 8px;"> </span>
                    </li>
                    @empty
                    <li class="list-group-item bg-transparent text-secondary border-0 py-4 text-center small tracking-widest uppercase">
                        NO EDITORS ASSIGNED
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .card { transition: 0.3s ease; }
    .card:hover { border-color: #fff !important; transform: translateY(-3px); }
    .tracking-widest { letter-spacing: 0.15em; font-family: 'Syncopate', sans-serif; }
    .btn-outline-light:hover { background-color: #fff; color: #000; }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const card = document.getElementById('runpod-resource-card');
    if (!card) return;

    const byId = (id) => document.getElementById(id);
    const value = (number) => Number.isFinite(Number(number)) ? Number(number) : null;
    const usage = (used, total, percent) => {
        const u = value(used), t = value(total), p = value(percent);
        return u !== null && t !== null && p !== null ? `${u}GB / ${t}GB (${p}%)` : 'USAGE UNAVAILABLE';
    };
    let loading = false;

    async function refreshRunPod() {
        if (loading || document.hidden) return;
        loading = true;
        try {
            const response = await fetch(card.dataset.statusUrl, {
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();
            const badge = byId('runpod-online');
            badge.textContent = data.online ? 'ONLINE' : 'OFFLINE';
            badge.className = `badge rounded-0 border border-${data.online ? 'success text-success' : 'danger text-danger'}`;
            byId('runpod-name').textContent = data.pod_name || 'RUNPOD';
            byId('runpod-id').textContent = data.pod_id || 'POD ID NOT CONFIGURED';
            byId('runpod-gpu').textContent = `${data.gpu_name || 'GPU unavailable'}${data.hourly_cost !== null ? ` · $${Number(data.hourly_cost).toFixed(2)}/HR` : ''}`;
            byId('runpod-vram-bar').style.width = `${value(data.vram_percent) ?? 0}%`;
            byId('runpod-vram-text').textContent = usage(data.vram_used_gb, data.vram_total_gb, data.vram_percent);
            byId('runpod-ram-bar').style.width = `${value(data.ram_percent) ?? 0}%`;
            byId('runpod-ram-text').textContent = usage(data.ram_used_gb, data.ram_total_gb, data.ram_percent);
            byId('runpod-runtime').textContent = `COMFYUI ${data.comfyui_version || 'UNAVAILABLE'} · RUNNING ${data.running_jobs || 0} · QUEUED ${data.pending_jobs || 0}`;
            byId('runpod-updated').textContent = `UPDATED ${new Date(data.checked_at).toLocaleTimeString()} · AUTO-REFRESH 5S`;
        } catch (error) {
            byId('runpod-online').textContent = 'CONNECTION ERROR';
            byId('runpod-online').className = 'badge rounded-0 border border-danger text-danger';
            byId('runpod-updated').textContent = 'LIVE UPDATE FAILED · RETRYING';
        } finally {
            loading = false;
        }
    }

    refreshRunPod();
    setInterval(refreshRunPod, 5000);
    document.addEventListener('visibilitychange', refreshRunPod);
});
</script>
@endsection
