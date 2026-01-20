<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Versus TCG')</title>

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('assets/favicon.png') }}" type="image/png">

    {{-- Logo do Site --}}
    <link rel="apple-touch-icon" href="{{ asset('assets/site-logo.png') }}">
    <link rel="image_src" href="{{ asset('assets/site-logo.png') }}">

    {{-- 1. CSS Compilado pelo Mix (Substitui o @vite CSS) --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    {{-- 2. Estilos do Livewire (Essencial para a nova stack) --}}
    @livewireStyles

    @stack('head')

    <style>
        html {
            scroll-behavior: smooth !important;
        }
    </style>

</head>
<body class="">

    {{-- Header --}}
    @include('partials.header')

        {{-- Conteúdo principal --}}
    <main>
        {{-- Se for uma página Livewire (como Planos), entra aqui: --}}
        {{ $slot ?? '' }}

        {{-- Se for uma página normal (como Home), entra aqui: --}}
        @yield('content')
    </main>

  
    {{-- Footer --}}
    @include('partials.footer')

    {{-- 3. Scripts do Livewire (Essencial para a interatividade) --}}
    @livewireScripts

    {{-- 4. JS Compilado pelo Mix (Substitui o @vite JS) --}}
    <script src="{{ mix('js/app.js') }}"></script>

    @stack('scripts')
</body>
</html>
