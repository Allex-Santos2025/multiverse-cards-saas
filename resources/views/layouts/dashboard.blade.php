<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Lojista - Versus TCG</title>
    
    {{-- 1. Estilos do Livewire --}}
    @livewireStyles

    {{-- 2. CSS Compilado --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    
    {{-- REMOVI O APP.JS DAQUI. Vamos carregar apenas no final para garantir a ordem. --}}
        
    {{-- ApexCharts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    {{-- Fonts e Ícones --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    @stack('cards')
    
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 dark:bg-[#0f172a] dark:text-[#f1f5f9] min-h-screen flex flex-col antialiased transition-colors duration-300">

    <header class="bg-white border-b border-slate-200 dark:bg-[#1e293b] dark:border-slate-700 sticky top-0 z-50">
        
        {{-- Camada 1: Header --}}
        <div class="relative z-[60]">
            @include('partials.dashboard.header')
        </div>

        {{-- Camada 2: Menu --}}
        <div class="relative z-[50]">
            @include('partials.dashboard.menu')
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.dashboard.footer')

    {{-- ================================================================= --}}
    {{-- ÁREA CRÍTICA DE SCRIPTS (ORDEM CORRIGIDA) --}}
    {{-- ================================================================= --}}

    {{-- 1. Tema (Vanilla JS - roda independente) --}}
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
            window.dispatchEvent(new Event('theme-changed'));
        }

        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    {{-- 2. O SEU JAVASCRIPT (Alpine + Plugins) --}}
    {{-- SEM 'defer'. Queremos que ele rode IMEDIATAMENTE antes do Livewire. --}}
    <script src="{{ mix('js/app.js') }}?v={{ time() }}"></script>

    {{-- 3. LIVEWIRE (Vai encontrar o Alpine pronto acima e usar ele) --}}
    @livewireScripts

    {{-- 4. STACKS (Scripts específicos das páginas, como o estoque) --}}
    @stack('scripts')
    
</body>
</html>