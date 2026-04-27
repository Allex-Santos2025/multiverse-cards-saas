@php
    // 1. DETECÇÃO DE CONTEXTO (Lê tanto a Rota quanto a Query String do registro)
    $gameSlugRoute = request()->route('game_slug') ?? request()->query('game_slug');
    $lojaQuerySlug = request()->query('loja');

    // Tenta carregar a loja se ela vier pela URL do Wizard
    if (!isset($loja) && $lojaQuerySlug) {
        $loja = \App\Models\Store::where('url_slug', $lojaQuerySlug)->first();
    }

    // 2. MODO MARKETPLACE
    $isMarketplace = !isset($loja) && ($gameSlugRoute || request()->is('marketplace/*') || request()->is('registro/*'));
    
    // 3. ROTA DE VOLTA INTELIGENTE (Alimenta o botão "Voltar" do Funnel Mode)
    // Se a tela não definiu um backLink manualmente, a gente calcula a origem real
    if (!isset($backLink)) {
        if (isset($loja) && $loja) {
            $backLink = '/' . $loja->url_slug; // Volta para a loja
        } elseif ($gameSlugRoute) {
            $backLink = url('/marketplace/' . $gameSlugRoute); // Volta para o Marketplace do Jogo
        } else {
            $backLink = route('home'); // Volta para a Home Global
        }
    }

    // 4. DEFINE O JOGO E A COR DINÂMICA
    $activeSlug = $gameSlugRoute ?? (request()->is('marketplace/*') ? request()->segment(2) : 'magic');

    $themeColor = match($activeSlug) {
        'magic'   => '#ff5500', // Laranja do Magic
        'pokemon' => '#eab308', // Amarelo do Pokémon
        'yugioh'  => '#3b82f6', // Azul do Yu-Gi-Oh!
        default   => '#ff5500'  // Laranja Padrão Versus
    };
@endphp

