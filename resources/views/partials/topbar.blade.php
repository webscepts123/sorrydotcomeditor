<nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary py-3">
    <div class="container-fluid">
        <span class="navbar-text small text-uppercase tracking-widest text-secondary" style="letter-spacing: 2px;">
            System Status: <span class="text-success">Active</span> / {{ now()->format('Y.m.d') }}
        </span>
        <div class="ms-auto d-flex align-items-center">
            <a href="{{ isset($project) ? route('projects.editor', $project) : '#' }}" class="btn btn-outline-light btn-sm rounded-0 px-3 small">
                NEW CLIP +
            </a>        
        </div>
    </div>
</nav>