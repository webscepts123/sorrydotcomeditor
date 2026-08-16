<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VOID SHADOW EDITOR | Studio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body { background-color: #050505; color: #fff; font-family: 'Inter', sans-serif; overflow-x: hidden; }
        .nav-link:hover { background: rgba(255,255,255,0.1); }
        .bg-black { background-color: #000 !important; }
        .tracking-widest { letter-spacing: 0.2em; }
        /* Custom Scrollbar for modern feel */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: #000; }
        ::-webkit-scrollbar-thumb { background: #333; }
        .app-shell > .w-100 { min-width: 0; }
        @media (max-width: 767.98px) {
            .app-shell { display: block !important; }
            .app-shell > .flex-column { width: 100% !important; height: auto !important; min-height: auto; position: static; }
            .app-shell > .flex-column .nav { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px; }
            .app-shell > .flex-column .nav-link { margin-bottom: 0 !important; font-size: 10px; }
            .app-shell > .flex-column hr, .app-shell > .flex-column .dropdown { display: none; }
            main.p-4 { padding: 16px !important; }
            .navbar .container-fluid { align-items: flex-start; flex-direction: column; gap: 10px; }
            .navbar .ms-auto { margin-left: 0 !important; width: 100%; flex-wrap: wrap; }
        }
    </style>
</head>
<body>

<div class="d-flex app-shell">
    @include('partials.sidebar')

    <div class="w-100 overflow-auto vh-100">
        @include('partials.topbar')
        
        <main class="p-4">
            @if(session('success'))
                <div class="alert alert-success bg-black border-success text-success rounded-0">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger bg-black border-danger text-danger rounded-0">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
