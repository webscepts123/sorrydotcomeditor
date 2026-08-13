@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">TIMELINE / SEQUENCE</h6>
            <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">SCENES</h2>
        </div>
        <a href="{{ route('scenes.create') }}" class="btn btn-white bg-white text-black rounded-0 px-4 py-2 fw-bold small tracking-widest uppercase transition-hover">
            ADD SCENE +
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-dark table-hover border-secondary align-middle">
            <thead class="text-secondary small tracking-widest">
                <tr>
                    <th class="border-secondary py-3">#</th>
                    <th class="border-secondary py-3">DESCRIPTION / SCRIPT</th>
                    <th class="border-secondary py-3">CLIPS</th>
                    <th class="border-secondary py-3">STATUS</th>
                    <th class="border-secondary py-3 text-end">ACTIONS</th>
                </tr>
            </thead>
            <tbody class="border-secondary">
                @forelse($scenes as $scene)
                <tr>
                    <td class="py-4 fw-bold">{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }}</td>
                    <td class="text-secondary small">{{ Str::limit($scene->script_segment, 100) }}</td>
                    <td>{{ $scene->video_clips_count }} / 4</td>
                    <td>
                        <span class="badge border border-{{ strtolower($scene->status) == 'ready' ? 'success' : 'warning' }} rounded-0 small uppercase">
                            {{ $scene->status }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <a href="{{ route('scenes.show', $scene) }}" class="btn btn-sm btn-outline-info rounded-0">VIEW</a>
                            <a href="{{ route('scenes.edit', $scene) }}" class="btn btn-sm btn-outline-light rounded-0">EDIT</a>
                            <form action="{{ route('scenes.destroy', $scene) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete scene #{{ str_pad($scene->order_index, 3, '0', STR_PAD_LEFT) }} and its clips permanently?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-0">DELETE</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-secondary py-5">No scenes found. Add the first scene to begin.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $scenes->links() }}</div>
</div>
@endsection
