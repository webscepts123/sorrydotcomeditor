@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5 border-bottom border-secondary pb-4">
        <div>
            <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">STUDIO / ROSTER</h6>
            <h2 class="fw-light mb-0" style="font-family: 'Syncopate'; letter-spacing: 4px;">EDITORS</h2>
        </div>
        <a href="{{ route('editors.create') }}" class="btn btn-outline-light rounded-0 px-4 small uppercase tracking-widest">
            ADD STAFF +
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover border-secondary align-middle">
            <thead class="text-secondary small tracking-widest">
                <tr>
                    <th class="border-secondary py-3">NAME / IDENTIFIER</th>
                    <th class="border-secondary py-3">SPECIALIZATION</th>
                    <th class="border-secondary py-3 text-center">ACTIVE PROJECTS</th>
                    <th class="border-secondary py-3">STATUS</th>
                    <th class="border-secondary py-3 text-end">MANAGEMENT</th>
                </tr>
            </thead>
            <tbody class="border-secondary">
                @forelse($editors as $editor)
                <tr class="transition-hover">
                    <td class="py-4">
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($editor->name) }}&background=111&color=fff&bold=true" class="rounded-0 border border-secondary me-3" width="35">
                            <div>
                                <span class="fw-bold d-block text-white">{{ strtoupper($editor->name) }}</span>
                                <small class="text-secondary font-monospace" style="font-size: 9px;">{{ $editor->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="text-secondary small uppercase tracking-widest" style="font-size: 10px;">
                        <i class="bi bi-cpu-fill me-1 text-info"></i> {{ $editor->role ?? 'VFX / AI Prompting' }}
                    </td>
                    <td class="text-center">
                        <span class="badge bg-dark border border-secondary rounded-0 font-monospace">{{ $editor->projects_count ?? 0 }}</span>
                    </td>
                    <td>
                        <span class="badge border border-success text-success rounded-0 small uppercase px-2 py-1" style="font-size: 9px;">Active</span>
                    </td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark rounded-0 border-secondary shadow-lg">
                                <li>
                                    <a class="dropdown-item small uppercase tracking-widest py-2" href="{{ route('editors.show', $editor) }}">
                                        <i class="bi bi-eye me-2"></i> Dossier
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item small uppercase tracking-widest py-2 text-info" href="{{ route('editors.edit', $editor) }}">
                                        <i class="bi bi-pencil-square me-2"></i> Edit Identity
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <form action="{{ route('editors.destroy', $editor) }}" method="POST" onsubmit="return confirm('CRITICAL: Terminate this staff record?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item small uppercase tracking-widest py-2 text-danger">
                                            <i class="bi bi-trash3 me-2"></i> Terminate
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-secondary tracking-widest small">
                        NO EDITORS REGISTERED IN THE VOID SYSTEM.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.15em; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    .transition-hover { transition: all 0.2s ease; }
    .transition-hover:hover { background-color: #0a0a0a !important; }
    .dropdown-item:hover { background-color: #fff !important; color: #000 !important; }
</style>
@endsection