@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">SYSTEM / CORE</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">STUDIO SETTINGS</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success bg-black border border-success text-success rounded-0 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger bg-black border border-danger text-danger rounded-0 mb-4">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="row g-5">
        <div class="col-lg-6">
            <div class="bg-black border border-secondary p-4 h-100">
                <h5 class="text-white small tracking-widest uppercase mb-4 border-bottom border-secondary pb-2">AI ENGINES</h5>
                
                <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">Local ComfyUI URL</label>
                        <input type="url" name="comfyui_url" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" value="{{ old('comfyui_url', $settings['comfyui_url']) }}" required>
                        <small class="text-secondary">Free local Wan video engine (default port 8188).</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">Wan API Workflow File</label>
                        <input type="text" name="comfyui_video_workflow" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" value="{{ old('comfyui_video_workflow', $settings['comfyui_video_workflow']) }}" required>
                        <small class="text-secondary">Export a ComfyUI workflow in API format and use <code>@{{PROMPT}}</code> in its positive prompt.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">Seedance API Key</label>
                        <input type="password"
                               name="seedance_api_key"
                               class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2"
                               value="{{ old('seedance_api_key', $settings['seedance_api_key']) }}"
                               placeholder="Not configured">
                        <small class="text-secondary d-block mt-1">
                            {{ $settings['seedance_api_key'] ? 'Loaded from .env' : 'Missing SEEDANCE_API_KEY in .env' }}
                        </small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">Seedance Base URL</label>
                        <input type="url"
                               name="seedance_base_url"
                               class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2"
                               value="{{ old('seedance_base_url', $settings['seedance_base_url']) }}"
                               placeholder="https://your-seedance-endpoint.example">
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">OpenAI API Key</label>
                        <input type="password"
                               name="openai_api_key"
                               class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2"
                               value="{{ old('openai_api_key', $settings['openai_api_key']) }}"
                               placeholder="Not configured">
                        <small class="text-secondary d-block mt-1">
                            {{ $settings['openai_api_key'] ? 'Loaded from .env' : 'Missing OPENAI_API_KEY in .env' }}
                        </small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-secondary small tracking-widest uppercase">DEFAULT RENDER RESOLUTION</label>
                        <select name="default_render_resolution" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                            @foreach(['720p' => '720p (Draft Speed)', '1080p' => '1080p (Production Standard)', '2k' => '2K (Digital Cinema)', '4k' => '4K (Cinematic Master)', '8k' => '8K (Archive Master)'] as $value => $label)
                                <option value="{{ $value }}" {{ old('default_render_resolution', $settings['default_render_resolution']) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-outline-light rounded-0 px-4 py-2 small tracking-widest uppercase">Save Engine Config</button>
                    </div>
                </form>

                <form action="{{ route('settings.api-refresh') }}" method="POST" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-info rounded-0 px-4 py-2 small tracking-widest uppercase">
                        Generate Placeholder Key
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="bg-black border border-secondary p-4 h-100">
                <h5 class="text-white small tracking-widest uppercase mb-4 border-bottom border-secondary pb-2">INFRASTRUCTURE</h5>
                
                <div class="mb-4">
                    <label class="text-secondary small tracking-widest d-block mb-1">STORAGE NODE STATUS</label>
                    <div class="d-flex align-items-center">
                        <span class="badge {{ $settings['db_online'] ? 'bg-success' : 'bg-danger' }} rounded-circle p-1 me-2" style="width: 10px; height: 10px;"> </span>
                        <span class="text-white small">{{ strtoupper($settings['db_status']) }}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-secondary small tracking-widest d-block mb-1">LOCAL WORKSTATION</label>
                    <span class="text-secondary small italic">{{ $settings['workstation'] }}</span>
                    <div class="text-secondary small mt-1">Server: {{ $settings['server_software'] }}</div>
                </div>

                <div class="mb-4">
                    <label class="text-secondary small tracking-widest d-block mb-1">STORAGE USAGE</label>
                    <div class="progress bg-dark rounded-0 mb-1" style="height: 4px;">
                        <div class="progress-bar bg-white" style="width: {{ $settings['storage_percent'] }}%"></div>
                    </div>
                    <span class="text-secondary small">{{ $settings['storage_used_gb'] }}GB / {{ $settings['storage_total_gb'] }}GB used</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="text-secondary small tracking-widest d-block mb-1">QUEUE</label>
                        <span class="text-white small">{{ strtoupper($settings['queue_connection']) }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="text-secondary small tracking-widest d-block mb-1">CACHE</label>
                        <span class="text-white small">{{ strtoupper($settings['cache_store']) }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="text-secondary small tracking-widest d-block mb-1">FILESYSTEM</label>
                        <span class="text-white small">{{ strtoupper($settings['filesystem_disk']) }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="text-secondary small tracking-widest d-block mb-1">FFMPEG</label>
                        <span class="text-{{ $settings['ffmpeg_status'] === 'Available' ? 'success' : 'warning' }} small">{{ strtoupper($settings['ffmpeg_status']) }}</span>
                    </div>
                </div>

                <div class="mt-5 pt-3">
                    <h6 class="text-danger small tracking-widest uppercase mb-2">Danger Zone</h6>
                    <form action="{{ route('settings.cache-clear') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger rounded-0 px-3 py-1 small tracking-widest">
                            WIPE TEMP CACHE
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .uppercase { text-transform: uppercase; }
    .tracking-widest { letter-spacing: 0.2em; }
    .form-control:focus, .form-select:focus { 
        background-color: transparent; 
        border-bottom-color: #fff !important; 
        box-shadow: none; 
        color: #fff;
    }
</style>
@endsection
