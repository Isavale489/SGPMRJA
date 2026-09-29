<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Manufacturas R.J. Atlántico: confección de prendas textiles y bordados personalizados en Acarigua - Araure, estado Portuguesa.">
        <meta name="keywords" content="sistema, atlántico, keywords">
        <meta name="author" content="" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        
        <!-- SEO Meta Tags -->
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:title" content="@yield('title') - Sistema Atlántico">
        <meta property="og:description" content="Manufacturas R.J. Atlántico: confección de prendas textiles y bordados personalizados en Acarigua - Araure, estado Portuguesa.">
        
        <!-- Twitter -->
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="@yield('title') - Sistema Atlántico">
        <meta name="twitter:description" content="Manufacturas R.J. Atlántico: confección de prendas textiles y bordados personalizados en Acarigua - Araure, estado Portuguesa.">

        <title>@yield('title') - Sistema Atlántico</title>
        
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/favicon.ico') }}" />
        <!-- FontAwesome-->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />
        <!-- Custom CSS -->
        <link href="{{ asset('css/custom.css') }}" rel="stylesheet" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        {{-- Bootstrap estático (el mismo que usa el panel), no un duplicado empaquetado por Vite. --}}
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    </head>
    <body class="d-flex flex-column h-100 font-sans antialiased">
        <main class="flex-shrink-0">
            <!-- Navigation-->
            @include('components.navigation')

            @yield('content')
        </main>

        <!-- Footer-->
        @include('components.footer')

        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('js/scripts.js') }}"></script>
    </body>
</html> 