@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="container-fluid bg-black min-vh-100 p-0">
    <div class="p-4 border-bottom border-secondary bg-dark d-flex justify-content-between align-items-center">
        <div>
            <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">PRODUCTION TOOLS / ENGINE</h6>
            <h2 class="fw-light mb-0" style="font-family: 'Syncopate'; letter-spacing: 4px;">SCRIPT PROCESSOR</h2>
        </div>
        <div class="d-flex gap-3">
            <select class="form-select bg-black border-secondary text-white rounded-0 small tracking-widest uppercase" style="width: 250px; font-size: 12px;">
                <option value="">SELECT PRODUCTION...</option>
                <option value="1" selected>VOID SHADOW (ACTIVE)</option>
            </select>
            <button class="btn btn-outline-info rounded-0 px-4 py-2 small tracking-widest uppercase fw-bold transition-hover">
                <i class="bi bi-cpu me-2"></i> PARSE TO SCENES
            </button>
        </div>
    </div>

    <div class="border-bottom border-secondary bg-black d-flex text-center">
        <div class="flex-fill p-2 border-end border-secondary">
            <span class="text-secondary small tracking-widest uppercase d-block" style="font-size: 9px;">Est. Word Count</span>
            <span class="text-white fw-bold font-monospace">2,450</span>
        </div>
        <div class="flex-fill p-2 border-end border-secondary">
            <span class="text-secondary small tracking-widest uppercase d-block" style="font-size: 9px;">Detected Scenes</span>
            <span class="text-info fw-bold font-monospace">14</span>
        </div>
        <div class="flex-fill p-2">
            <span class="text-secondary small tracking-widest uppercase d-block" style="font-size: 9px;">Est. Screen Time</span>
            <span class="text-white fw-bold font-monospace">00:18:30</span>
        </div>
    </div>

    <div class="row g-0" style="height: calc(100vh - 165px);">
        
        <div class="col-md-7 border-end border-secondary d-flex flex-column h-100">
            <div class="bg-dark border-bottom border-secondary p-2 d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary rounded-0 uppercase tracking-widest editor-btn" title="Scene Heading (INT/EXT)">SCENE</button>
                <button class="btn btn-sm btn-outline-secondary rounded-0 uppercase tracking-widest editor-btn" title="Action / Visuals">ACTION</button>
                <button class="btn btn-sm btn-outline-secondary rounded-0 uppercase tracking-widest editor-btn" title="Character Name">CHARACTER</button>
                <button class="btn btn-sm btn-outline-secondary rounded-0 uppercase tracking-widest editor-btn" title="Dialogue">DIALOGUE</button>
                <div class="vr bg-secondary mx-2"></div>
                <button class="btn btn-sm btn-outline-secondary rounded-0" title="Format to Screenplay Standard"><i class="bi bi-magic"></i> Auto-Format</button>
            </div>
            
            <div class="flex-grow-1 p-4 bg-black position-relative">
                <textarea class="form-control bg-transparent border-0 text-white rounded-0 w-100 h-100 script-font" 
                          style="resize: none; outline: none; box-shadow: none;" 
                          placeholder="[SCENE 001]&#10;INT. ABANDONED WAREHOUSE - NIGHT&#10;&#10;Silas strikes a match. The flame illuminates the rain leaking through the rusted roof..."></textarea>
            </div>
        </div>

        <div class="col-md-5 bg-dark d-flex flex-column h-100 overflow-auto">
            <div class="p-3 border-bottom border-secondary bg-black sticky-top">
                <h6 class="text-white small tracking-widest uppercase mb-0"><i class="bi bi-radar me-2 text-info"></i> AI Extraction Log</h6>
            </div>

            <div class="p-4">
                <div class="mb-5">
                    <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-3">Detected Cast</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge border border-secondary bg-black text-white rounded-0 p-2"><i class="bi bi-person text-info me-1"></i> SILAS</span>
                        <span class="badge border border-secondary bg-black text-white rounded-0 p-2"><i class="bi bi-person text-info me-1"></i> THE INFORMANT</span>
                        <span class="badge border border-dashed border-secondary bg-transparent text-secondary rounded-0 p-2"><i class="bi bi-plus"></i> ADD TAG</span>
                    </div>
                </div>

                <div class="mb-5">
                    <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-3">Identified Locations</h6>
                    <ul class="list-unstyled">
                        <li class="text-white small mb-2"><i class="bi bi-geo-alt text-secondary me-2"></i> INT. ABANDONED WAREHOUSE</li>
                        <li class="text-white small mb-2"><i class="bi bi-geo-alt text-secondary me-2"></i> EXT. COLOMBO STREETS</li>
                    </ul>
                </div>

                <div>
                    <h6 class="text-secondary small tracking-widest uppercase border-bottom border-secondary pb-2 mb-3">Pending Clip Prompts</h6>
                    
                    <div class="p-3 border border-secondary bg-black mb-2 transition-hover">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-secondary rounded-0 text-black fw-bold" style="font-size: 10px;">CLIP 001</span>
                            <span class="text-secondary small font-monospace">15s</span>
                        </div>
                        <p class="text-white small italic mb-0">"Cinematic noir, wide shot, Silas striking a match in a dark abandoned warehouse, heavy rain leaking from roof, high contrast lighting..."</p>
                    </div>

                    <div class="p-3 border border-secondary bg-black mb-2 transition-hover">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-secondary rounded-0 text-black fw-bold" style="font-size: 10px;">CLIP 002</span>
                            <span class="text-secondary small font-monospace">15s</span>
                        </div>
                        <p class="text-white small italic mb-0">"Close up on Silas's face illuminated by match light, rain dripping in background, intense expression, Arri Alexa 35mm..."</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
    .tracking-widest { letter-spacing: 0.2em; }
    .uppercase { text-transform: uppercase; }
    .italic { font-style: italic; }
    .font-monospace { font-family: 'Courier New', Courier, monospace; }
    
    /* Screenplay Font Setup */
    .script-font {
        font-family: 'Courier Prime', 'Courier New', Courier, monospace;
        font-size: 14px;
        line-height: 1.6;
    }

    /* UI Enhancements */
    .editor-btn { font-size: 10px; }
    .editor-btn:hover { background: #fff; color: #000; }
    .transition-hover:hover { border-color: #fff !important; cursor: pointer; }
    
    /* Scrollbar for dark theme */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #0a0a0a; }
    ::-webkit-scrollbar-thumb { background: #333; }
    ::-webkit-scrollbar-thumb:hover { background: #555; }
</style>
@endsection