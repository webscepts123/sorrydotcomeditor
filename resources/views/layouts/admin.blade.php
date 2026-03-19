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
    </style>
</head>
<body>

<div class="d-flex">
    @include('partials.sidebar')

    <div class="w-100 overflow-auto vh-100">
        @include('partials.topbar')
        
        <main class="p-4">
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>