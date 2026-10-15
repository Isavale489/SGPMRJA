<!DOCTYPE html>
{{--
    Plantilla raíz de la plataforma Inertia + React. Separada a propósito de
    admin/layouts/app.blade.php: NO carga Bootstrap/Velzon ni jQuery (el
    preflight de Tailwind y Bootstrap se pisan). Ver docs/conventions/frontend.md.
--}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets/images/favicon.ico') }}">

    {{-- Tema antes del primer pintado (misma clave que el layout Blade). --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('sgpmrja-theme');
                if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>

    @viteReactRefresh
    @vite(['resources/js/inertia.tsx', "resources/js/pages/{$page['component']}.tsx"])
    <x-inertia::head>
        <title>{{ config('app.name') }}</title>
    </x-inertia::head>
</head>
<body>
    <x-inertia::app />
</body>
</html>
