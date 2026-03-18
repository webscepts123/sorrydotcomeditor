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
                                <div class="text-secondary" style="font-size: 0.7rem;">SEEDANCE 2.0 - GENERATING CLIP...</div>
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
            <div class="card bg-black border border-secondary text-white rounded-0 h-100">
                <div class="card-body">
                    <h6 class="text-secondary small mb-3 tracking-widest">VDS RESOURCES</h6>
                    <div class="mb-3">
                        <label class="small text-secondary d-block mb-1">STORAGE (NVMe)</label>
                        <div class="progress bg-dark rounded-0" style="height: 4px;">
                            <div class="progress-bar bg-white" style="width: {{ $storagePercent }}%"></div>
                        </div>
                        <span class="text-secondary text-uppercase" style="font-size: 0.65rem;">{{ $usedGB }}GB / {{ $totalGB }}GB USED</span>
                    </div>
                    <div>
                        <label class="small text-secondary d-block mb-1">GPU CLUSTER LOAD</label>
                        <div class="progress bg-dark rounded-0" style="height: 4px;">
                            <div class="progress-bar bg-info" style="width: 5%"></div> </div>
                        <span class="text-secondary" style="font-size: 0.65rem;">API STATUS: ONLINE / CONNECTED</span>
                    </div>
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
@endsection