<header class="fixed w-full top-0 z-50 bg-[#09090b] backdrop-blur-md border-b border-white/10 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-6 py-4 lg:py-0 h-auto lg:h-20 flex flex-wrap lg:flex-nowrap items-center justify-between gap-4">

        {{-- 1. LOGO (Sempre visível na esquerda) --}}
        <div class="w-full lg:w-1/4 flex justify-center lg:justify-start">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group cursor-pointer z-10">
                <div class="relative w-10 h-10 flex items-center justify-center">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-yellow-500 transform -skew-x-12 rounded-sm shadow-lg shadow-orange-500/20 group-hover:scale-110 transition-transform duration-300"></div>
                    <span class="relative z-10 font-black text-black text-xl tracking-tighter italic pr-1">VS</span>
                </div>
                <div class="flex flex-col justify-center">
                    <h1 class="font-black text-2xl text-white tracking-wide italic leading-none group-hover:text-orange-500 transition-colors">
                        VERSUS <span class="text-gray-600 text-lg not-italic font-bold">TCG</span>
                    </h1>
                    <p class="text-[10px] text-gray-400 font-medium tracking-widest uppercase mt-1 opacity-80 group-hover:opacity-100 transition-opacity hidden lg:block">
                        Um login. Infinitos Universos.
                    </p>
                </div>
            </a>
        </div>

        {{-- 2. CENTRO (Mutante: Funil / Busca Varejo / Institucional) --}}
        @if($funnelMode ?? false)
            <div class="w-full lg:w-2/4 order-3 lg:order-none text-center">
                <span class="text-gray-200 font-bold text-lg tracking-widest uppercase border-b-2 border-orange-500/50 pb-1">
                    {{ $funnelTitle ?? 'Escolha seu Plano' }}
                </span>
            </div>
        @elseif($isMarketplace)
            {{-- MODO MARKETPLACE: Barra de Busca com Arquitetura de Loja e Cor Dinâmica --}}
            <div class="w-full lg:w-2/4 order-3 lg:order-none flex shadow-sm group">
                <input type="text" 
                       class="w-full bg-[#18181b] text-white rounded-l-md px-5 py-2.5 text-sm focus:outline-none focus:brightness-125 transition-all placeholder-gray-500 shadow-inner" 
                       style="border: 1.5px solid {{ $themeColor }}; border-right: none;"
                       placeholder="Buscar cartas, coleções ou produtos...">
                <button class="transition-all text-white px-6 rounded-r-md flex items-center justify-center cursor-pointer hover:brightness-110"
                        style="background-color: {{ $themeColor }}; border: 1.5px solid {{ $themeColor }};">
                    <i class="ph ph-magnifying-glass text-lg font-bold text-white"></i>
                </button>
            </div>
        @else
            {{-- MODO GLOBAL: Navegação Institucional --}}
            <nav class="w-full lg:w-2/4 order-3 lg:order-none hidden md:flex justify-center gap-8 text-sm font-bold text-gray-400">
                <a href="{{ route('events.index') }}" class="hover:text-white transition flex items-center gap-2">Eventos</a>
                <a href="#lojista" class="text-orange-500 hover:text-orange-400 transition font-bold border border-orange-500/20 px-3 py-1 rounded-full hover:bg-orange-500/10">Área do Lojista</a>
            </nav>
        @endif

        {{-- 3. DIREITA (Login, Avatar e Carrinho com o Padrão da Loja) --}}
        <div class="w-full lg:w-1/4 flex justify-center lg:justify-end items-center order-2 lg:order-none z-[100] text-gray-300" style="gap: 20px;">
            @if($funnelMode ?? false)
                <a href="{{ $backLink ?? route('home') }}" class="text-sm font-bold text-gray-300 hover:text-white hover:opacity-100 transition-all flex items-center gap-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover:-translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar
                </a>
            @else
                
                @auth('player')
                    {{-- Bloco do Usuário Logado --}}
                    <div class="relative group">
                        <button class="flex flex-col items-center text-gray-300 hover:text-white hover:opacity-100 transition-all cursor-pointer">
                            <div class="relative h-9 flex items-end justify-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs uppercase shadow-sm border border-white/20 bg-[#ff5500] text-[#18181b] mb-0.5">
                                    {{ substr(auth('player')->user()->name, 0, 1) }}
                                </div>
                            </div>
                            <span class="text-xs font-bold mt-1.5 uppercase tracking-wider leading-none truncate max-w-[120px]">OLÁ, {{ explode(' ', auth('player')->user()->name)[0] }}</span>
                        </button>
                        
                        <div class="absolute right-0 top-full pt-2 hidden group-hover:block z-[100]">
                            <livewire:lobby.player-dropdown :isMarketplace="true" :loja="null" />
                        </div>
                    </div>
                @else
                    @if($isMarketplace)
                        {{-- MODO VAREJO: Ícone "Entrar" --}}
                        <button 
                            @click="$dispatch('open-login-modal')" 
                            class="flex flex-col items-center text-gray-300 hover:text-white hover:opacity-100 transition-all bg-transparent border-none cursor-pointer"
                        >
                            <div class="relative h-9 flex items-end justify-center">
                                <i class="ph ph-user text-[28px] leading-none"></i>
                            </div>
                            <span class="text-xs font-bold mt-1.5 uppercase tracking-wider leading-none">ENTRAR</span>
                        </button>
                    @else
                        {{-- MODO INSTITUCIONAL: Botões de Landing Page --}}
                        <button onclick="Livewire.dispatch('open-login-modal')" class="text-sm font-bold text-gray-300 hover:text-white hover:opacity-100 transition-all whitespace-nowrap">Entrar</button>
                        <button onclick="Livewire.dispatch('open-auth-modal')" class="bg-white text-black px-6 py-2 rounded-full font-bold hover:bg-gray-200 transition-all shadow-[0_0_15px_rgba(255,255,255,0.1)] transform hover:scale-105 whitespace-nowrap">Criar Conta</button>
                    @endif
                @endauth
                
                {{-- Carrinho (Sempre visível no Marketplace) --}}
                @if($isMarketplace)
                    @php
                        $lojaParaCarrinho = isset($loja) && $loja->id ? $loja : null;
                    @endphp
                    <livewire:store.template.cart.dropdown :loja="$lojaParaCarrinho" :key="'cart-global-'.($lojaParaCarrinho?->id ?? 'marketplace')" />
                @endif

            @endif
        </div>

    </div>
</header>