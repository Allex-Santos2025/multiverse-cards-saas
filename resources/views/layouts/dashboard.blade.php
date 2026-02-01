<!DOCTYPE html>
<html lang="pt-BR" class="dark"> {{-- O JS controla essa classe --}}
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Lojista - Versus TCG</title>
    
    {{-- Única importação de CSS necessária --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ mix('js/app.js') }}" defer></script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        /* ZERO CSS DE COR AQUI - O TAILWIND MANDA NO BODY */
    </style>
</head>

{{-- 
    bg-slate-50 -> Seu BRANCO GELO (contraste com cards brancos)
    dark:bg-[#0f172a] -> Seu AZUL MARINHO original
--}}
<body class="bg-slate-50 text-slate-900 dark:bg-[#0f172a] dark:text-[#f1f5f9] min-h-screen flex flex-col antialiased transition-colors duration-300">

    <header class="bg-white border-b border-slate-200 dark:bg-[#1e293b] dark:border-slate-700 sticky top-0 z-50">
        @stack('cards')
        @stack('scripts')

        {{-- Camada 1: O Header ganha z-index maior para os dropdowns "voarem" por cima --}}
        <div class="relative z-[60]">
            @include('partials.dashboard.header')
        </div>

        {{-- Camada 2: O Menu fica logo abaixo na pilha --}}
        <div class="relative z-[50]">
            @include('partials.dashboard.menu')
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.dashboard.footer')

    <script>
        // Função de troca de tema sem frescura
        function toggleTheme() {
            const html = document.documentElement;
            html.classList.toggle('dark');
            localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
            window.dispatchEvent(new Event('theme-changed'));
        }

        // Aplica o tema salvo ao carregar para evitar o "flash" de luz
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</body>
</html>