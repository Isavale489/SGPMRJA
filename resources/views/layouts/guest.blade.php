<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Bootstrap CSS -->
        <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
 
    </head>
    <body class="bg-light">
        <div class="container min-vh-100 d-flex flex-column justify-content-center align-items-center py-5">
            <div class="text-center mb-4">
                <a href="/" class="d-inline-block">
                    <img src="{{ asset('logo.jpg') }}" alt="logo"  width="200" height="auto">
                </a>
            </div>

            <div class="col-12 col-sm-10 col-md-8 col-lg-6">
                <div class="card shadow-sm rounded-4">
                    <div class="card-body p-4">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    </body>
</html>