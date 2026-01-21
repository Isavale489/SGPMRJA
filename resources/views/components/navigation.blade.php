<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #1b2a4e !important;">
    <style>
        .navbar-nav .nav-link {
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #0d6efd !important; /* Un azul primario, que coincida con el tema potencial del sitio */
            transform: translateY(-2px);
        }

        .navbar-dark .navbar-nav .nav-link.active {
            color: #0d6efd !important; /* Color del enlace activo */
        }

        .navbar-nav .nav-link {
            display: flex !important; /* Usar flex para control total del contenido */
            align-items: center !important; /* Centrar verticalmente ícono y texto */
            white-space: nowrap !important; /* Asegurar que el texto no se envuelva */
            flex-direction: row !important; /* Forzar la dirección de fila para evitar que el ícono se apile */
        }

        .navbar-nav .nav-item {
            margin-right: 15px; /* Ajusta este valor según sea necesario */
        }

        .navbar-nav .nav-item:last-child {
            margin-right: 0; /* Eliminar margen derecho del último elemento para evitar espacio extra */
        }

        .dropdown-menu {
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border: none;
            background-color: #343a40; /* Fondo oscuro para consistencia */
        }

        .dropdown-item {
            color: rgba(255, 255, 255, 0.75);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .dropdown-item:hover, .dropdown-item:focus {
            background-color: #495057; /* Oscuro ligeramente más claro */
            color: #ffffff;
        }

        /* Estilos de botón personalizados para una mejor apariencia si custom-btn no es suficiente */
        .custom-btn {
            border-radius: 20px; /* Más redondeado */
            padding: 8px 20px;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .btn-outline-light.custom-btn:hover {
            background-color: #0d6efd;
            color: white !important;
            border-color: #0d6efd;
        }

        .btn-primary.custom-btn:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }

        .nav-item .nav-link i {
            margin-right: 8px; /* Espacio entre el ícono y el texto */
        }

        /* NUEVO: Estilos para el elemento select en la barra de navegación */
        .navbar-nav .nav-item select.form-select {
            padding: 0.5rem 1rem;
            height: auto;
            background-color: #343a40;
            color: rgba(255, 255, 255, 0.75);
            border: none;
            box-shadow: none;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
            cursor: pointer;
            /* Flecha personalizada para fondo oscuro */
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23f8f9fa' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 0.65em 0.65em;
        }

        .navbar-nav .nav-item select.form-select:hover {
            color: #0d6efd !important;
            transform: translateY(-2px);
            background-color: #495057;
        }

        .navbar-nav .nav-item select.form-select:focus {
            border-color: transparent;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
            outline: 0;
        }

        /* Responsive: Ajustar paddings y márgenes en móvil */
        @media (max-width: 991.98px) {
            .navbar-nav .nav-item {
                margin-right: 0;
                margin-bottom: 10px;
            }
            .navbar-nav {
                gap: 0 !important;
            }
            .navbar-brand img {
                height: 50px !important;
            }
        }
    </style>
    <div class="container px-3">
        <a class="navbar-brand d-flex align-items-center flex-nowrap" href="{{ url('/') }}">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('atlantico-logo-wide.png') }}" alt="Logo de la empresa" style="height: 60px; width: auto;">
                <span class="fs-5 fw-bold text-nowrap">Manufacturas R.J Atlántico</span>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="{{ url('/') }}"><i class="fas fa-home"></i>Inicio</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdownPortfolio" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-book"></i>Catálogo</a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownPortfolio">
                        <li><a class="dropdown-item" href="{{ url('portfolio') }}">Catálogo General</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="{{ url('about') }}"><i class="fas fa-users"></i>Sobre Nosotros</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('faq') }}"><i class="fas fa-question-circle"></i>Preg. Freq.</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ url('contact') }}"><i class="fas fa-envelope"></i>Contáctanos</a></li>
            </ul>
            <!-- Botones de sesión dentro del colapsable para responsividad -->
            <div class="d-lg-flex ms-lg-3 mt-3 mt-lg-0">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light me-2 custom-btn mb-2 mb-lg-0 w-100 w-lg-auto"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline w-100 w-lg-auto">
                        @csrf
                        <button type="submit" class="btn btn-primary custom-btn w-100 w-lg-auto"><i class="fas fa-sign-out-alt"></i>Cerrar Sesión</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light custom-btn w-100 w-lg-auto"><i class="fas fa-sign-in-alt"></i>Iniciar</a>
                @endauth
            </div>
        </div>
    </div>
</nav> 