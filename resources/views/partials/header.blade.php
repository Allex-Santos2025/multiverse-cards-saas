<header class="fixed w-full top-0 z-50 bg-black/90 backdrop-blur-md border-b border-white/10 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between relative">

        {{-- 1. LOGO (Sempre visível na esquerda) --}}
        <a href="{{ route('home') }}" class="flex items-center gap-3 group cursor-pointer z-10">
            <div class="relative w-10 h-10 flex items-center justify-center">
                <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-yellow-500 transform -skew-x-12 rounded-sm shadow-lg shadow-orange-500/20 group-hover:scale-110 transition-transform duration-300"></div>
                <span class="relative z-10 font-black text-black text-xl tracking-tighter italic pr-1">VS</span>
            </div>
            <div class="flex flex-col justify-center">
                <h1 class="font-black text-2xl text-white tracking-wide italic leading-none group-hover:text-orange-500 transition-colors">
                    VERSUS <span class="text-gray-600 text-lg not-italic font-bold">TCG</span>
                </h1>
                <p class="text-[10px] text-gray-400 font-medium tracking-widest uppercase mt-1 opacity-80 group-hover:opacity-100 transition-opacity">
                    Um login. Infinitos Universos.
                </p>
            </div>
        </a>

        {{-- 2. CENTRO (Alterna entre Menu ou Título do Funil) --}}
        @if($funnelMode ?? false)
            {{-- MODO FUNIL: Título Centralizado (Absoluto para garantir o centro exato) --}}
            <div class="absolute left-1/2 top-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center hidden md:block">
                <span class="text-gray-200 font-bold text-lg tracking-widest uppercase border-b-2 border-orange-500/50 pb-1">
                    {{ $funnelTitle ?? 'Escolha seu Plano' }}
                </span>
            </div>
        @else
            {{-- MODO PADRÃO: Menu de Navegação --}}
            <nav class="hidden md:flex gap-8 text-sm font-bold text-gray-400">
                <a href="#eventos" class="hover:text-white transition flex items-center gap-2">Eventos</a>
                <a href="#lojista" class="text-orange-500 hover:text-orange-400 transition font-bold border border-orange-500/20 px-3 py-1 rounded-full hover:bg-orange-500/10">Área do Lojista</a>
            </nav>
        @endif

        {{-- 3. DIREITA (Alterna entre Login ou Botão Voltar) --}}
        <div class="flex items-center gap-4 z-10">
            @if($funnelMode ?? false)
                {{-- MODO FUNIL: Botão Voltar --}}
                <a href="{{ $backLink ?? route('home') }}" class="text-sm font-bold text-gray-400 hover:text-white transition flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar
                </a>
            @else
                {{-- MODO PADRÃO: Botões de Auth --}}
                <button class="text-sm font-bold text-gray-300 hover:text-white transition">Entrar</button>
                <button class="bg-white text-black px-6 py-2 rounded-full font-bold hover:bg-gray-200 transition shadow-[0_0_15px_rgba(255,255,255,0.1)] transform hover:scale-105">Criar Conta</button>
            @endif
        </div>

    </div>
</header>
