@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5 border-bottom border-secondary pb-4">
        <h6 class="text-secondary mb-1 uppercase tracking-widest" style="font-size: 10px;">PRODUCTION TOOLS</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">GENERATE SCORE</h2>
    </div>

    <div class="bg-black border border-secondary p-5">
        <div class="mb-4">
            <label class="form-label text-secondary small tracking-widest uppercase">Atmosphere / AI Prompt</label>
            <textarea rows="4" class="form-control bg-transparent border border-secondary text-white rounded-0 p-3 italic" placeholder="e.g. A slow, brooding cello melody mixed with distant synth pads, cinematic noir style, heavy rain background..."></textarea>
        </div>
        
        <div class="row mb-5">
            <div class="col-md-6">
                <label class="form-label text-secondary small tracking-widest uppercase">Target Duration</label>
                <select class="form-select bg-dark border-secondary text-white rounded-0">
                    <option>30 Seconds (Stem)</option>
                    <option>1 Minute (Loop)</option>
                    <option>3 Minutes (Full Track)</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label text-secondary small tracking-widest uppercase">BPM / Pacing</label>
                <input type="range" class="form-range mt-2" min="60" max="140" id="bpmRange">
                <div class="d-flex justify-content-between text-secondary" style="font-size: 10px;">
                    <span>SLOW (60)</span><span>FAST (140)</span>
                </div>
            </div>
        </div>

        <button class="btn btn-white bg-white text-black rounded-0 px-5 py-3 fw-bold tracking-widest uppercase w-100 transition-hover">GENERATE AUDIO TRACK</button>
    </div>
</div>
<style>
    .tracking-widest { letter-spacing: 0.2em; }
    .uppercase { text-transform: uppercase; }
    .italic { font-style: italic; }
    .transition-hover:hover { background: #ccc !important; transform: scale(1.01); }
</style>
@endsection