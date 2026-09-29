@props(['title' => 'Bienvenido de nuevo', 'icon' => 'bx-user-circle'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} — Acceso</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Icons CSS -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet">

    <!-- FOUC Prevention: apply saved theme before paint -->
    <script>
        (function () {
            var t = localStorage.getItem('sgpmrja-theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', t);
        })();
    </script>

    <style>
        * { font-family: 'Figtree', sans-serif; }

        /* ── Background ── */
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f2044 0%, #1e3c72 45%, #2a5298 100%);
            transition: background 0.3s ease;
        }

        [data-bs-theme="dark"] body {
            background: linear-gradient(135deg, #080e1c 0%, #111827 50%, #1a2540 100%);
        }

        /* ── Subtle animated dots ── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,0.04) 1px, transparent 1px),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 60px 60px;
            pointer-events: none;
        }

        /* ── Card ── */
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.35);
            background: #ffffff;
            overflow: hidden;
        }

        [data-bs-theme="dark"] .login-card {
            background: #1c2033;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.6);
        }

        /* ── Card header strip ── */
        .login-card-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 20px 28px 18px;
            text-align: center;
        }

        .login-card-header i {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.8);
            margin-right: 8px;
        }

        .login-card-header h4 {
            color: #ffffff;
            font-weight: 600;
            margin: 0;
            font-size: 1.05rem;
            letter-spacing: 0.01em;
        }

        /* ── Card body ── */
        .login-card-body {
            padding: 32px;
        }

        /* ── Input groups ── */
        .input-group {
            border-radius: 10px;
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }

        .input-group-text,
        .input-group .form-control,
        .btn-show-pass {
            border-color: #d0d5dd;
            transition: border-color 0.2s, background 0.2s;
        }

        [data-bs-theme="dark"] .input-group-text,
        [data-bs-theme="dark"] .input-group .form-control,
        [data-bs-theme="dark"] .btn-show-pass {
            border-color: #2d3550;
        }

        /* Hover — solo oscurece el borde del grupo completo */
        .input-group:hover .input-group-text,
        .input-group:hover .form-control,
        .input-group:hover .btn-show-pass {
            border-color: #a0aec0;
        }

        [data-bs-theme="dark"] .input-group:hover .input-group-text,
        [data-bs-theme="dark"] .input-group:hover .form-control,
        [data-bs-theme="dark"] .input-group:hover .btn-show-pass {
            border-color: #4a5568;
        }

        /* Focus */
        .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.15);
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control,
        .input-group:focus-within .btn-show-pass {
            border-color: #1e3c72;
        }

        [data-bs-theme="dark"] .input-group:focus-within {
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.18);
        }

        [data-bs-theme="dark"] .input-group:focus-within .input-group-text,
        [data-bs-theme="dark"] .input-group:focus-within .form-control,
        [data-bs-theme="dark"] .input-group:focus-within .btn-show-pass {
            border-color: #60a5fa;
        }

        /* Icon area */
        .input-group-text {
            background: rgba(30, 60, 114, 0.05);
            border-right: none;
            color: #1e3c72;
            font-size: 1.05rem;
            padding: 0 14px;
        }

        [data-bs-theme="dark"] .input-group-text {
            background: rgba(96, 165, 250, 0.07);
            color: #60a5fa;
        }

        /* Input */
        .input-group .form-control {
            border-left: none;
            font-size: 0.925rem;
            padding: 12px 14px;
        }

        .form-control:focus { box-shadow: none; }

        [data-bs-theme="dark"] .form-control {
            background: #1a2035;
            color: #d1d5db;
        }

        [data-bs-theme="dark"] .form-control:focus {
            background: #1e2645;
            color: #ffffff;
            box-shadow: none;
        }

        .form-control::placeholder { color: #9ca3af; }
        [data-bs-theme="dark"] .form-control::placeholder { color: #4b5568; }

        /* Show/hide btn — absoluto dentro del wrapper */
        .pass-wrapper {
            position: relative;
        }

        .pass-wrapper .form-control {
            padding-right: 44px;
        }

        .btn-show-pass {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            line-height: 1;
            color: #9ca3af;
            font-size: 1.1rem;
            cursor: pointer;
            z-index: 5;
            transition: color 0.2s;
        }

        .btn-show-pass:hover  { color: #1e3c72; }
        .btn-show-pass:focus  { outline: none; color: #1e3c72; }

        [data-bs-theme="dark"] .btn-show-pass         { color: #4b5568; }
        [data-bs-theme="dark"] .btn-show-pass:hover,
        [data-bs-theme="dark"] .btn-show-pass:focus   { color: #60a5fa; }

        /* Error state */
        .input-group:has(.is-invalid) {
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.13);
        }

        .input-group:has(.is-invalid) .input-group-text,
        .input-group:has(.is-invalid) .form-control,
        .input-group:has(.is-invalid) .btn-show-pass {
            border-color: #dc3545;
        }

        .input-group:has(.is-invalid) .input-group-text,
        .input-group:has(.is-invalid) .btn-show-pass {
            color: #dc3545;
        }

        /* Anular el icono SVG que Bootstrap inyecta en inputs .is-invalid */
        .form-control.is-invalid {
            background-image: none !important;
            padding-right: 14px;
        }

        .pass-wrapper .form-control.is-invalid {
            padding-right: 44px;
        }

        /* ── Submit button ── */
        .btn-atlantico {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 11px 28px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.35);
            transition: all 0.25s ease;
            letter-spacing: 0.02em;
        }

        .btn-atlantico:hover {
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
            box-shadow: 0 6px 18px rgba(30, 60, 114, 0.45);
            transform: translateY(-1px);
            color: #ffffff;
        }

        .btn-atlantico:active {
            transform: translateY(0);
            box-shadow: 0 3px 8px rgba(30, 60, 114, 0.3);
        }

        /* ── Links ── */
        .link-atlantico {
            color: #1e3c72;
            font-size: 0.875rem;
            text-decoration: none;
            transition: color 0.2s;
        }

        .link-atlantico:hover { color: #2a5298; text-decoration: underline; }

        [data-bs-theme="dark"] .link-atlantico { color: #60a5fa; }
        [data-bs-theme="dark"] .link-atlantico:hover { color: #93c5fd; }

        /* ── Form check ── */
        .form-check-input:checked {
            background-color: #1e3c72;
            border-color: #1e3c72;
        }

        [data-bs-theme="dark"] .form-check-label { color: #c8cbd0; }

        /* ── Dark mode toggle ── */
        .theme-toggle {
            position: fixed;
            top: 16px;
            right: 16px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.25);
            background: rgba(255,255,255,0.12);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            backdrop-filter: blur(8px);
            transition: background 0.2s, border-color 0.2s, transform 0.2s;
            z-index: 1000;
            font-size: 1.1rem;
        }

        .theme-toggle:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.08);
        }

        [data-bs-theme="dark"] .theme-toggle {
            border-color: rgba(255,255,255,0.15);
            background: rgba(255,255,255,0.08);
        }

        /* ── Footer text ── */
        .login-footer {
            color: rgba(255,255,255,0.55);
            font-size: 0.78rem;
            text-align: center;
            margin-top: 20px;
        }

        /* ── Form labels ── */
        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 6px;
            color: #374151;
        }

        [data-bs-theme="dark"] .form-label { color: #c8cbd0; }

        /* ── Alert ── */
        .alert-success {
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

    <!-- Dark/Light mode toggle -->
    <button class="theme-toggle" id="themeToggle" title="Cambiar tema" aria-label="Cambiar tema">
        <i class="bx bx-moon" id="themeIcon"></i>
    </button>

    <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

        <!-- Logo -->
        <div class="text-center mb-4">
            <a href="/">
                <img src="{{ asset('atlantico-logo-wide.png') }}" alt="Atlántico" width="240">
            </a>
        </div>

        <div class="card login-card">
            <!-- Card header -->
            <div class="login-card-header">
                <h4><i class="bx {{ $icon }}"></i>{{ $title }}</h4>
            </div>

            <!-- Card body -->
            <div class="login-card-body">
                {{-- Sesión vencida (419) en cualquier pantalla de acceso: lo pone el Handler. --}}
                @if (session('aviso'))
                    <div class="alert alert-warning d-flex align-items-center gap-2 mb-4" role="alert">
                        <i class="bx bx-time-five fs-5"></i>
                        {{ session('aviso') }}
                    </div>
                @endif
                {{ $slot }}
            </div>
        </div>

        <p class="login-footer">
            &copy; 2026 Grupo Textil 636 &middot; Informática
        </p>
    </div>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script>
        (function () {
            var html    = document.documentElement;
            var btn     = document.getElementById('themeToggle');
            var icon    = document.getElementById('themeIcon');

            function applyTheme(t) {
                html.setAttribute('data-bs-theme', t);
                localStorage.setItem('sgpmrja-theme', t);
                if (t === 'dark') {
                    icon.classList.replace('bx-moon', 'bx-sun');
                } else {
                    icon.classList.replace('bx-sun', 'bx-moon');
                }
            }

            // Sync icon on load
            applyTheme(html.getAttribute('data-bs-theme') || 'light');

            btn.addEventListener('click', function () {
                var current = html.getAttribute('data-bs-theme') || 'light';
                applyTheme(current === 'dark' ? 'light' : 'dark');
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
