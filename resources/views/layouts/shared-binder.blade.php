<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Versus TCG | Fichário' }}</title>

    <link rel="icon" href="{{ asset('assets/favicon.png') }}" type="image/png">

    {{-- Estilos compilados do sistema e suporte Phosphor Icons --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>

    @livewireStyles
    @stack('head')

    <style>
        body {
            background-color: #07090e;
            color: #f1f5f9;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col justify-between">

    {{-- HEADER MINIMALISTA OFICIAL --}}
    <header class="w-full bg-[#09090b] border-b border-white/10 sticky top-0 z-50 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            
            {{-- LOGO OFICIAL COM LINK PARA O SITE PRINCIPAL --}}
            <a href="https://versustcg.com.br" class="flex items-center gap-3 group cursor-pointer">
                <div class="relative w-10 h-10 flex items-center justify-center">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-yellow-500 transform -skew-x-12 rounded-sm shadow-lg shadow-orange-500/20 group-hover:scale-105 transition-transform"></div>
                    <span class="relative z-10 font-black text-black text-xl tracking-tighter italic pr-1">VS</span>
                </div>
                <div class="flex flex-col justify-center">
                    <h1 class="font-black text-2xl text-white tracking-wide italic leading-none group-hover:text-orange-500 transition-colors">
                        VERSUS <span class="text-gray-600 text-lg not-italic font-bold">TCG</span>
                    </h1>
                    <p class="text-[10px] text-gray-400 font-medium tracking-widest uppercase mt-1 opacity-80 hidden sm:block">
                        Um login. Infinitos Universos.
                    </p>
                </div>
            </a>

            {{-- IDENTIFICAÇÃO ESTÁTICA DO DONO (SEM AUTH, SEM LINKS) --}}
            @if(isset($owner))
                <div class="flex items-center gap-3 bg-[#12161f] border border-white/10 py-1.5 px-3.5 rounded-2xl shadow-inner select-none">
                    <div class="text-right">
                        <span class="block text-[9px] font-black uppercase tracking-wider text-gray-500">Coleção de</span>
                        <span class="block text-xs font-bold text-white tracking-wide">
                            {{ $owner->nickname ?: $owner->name }}
                        </span>
                    </div>
                    <div class="w-9 h-9 rounded-full border border-orange-500/40 overflow-hidden bg-black flex items-center justify-center shrink-0 shadow">
                        @if(!empty($owner->avatar))
                            <img src="{{ $owner->avatar }}" alt="{{ $owner->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center font-black text-xs text-white bg-[#ff5500]">
                                {{ strtoupper(substr($owner->name ?? 'P', 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </header>

    {{-- CONTEÚDO DO FICHÁRIO --}}
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 py-6 flex flex-col items-center justify-center">
        {{ $slot }}
    </main>

    {{-- FOOTER DISCRETO --}}
    <footer class="py-4 border-t border-white/5 text-center text-[11px] text-gray-500 font-mono">
        &copy; {{ date('Y') }} Versus TCG &bull; Fichário Virtual Público
    </footer>

    @livewireScripts
    <script src="{{ mix('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
