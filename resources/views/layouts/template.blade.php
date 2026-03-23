<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $loja->name ?? 'Minha Loja TCG' }} - Powered by Versus</title>

    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="{{ mix('js/app.js') }}" defer></script>

    {{-- FAVICON DINÂMICO DA LOJA AQUI 👇 --}}
    @php
        $userStore = auth('store_user')->user()->store ?? null;
        $visual = $userStore ? $userStore->visual : null;
    @endphp

    @if($visual && $visual->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('store_images/' . $userStore->url_slug . '/' . $visual->favicon) }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon-default.ico') }}">
    @endif
    {{-- FIM DO FAVICON 👆 --}}

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    @livewireStyles
    @include('partials.template.styles')
</head>
<body class="bg-white text-gray-800 font-sans antialiased flex flex-col min-h-screen">
    
    @include('partials.template.admin-bar')

    @include('partials.template.header')

    <main class="flex-grow bg-gray-50">
        {{-- Para páginas clássicas do Blade --}}
        @yield('content')
        
        {{-- Para componentes de página inteira do Livewire --}}
        {{ $slot ?? '' }}
    </main>

    @include('partials.template.footer')

    @livewireScripts
</body>
</html>