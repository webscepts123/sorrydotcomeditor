@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-5">
        <h6 class="text-secondary mb-1" style="letter-spacing: 3px;">PRODUCTION / CASTING</h6>
        <h2 class="fw-light" style="font-family: 'Syncopate'; letter-spacing: 4px;">INITIALIZE CHARACTER</h2>
    </div>

    <div class="row">
        <div class="col-md-7">
            <form action="{{ route('characters.store') }}" method="POST" class="bg-black border border-secondary p-5">
                @csrf

                <div class="mb-4">
                    <label class="form-label text-secondary small tracking-widest">CHARACTER NAME</label>
                    <input type="text" name="name" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" placeholder="E.G. THE WATCHER" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-secondary small tracking-widest">AI SEED TAG</label>
                        <input type="text" name="ai_tag" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" placeholder="@actor_v1_seed_9921">
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="form-label text-secondary small tracking-widest">ROLE / ARCHETYPE</label>
                        <select name="role" class="form-select bg-black border-0 border-bottom border-secondary rounded-0 text-white p-2">
                            <option value="protagonist">Protagonist</option>
                            <option value="antagonist">Antagonist</option>
                            <option value="supporting">Supporting</option>
                        </select>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="form-label text-secondary small tracking-widest">VISUAL DESCRIPTION (FOR PROMPTING)</label>
                    <textarea name="description" rows="3" class="form-control bg-transparent border-0 border-bottom border-secondary rounded-0 text-white p-2" placeholder="Describe key facial features for consistency..."></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('characters.index') }}" class="text-secondary text-decoration-none small tracking-widest">CANCEL</a>
                    <button type="submit" class="btn btn-white bg-white text-black rounded-0 px-5 py-3 fw-bold tracking-widest">
                        SAVE IDENTITY
                    </button>
                </div>
            </form>
        </div>
        
        <div class="col-md-5">
            <div class="p-4 border border-secondary text-secondary small">
                <h6 class="text-white tracking-widest mb-3">CONSISTENCY NOTE</h6>
                <p>The **AI Seed Tag** is passed directly to the video generation engine. Ensure you have tested the seed in your local environment before locking it into the production database.</p>
            </div>
        </div>
    </div>
</div>
@endsection