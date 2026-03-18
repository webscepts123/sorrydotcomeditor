@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">SYSTEM / CORE</h6>
            <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">OPERATIONS</h2>
        </div>
        <div class="text-end">
            <span class="badge border border-secondary rounded-0 px-3 py-2 text-uppercase">Server: Contabo-Node-01</span>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card bg-black border border-secondary text-white rounded-0 h-100 shadow-sm">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between mb-3">
                        <h6 class="text-secondary small tracking-widest">ACTIVE PRODUCTION</h6>
                        <span class="text-success small">● LIVE</span>
                    </div>
                    <h3 class="fw-bold mb-1">VOID SHADOW</h3>
                    <p class="text-secondary small mb-4">Genre: Dark Thriller / Sci-Fi</p>
                    
                    <div class="mt-auto">
                        <div class="d-flex justify-content-between mb-1 small">
                            <span>TIMELINE PROGRESS</span>
                            <span>37.5 / 150 MIN</span>
                        </div>
                        <div class="progress bg-dark rounded-0" style="height: 2px;">
                            <div class="progress-bar bg-white" style="width: 25%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-black border border-secondary text-white rounded-0 h-100">
                <div class="card-body">
                    <h6 class="text-secondary small mb-4 tracking-widest">AI RENDER QUEUE</h6>
                    <div class="d-flex align-items-center mb-3">
                        <div class="spinner-grow spinner-grow-sm text-light me-3" role="status"></div>
                        <div class="small">
                            <div class="fw-bold">Scene 04 / Shot 12</div>
                            <div class="text-secondary" style="font-size: 0.7rem;">SEEDANCE 2.0 - EXTENDING CLIP...</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-0 opacity-50">
                        <div class="vr me-3" style="height: 20px; color: #333;"></div>
                        <div class="small text-secondary">Next: Scene 04 / Shot 13 (Queued)</div>
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
                            <div class="progress-bar bg-white" style="width: 65%"></div>
                        </div>
                        <span class="text-secondary" style="font-size: 0.65rem;">420GB / 800GB USED</span>
                    </div>
                    <div>
                        <label class="small text-secondary d-block mb-1">GPU CLUSTER LOAD</label>
                        <div class="progress bg-dark rounded-0" style="height: 4px;">
                            <div class="progress-bar bg-white" style="width: 12%"></div>
                        </div>
                        <span class="text-secondary" style="font-size: 0.65rem;">API RATE LIMIT: 12/100 (RPM)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card bg-black border border-secondary text-white rounded-0">
                <div class="card-header bg-transparent border-secondary py-3">
                    <h6 class="m-0 small tracking-widest">PRODUCTION TOOLS</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <button class="btn btn-outline-light w-100 rounded-0 py-3 small">
                                <i class="bi bi-camera-reels d-block mb-2"></i> NEW SHOT
                            </button>
                        </div>
                        <div class="col-6 col-md-3">
                            <button class="btn btn-outline-light w-100 rounded-0 py-3 small">
                                <i class="bi bi-person-bounding-box d-block mb-2"></i> SYNC FACE
                            </button>
                        </div>
                        <div class="col-6 col-md-3">
                            <button class="btn btn-outline-light w-100 rounded-0 py-3 small">
                                <i class="bi bi-music-note-beamed d-block mb-2"></i> GEN SCORE
                            </button>
                        </div>
                        <div class="col-6 col-md-3">
                            <button class="btn btn-outline-light w-100 rounded-0 py-3 small">
                                <i class="bi bi-file-earmark-pdf d-block mb-2"></i> SCRIPT
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-black border border-secondary text-white rounded-0">
                <div class="card-header bg-transparent border-secondary py-3">
                    <h6 class="m-0 small tracking-widest">COLLABORATORS</h6>
                </div>
                <ul class="list-group list-group-flush bg-transparent">
                    @foreach($editors as $editor)
                    <li class="list-group-item bg-transparent text-white border-secondary d-flex align-items-center py-3">
                        <div class="rounded-circle bg-white text-black text-center me-3" style="width: 30px; height: 30px; line-height: 30px; font-weight: bold; font-size: 0.7rem;">
                            {{ substr($editor->name, 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="small fw-bold">{{ strtoupper($editor->name) }}</div>
                            <div class="text-secondary" style="font-size: 0.65rem;">{{ strtoupper($editor->pivot->assigned_task ?? 'IDLE') }}</div>
                        </div>
                        <span class="badge bg-success rounded-circle p-1" style="width: 8px; height: 8px;"> </span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
    .card { transition: 0.3s ease; }
    .card:hover { border-color: #fff !important; transform: translateY(-5px); }
    .tracking-widest { letter-spacing: 0.15em; font-family: 'Syncopate'; }
</style>
@endsection