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

    {{-- 1. CSS Compilado pelo Mix --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    {{-- 2. Estilos do Livewire --}}
    @livewireStyles

    @stack('head')

    <style>
        html {
            scroll-behavior: smooth !important;
        }
    </style>

</head>
<body class="antialiased" 
      x-data="{ isModalOpen: false }"
      @modal-opened.window="isModalOpen = true"
      @modal-closed.window="isModalOpen = false"
      :class="{ 'overflow-hidden': isModalOpen }">

    {{-- TUDO o que deve ser distorcido --}}
    <div id="site-wrapper" 
         class="transition-all duration-700 ease-in-out"
         :class="{ 'blur-xl opacity-20 grayscale scale-[0.95] pointer-events-none': isModalOpen }">
        
        @include('partials.header')

        {{-- INJEÇÃO DO MENU FUNIL (MAGIC, POKEMON, ETC) --}}
        

        <main>{{ $slot ?? '' }} @yield('content') </main>

        @include('partials.footer')
    </div>

    {{-- O Modal DEVE ficar aqui, no final de tudo --}}
    @livewire('marketplace.auth-modal')
    @livewire('marketplace.auth-modal-login')

    @livewireScripts
    <script src="{{ mix('js/app.js') }}"></script>
    @stack('scripts')
    <script>
    window.addEventListener('modal-opened', () => {
        console.log('O sinal de abrir chegou no Layout!');
    });
    </script>
</body>
</html>