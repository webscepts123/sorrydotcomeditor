<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-black vh-100 border-end border-secondary" style="width: 280px;">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4 fw-bold shadow-sm" style="font-family: 'Syncopate'; letter-spacing: 4px;">
            VOID<span class="text-secondary border px-1 ms-1" style="font-size: 14px;">S</span>
        </span>
    </a>
    <hr class="border-secondary">
    
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-2 tracking-widest small">
                DASHBOARD
            </a>
        </li>
        <li>
            <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.*') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-2 tracking-widest small">
                PROJECTS
            </a>
        </li>
        <li>
            @php $activeSidebarProject = \App\Models\Project::latest('updated_at')->first(); @endphp
            <a href="{{ $activeSidebarProject ? route('projects.timeline', $activeSidebarProject->id) : route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.timeline') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-2 tracking-widest small">
                TIMELINE
            </a>
        </li>
        <li>
            <a href="{{ route('scenes.index') }}" class="nav-link {{ request()->routeIs('scenes.*') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-2 tracking-widest small">
                SCENES
            </a>
        </li>
        <li>
            <a href="{{ route('characters.index') }}" class="nav-link {{ request()->routeIs('characters.*') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-2 tracking-widest small">
                CHARACTERS
            </a>
        </li>
        <li>
            <a href="{{ route('editors.index') }}" class="nav-link {{ request()->routeIs('editors.*') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-2 tracking-widest small">
                EDITORS
            </a>        
        </li>
        <li>
            <a href="{{ route('soundtracks.index') }}" class="nav-link {{ request()->routeIs('soundtracks.*') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-4 tracking-widest small">
                SOUNDTRACKS
            </a>
        </li>
       

        <li class="nav-item mb-2 border border-secondary bg-dark">
            <a href="#productionTools" data-bs-toggle="collapse" aria-expanded="false" class="nav-link text-white rounded-0 d-flex justify-content-between align-items-center tracking-widest small">
                <span>PRODUCTION TOOLS</span>
                <i class="bi bi-chevron-down" style="font-size: 12px;"></i>
            </a>
            <div class="collapse" id="productionTools">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-2 small ms-3 border-start border-secondary ps-3 mt-2">
                    <li><a href="{{ route('scenes.create') }}" class="text-secondary text-decoration-none rounded-0 py-2 d-block tool-hover tracking-widest" style="font-size: 10px;">NEW SHOT</a></li>
                    <li><a href="{{ route('tools.sync-face') }}" class="text-secondary text-decoration-none rounded-0 py-2 d-block tool-hover tracking-widest" style="font-size: 10px;">SYNC FACE</a></li>
                    <li><a href="{{ route('tools.gen-score') }}" class="text-secondary text-decoration-none rounded-0 py-2 d-block tool-hover tracking-widest" style="font-size: 10px;">GEN SCORE</a></li>
                    <li><a href="{{ route('tools.script') }}" class="text-secondary text-decoration-none rounded-0 py-2 d-block tool-hover tracking-widest" style="font-size: 10px;">SCRIPT</a></li>
                </ul>
            </div>
        </li>

        <li>
            <a class="nav-link text-white rounded-0 mb-2 tracking-widest small" href="{{ route('settings') }}">OTHER SETTINGS</a>
        </li>
    </ul>
    
    <hr class="border-secondary">
    
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name ?? 'A' }}&background=fff&color=000&rounded=true" alt="" width="32" height="32" class="rounded-circle me-2">
            <strong class="small tracking-widest">{{ strtoupper(Auth::user()->name ?? 'AMILA DIRECTOR') }}</strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow rounded-0 border-secondary" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item tracking-widest small" href="#">SETTINGS</a></li>
            <li><hr class="dropdown-divider border-secondary"></li>
            <li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="dropdown-item text-danger tracking-widest small fw-bold" type="submit">LOGOUT</button>
                </form>
            </li>
        </ul>
    </div>
</div>

