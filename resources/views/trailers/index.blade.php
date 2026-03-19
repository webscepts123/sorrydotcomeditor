@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5 border-bottom border-secondary pb-4">
        <div>
            <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">PRODUCTION / PROMO</h6>
            <h2 class="fw-light mb-0" style="font-family: 'Syncopate'; letter-spacing: 4px;">TRAILERS</h2>
        </div>
        <a href="{{ route('trailers.create') }}" class="btn btn-outline-light rounded-0 px-4 small uppercase tracking-widest">
            INITIALIZE ASSET +
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-dark bg-black border border-success text-success rounded-0 mb-4 p-3 shadow-sm font-monospace small uppercase tracking-widest">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-dark table-hover border-secondary align-middle">
            <thead class="text-secondary small tracking-widest" style="font-size: 10px;">
                <tr>
                    <th class="border-secondary py-3">ASSET DESIGNATION</th>
                    <th class="border-secondary py-3">TARGET PRODUCTION</th>
                    <th class="border-secondary py-3 text-center">DURATION</th>
                    <th class="border-secondary py-3">STATUS</th>
                    <th class="border-secondary py-3 text-end">MANAGEMENT</th>
                </tr>
            </thead>
            <tbody class="border-secondary">
                @forelse($trailers as $trailer)
                <tr class="transition-hover">
                    <td class="py-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-dark border border-secondary d-flex justify-content-center align-items-center me-3" style="width: 45px; height: 30px;">
                                <i class="bi bi-film text-secondary" style="font-size: 12px;"></i>
                            </div>
                            <div>
                                <span class="fw-bold d-block text-white uppercase tracking-widest" style="font-size: 12px;">{{ $trailer->title }}</span>
                                <small class="text-secondary font-monospace" style="font-size: 9px;">ID: TRL_{{ str_pad($trailer->id, 4, '0', STR_PAD_LEFT) }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="text-secondary small uppercase tracking-widest" style="font-size: 11px;">
                        {{ $trailer->project->title ?? 'UNASSIGNED' }}
                    </td>
                    <td class="text-center text-info font-monospace" style="font-size: 12px;">
                        {{ $trailer->duration ?? '00:00' }}
                    </td>
                    <td>
                        @switch($trailer->status)
                            @case('Published')
                                <span class="badge border border-success text-success rounded-0 small uppercase px-2 py-1" style="font-size: 9px;">Published</span>
                                @break
                            @case('Ready')
                                <span class="badge border border-info text-info rounded-0 small uppercase px-2 py-1" style="font-size: 9px;">Ready</span>
                                @break
                            @case('Processing')
                                <span class="badge border border-warning text-warning rounded-0 small uppercase px-2 py-1" style="font-size: 9px;">Processing <span class="spinner-grow spinner-grow-sm ms-1" style="width: 0.5rem; height: 0.5rem;"></span></span>
                                @break
                            @default
                                <span class="badge border border-secondary text-secondary rounded-0 small uppercase px-2 py-1" style="font-size: 9px;">Draft</span>
                        @endswitch
                    </td>
                    <td class="text-end">
                        <div class="dropdown">
                            <button class="btn btn-link text-secondary p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark rounded-0 border-secondary shadow-lg">
                                <li>
                                    <a class="dropdown-item small uppercase tracking-widest py-2" href="{{ route('trailers.show', $trailer) }}">
                                        <i class="bi bi-play-circle me-2"></i> Preview Asset
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item small uppercase tracking-widest py-2 text-info" href="{{ route('trailers.edit', $trailer) }}">
                                        <i class="bi bi-sliders me-2"></i> Reconfigure
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <form action="{{ route('trailers.destroy', $trailer) }}" method="POST" onsubmit="return confirm('CRITICAL: Permanently delete this trailer asset?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item small uppercase tracking-widest py-2 text-danger">
                                            <i class="bi bi-trash3 me-2"></i> Purge Asset
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
                        <i class="bi bi-camera-reels d-block mb-2 fs-4"></i>
                        NO PROMOTIONAL ASSETS INITIALIZED IN THE SYSTEM.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($trailers->hasPages())
        <div class="d-flex justify-content-center mt-4 bg-black">
            {{ $trailers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.15em; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    .transition-hover { transition: background-color 0.2s ease; }
    .transition-hover:hover { background-color: #0a0a0a !important; }
    .dropdown-item:hover { background-color: #fff !important; color: #000 !important; }
    
    /* Dark Mode Pagination Overrides */
    .pagination .page-link { background-color: #000; border-color: #444; color: #fff; border-radius: 0; }
    .pagination .page-item.active .page-link { background-color: #fff; border-color: #fff; color: #000; }
    .pagination .page-link:hover { background-color: #222; color: #fff; }
</style>
@endsection