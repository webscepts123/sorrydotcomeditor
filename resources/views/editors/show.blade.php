@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4 d-flex justify-content-between align-items-end">
        <div>
            <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">STUDIO / PERSONNEL</h6>
            <h2 class="fw-light mb-0" style="font-family: 'Syncopate'; letter-spacing: 4px;">STAFF DOSSIER</h2>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('editors.edit', $editor) }}" class="btn btn-outline-info rounded-0 px-4 small uppercase tracking-widest">
                <i class="bi bi-pencil-square me-2"></i> Edit Identity
            </a>
            <a href="{{ route('editors.index') }}" class="btn btn-outline-secondary rounded-0 px-4 small uppercase tracking-widest">
                <i class="bi bi-arrow-left me-2"></i> Back to Roster
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="bg-black border border-secondary p-5 text-center shadow-sm">
                <div class="position-relative d-inline-block mb-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($editor->name) }}&background=111&color=fff&size=150&bold=true" 
                         class="rounded-0 border border-secondary p-1" width="140">
                    <div class="position-absolute bottom-0 end-0 bg-success border border-black rounded-circle" style="width: 15px; height: 15px;"></div>
                </div>
                
                <h4 class="text-white uppercase tracking-widest mb-1" style="letter-spacing: 2px;">{{ $editor->name }}</h4>
                <p class="text-info font-monospace small mb-4">{{ strtoupper($editor->role ?? 'VFX / AI Prompting') }}</p>
                
                <div class="text-start border-top border-secondary pt-4 mt-2">
                    <div class="mb-3">
                        <label class="text-secondary small uppercase tracking-widest d-block mb-1" style="font-size: 9px;">System ID</label>
                        <p class="text-white font-monospace small mb-0">USR_{{ str_pad($editor->id, 5, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-secondary small uppercase tracking-widest d-block mb-1" style="font-size: 9px;">Contact Channel</label>
                        <p class="text-white small mb-0">{{ $editor->email }}</p>
                    </div>
                    
                    <div class="mb-0">
                        <label class="text-secondary small uppercase tracking-widest d-block mb-1" style="font-size: 9px;">Security Clearance</label>
                        <p class="text-warning small mb-0">
                            <i class="bi bi-shield-lock-fill me-1"></i> Level 4 Access
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="bg-black border border-secondary p-4 h-100 shadow-sm">
                <h6 class="text-white uppercase tracking-widest mb-4 border-bottom border-secondary pb-3">
                    <i class="bi bi-layers-half me-2"></i> Active Production Assignments
                </h6>
                
                <div class="table-responsive">
                    <table class="table table-dark border-secondary align-middle">
                        <thead class="text-secondary small uppercase tracking-widest" style="font-size: 9px;">
                            <tr>
                                <th class="border-secondary py-3">Production Title</th>
                                <th class="border-secondary py-3">Assigned Task</th>
                                <th class="border-secondary py-3 text-end">Commission Date</th>
                            </tr>
                        </thead>
                        <tbody class="border-secondary">
                            @forelse($editor->projects as $project)
                            <tr>
                                <td class="py-3">
                                    <a href="{{ route('projects.show', $project) }}" class="text-white text-decoration-none fw-bold hover-info transition-all">
                                        {{ strtoupper($project->title) }}
                                    </a>
                                </td>
                                <td class="text-secondary font-monospace italic">
                                    {{ $project->pivot->assigned_task ?? 'General VFX Orchestration' }}
                                </td>
                                <td class="text-end text-secondary font-monospace" style="font-size: 11px;">
                                    {{ $project->pivot->created_at ? $project->pivot->created_at->format('Y.m.d') : '2026.03.18' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-secondary italic">
                                    <i class="bi bi-info-circle d-block mb-2 fs-4"></i>
                                    NO ACTIVE PRODUCTION ASSIGNMENTS FOUND IN DATABASE.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 pt-3 border-top border-secondary">
                    <h6 class="text-secondary uppercase tracking-widest mb-3" style="font-size: 9px;">Personnel Meta-Log</h6>
                    <div class="bg-dark p-3 rounded-0 font-monospace text-secondary" style="font-size: 10px; line-height: 1.6;">
                        <div>[SYSTEM_STAMP]: Record Created {{ $editor->created_at }}</div>
                        <div>[LAST_AUTH]: Node Active @ 127.0.0.1 (MacBook Pro Proxy)</div>
                        <div>[PERMISSION_STRING]: vfx.render | audio.mix | timeline.edit</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.15em; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    .italic { font-style: italic; }
    .hover-info:hover { color: #0dcaf0 !important; }
    .transition-all { transition: all 0.2s ease; }
</style>
@endsection