<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorry Dot Com | Cinematic Access</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syncopate:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-white: #ffffff;
            --accent-gray: #888888;
            --deep-black: #050505;
        }

        body {
            background-color: var(--deep-black);
            font-family: 'Inter', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            overflow: hidden;
            color: var(--primary-white);
        }

        /* Animated Background Mesh */
        .bg-mesh {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: 
                radial-gradient(at 0% 0%, rgba(255,255,255,0.03) 0, transparent 50%),
                radial-gradient(at 100% 100%, rgba(255,255,255,0.03) 0, transparent 50%);
            z-index: -1;
            animation: pulse 8s infinite alternate;
        }

        @keyframes pulse {
            0% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2px; /* Sharp edges for modern look */
            padding: 3rem;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            animation: slideUp 1s ease-out;
        }

        .login-card:hover {
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 40px rgba(255, 255, 255, 0.05);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); filter: blur(10px); }
            to { opacity: 1; transform: translateY(0); filter: blur(0); }
        }

        .studio-logo {
            font-family: 'Syncopate', sans-serif;
            font-size: 1.2rem;
            letter-spacing: 12px;
            text-align: center;
            margin-bottom: 3rem;
            text-transform: uppercase;
        }

        .studio-logo span {
            font-weight: 700;
            border: 1px solid white;
            padding: 4px 10px;
            margin-left: 5px;
        }

        .form-control {
            background: transparent;
            border: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0;
            color: #fff;
            padding: 15px 0;
            transition: 0.4s;
        }

        .form-control:focus {
            background: transparent;
            border-bottom-color: var(--primary-white);
            color: #fff;
            box-shadow: none;
            padding-left: 10px;
        }

        /* Floating Label Effect Styling */
        .form-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--accent-gray);
            margin-bottom: 0;
        }

        .btn-access {
            background: var(--primary-white);
            color: var(--deep-black);
            border: 1px solid var(--primary-white);
            border-radius: 0;
            padding: 15px;
            font-weight: 700;
            font-family: 'Syncopate', sans-serif;
            font-size: 0.8rem;
            letter-spacing: 3px;
            transition: all 0.4s;
            margin-top: 2rem;
        }

        .btn-access:hover {
            background: transparent;
            color: var(--primary-white);
            letter-spacing: 6px;
        }

        /* Custom Loader */
        .scan-line {
            width: 0%;
            height: 2px;
            background: white;
            position: absolute;
            bottom: 0;
            left: 0;
            transition: 0.5s;
        }

        .loading .scan-line {
            animation: scanning 2s linear infinite;
        }

        @keyframes scanning {
            0% { width: 0%; left: 0; }
            50% { width: 100%; left: 0; }
            100% { width: 0%; left: 100%; }
        }
    </style>
</head>
<body>

<div class="bg-mesh"></div>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            
            <div class="studio-logo">
                SORRY<span>DOT</span>COM
            </div>

            <div class="login-card" id="card">
            <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                    @csrf
                    
                    <div class="mb-4 position-relative">
                        <label class="form-label">Identification</label>
                        <input type="email" name="email" class="form-control" placeholder="EMAIL@VOIDSHADOW.COM" required autocomplete="off">
                    </div>

                    <div class="mb-4 position-relative">
                        <label class="form-label">Encryption Key</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-access w-100" id="loginBtn">
                        <span id="btnText">INITIATE ACCESS</span>
                        <div class="scan-line"></div>
                    </button>
                </form>

                @if($errors->any())
                    <div class="text-center mt-4">
                        <small class="text-danger" style="letter-spacing: 1px;">AUTHENTICATION FAILED. TRY AGAIN.</small>
                    </div>
                @endif
            </div>
            
            <div class="text-center mt-5">
                <p style="font-size: 0.6rem; letter-spacing: 4px; color: #444; text-transform: uppercase;">
                    System Status: Online / 2026.03.18
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    const loginForm = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = document.getElementById('btnText');
    const card = document.getElementById('card');

    loginForm.addEventListener('submit', function() {
        // Interactive Transition
        card.style.opacity = "0.5";
        card.style.transform = "scale(0.98)";
        loginBtn.classList.add('loading');
        btnText.innerText = "AUTHORIZING...";
        loginBtn.style.pointerEvents = "none";
    });

    // Subtle parallax effect on mouse move
    document.addEventListener('mousemove', (e) => {
        const moveX = (e.clientX - window.innerWidth / 2) * 0.01;
        const moveY = (e.clientY - window.innerHeight / 2) * 0.01;
        card.style.transform = `translate(${moveX}px, ${moveY}px)`;
    });
</script>

</body>
</html>