@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">STUDIO / ROSTER</h6>
            <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">EDITORS</h2>
        </div>
        <a href="{{ route('editors.create') }}" class="btn btn-outline-light rounded-0 px-4 small tracking-widest">
            ADD STAFF +
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover border-secondary align-middle">
            <thead class="text-secondary small tracking-widest">
                <tr>
                    <th class="border-secondary py-3">NAME</th>
                    <th class="border-secondary py-3">SPECIALIZATION</th>
                    <th class="border-secondary py-3">ACTIVE PROJECTS</th>
                    <th class="border-secondary py-3">STATUS</th>
                    <th class="border-secondary py-3 text-end">ACTIONS</th>
                </tr>
            </thead>
            <tbody class="border-secondary">
                @forelse($editors as $editor)
                <tr>
                    <td class="py-4">
                        <div class="d-flex align-items-center">
                            <img src="https://ui-avatars.com/api/?name={{ $editor->name }}&background=333&color=fff" class="rounded-circle me-3" width="35">
                            <span class="fw-bold">{{ strtoupper($editor->name) }}</span>
                        </div>
                    </td>
                    <td class="text-secondary small">{{ $editor->specialization ?? 'VFX / AI Prompting' }}</td>
                    <td>{{ $editor->projects_count }}</td>
                    <td>
                        <span class="badge border border-secondary text-secondary rounded-0 small uppercase">Available</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('editors.show', $editor) }}" class="btn btn-sm btn-link text-white text-decoration-none small">DOSSIER</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-secondary tracking-widest">
                        NO EDITORS REGISTERED IN THE VOID SYSTEM.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection