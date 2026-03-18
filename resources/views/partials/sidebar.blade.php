<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-black vh-100 border-end border-secondary" style="width: 280px;">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4 fw-bold shadow-sm" style="font-family: 'Syncopate'; letter-spacing: 4px;">
            SORRY.<span class="text-secondary border px-1 ms-1">COM</span>
        </span>
    </a>
    <hr class="border-secondary">
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-2">
                DASHBOARD
            </a>
        </li>
        <li>
            <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.*') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-2">
                PROJECTS
            </a>
        </li>
        <li>
            <a href="{{ route('scenes.index') }}" class="nav-link {{ request()->routeIs('scenes.*') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-2">
                SCENES
            </a>
        </li>
        <li>
            <a href="{{ route('characters.index') }}" class="nav-link {{ request()->routeIs('characters.*') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-2">
                CHARACTERS
            </a>
        </li>
        <li>
            <a href="{{ route('editors.index') }}" 
            class="nav-link {{ request()->routeIs('editors.*') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-2">
            EDITORS
        </a>        
        </li>
        <li>
            <a href="{{ route('soundtracks.index') }}" 
            class="nav-link {{ request()->routeIs('soundtracks.*') ? 'active bg-white text-black' : 'text-white' }} rounded-0 mb-2r">
                 SOUNDTRACKS
            </a>
        </li>
        <li>
            <a class="nav-link text-white rounded-0 mb-2" href="{{ route('settings') }}">OTHER SETTINGS</a>
        </li>
    </ul>
    <hr class="border-secondary">
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://ui-avatars.com/api/?name={{ Auth::user()->name }}&background=fff&color=000" alt="" width="32" height="32" class="rounded-circle me-2">
            <strong class="small">{{ strtoupper(Auth::user()->name) }}</strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><a class="dropdown-item" href="#">SETTINGS</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="dropdown-item" type="submit">LOGOUT</button>
                </form>
            </li>
        </ul>
    </div>
</div>