   
   
   @extends('layouts.app')

    @section('title', 'Um login. Infinitos Universos.')

@push('head')
        <style>
            /* =========================================
               COMPONENTES ESPECÍFICOS DA HOME
               ========================================= */
            /* LOGO (Se houver um estilo .logo específico para a Home que não seja Tailwind) */
            /* .logo {
                height: 50px;
                width: 300px;
            } */

            /* GRADIENTES EFEITO GLOW */
            .glow-text {
                text-shadow: 0 0 20px rgba(245, 158, 11, 0.5);
            }

            /* CARD JOGOS (PORTAIS DA HOME) */
            .game-portal {
                transition: all 0.4s ease;
                position: relative;
                overflow: hidden;
                border: 1px solid rgba(255,255,255,0.1);
            }

            .game-portal:hover {
                transform: translateY(-5px);
                border-color: var(--color-primary);
                box-shadow: 0 10px 30px -10px rgba(245, 158, 11, 0.3);
            }

            .game-portal img {
                transition: all 0.5s ease;
                filter: grayscale(100%) brightness(0.6);
            }

            .game-portal:hover img {
                filter: grayscale(0%) brightness(1);
                transform: scale(1.1);
            }

            /* BANNER PROMOCIONAL ANIMADO */
            .promo-banner {
                background: linear-gradient(45deg, #1e1b4b, #312e81);
                position: relative;
                overflow: hidden;
            }

            .promo-shine {
                position: absolute;
                top: 0; left: -100%; width: 50%; height: 100%;
                background: linear-gradient(to right, transparent, rgba(255,255,255,0.1), transparent);
                transform: skewX(-25deg);
                animation: shine 3s infinite;
            }

            @keyframes shine {
                100% { left: 150%; }
            }
        </style>
    @endpush

    @section('content')
        <div class="pt-24 pb-10 px-4">
            <div class="max-w-7xl mx-auto">
                <div class="promo-banner rounded-2xl p-8 md:p-16 flex flex-col md:flex-row items-center justify-between shadow-2xl border border-white/10 group">
                    <div class="promo-shine"></div> <div class="relative z-10 max-w-xl text-center md:text-left">
                        <span class="inline-block px-3 py-1 bg-yellow-500/20 text-yellow-400 rounded-full text-xs font-bold mb-4 border border-yellow-500/20">PROMOÇÃO DE LANÇAMENTO</span>
                        <h1 class="text-4xl md:text-6xl font-black mb-4 leading-tight">
                            Ganhe uma <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-orange-500 glow-text">Booster Box!</span>
                        </h1>
                        <p class="text-indigo-200 text-lg mb-8">
                            A cada R$ 100,00 em compras no marketplace, você ganha um número da sorte. Participe do sorteio inaugural do VERSUS TCG.
                        </p>
                    </div>
                    <div class="relative mt-8 md:mt-0 w-64 h-64 md:w-80 md:h-80 bg-gradient-to-t from-black/50 to-transparent rounded-full flex items-center justify-center border-4 border-white/5">
                        <span class="text-6xl">🎁</span>
                    </div>
                </div>
                <div class="flex justify-center gap-2 mt-6">
                    <div class="w-8 h-1 bg-yellow-500 rounded-full"></div>
                    <div class="w-2 h-1 bg-gray-700 rounded-full"></div>
                    <div class="w-2 h-1 bg-gray-700 rounded-full"></div>
                </div>
            </div>
        </div>
        <section class="w-full bg-black relative z-10 border-t border-white/10">
            <div class="absolute top-4 left-0 w-full text-center z-20 pointer-events-none">
                <h2 class="text-white/30 text-[10px] md:text-xs font-bold tracking-[0.8em] uppercase drop-shadow-md">Selecione o Card Game</h2>
            </div>
            <div class="flex flex-col md:flex-row h-[100vh] md:h-[85vh] w-full bg-black overflow-x-hidden">
                {{-- MAGIC --}}
                <a href="#" class="relative flex-1 group hover:flex-[12] transition-all duration-700 ease-in-out cursor-pointer border-r border-white/5 overflow-hidden">
                    <div 
                        class="absolute inset-0 bg-cover bg-center transition-all duration-1000 group-hover:scale-105 grayscale group-hover:grayscale-0"
                        style="background-image: url('{{ asset('assets/magic-background.jpg') }}');">
                    </div>
                    <div class="absolute inset-0 bg-black/70 group-hover:bg-black/20 transition-all duration-700"></div>
                    <div class="absolute bottom-0 w-full p-6 md:p-12 flex flex-col justify-end h-full opacity-60 group-hover:opacity-100 transition-opacity">
                        <div class="md:hidden lg:block absolute bottom-8 right-1/2 translate-x-1/2 md:right-8 md:translate-x-0 opacity-100 group-hover:opacity-0 transition-opacity duration-300 transform md:-rotate-90 md:origin-bottom-right">
                        {{-- Esta div estava vazia, mantida assim conforme sua estrutura original --}}
                        </div>
                        <div class="translate-y-10 group-hover:translate-y-0 transition-transform duration-700 delay-100 hidden md:block">
                        {{-- Contêiner para o badge e a logo, para melhor alinhamento --}}
                            <div class="flex flex-col items-start"> {{-- Adicionado para alinhar os itens à esquerda --}}
                                {{-- Publisher com nome completo --}}
                                <span class="text-orange-500 text-xs font-bold tracking-widest border border-orange-500/30 px-2 py-1 rounded bg-black/64">WIZARDS</span>
                                {{-- Logo oficial do jogo no lugar do texto "MAGIC" --}}
                                <img src="{{ asset('assets/magic-logo.png') }}" alt="Magic: The Gathering Logo" class="mt-2 max-h-24 w-48 object-contain">
                            </div>
                        </div>
                    </div>
                </a>
                {{-- POKEMON --}}
                <a href="#" class="relative flex-1 group hover:flex-[12] transition-all duration-700 ease-in-out cursor-pointer border-r border-white/5 overflow-hidden">
                    <div 
                        class="absolute inset-0 bg-cover bg-center transition-all duration-1000 group-hover:scale-105 grayscale group-hover:grayscale-0"
                        style="background-image: url('{{ asset('assets/pokemon_TCG-background.png') }}');">
                    </div>
                    <div class="absolute inset-0 bg-black/70 group-hover:bg-black/20 transition-all duration-700"></div>
                    <div class="absolute bottom-0 w-full p-6 md:p-12 flex flex-col justify-end h-full opacity-60 group-hover:opacity-100 transition-opacity">
                        <div class="md:hidden lg:block absolute bottom-8 right-1/2 translate-x-1/2 md:right-8 md:translate-x-0 opacity-100 group-hover:opacity-0 transition-opacity duration-300 transform md:-rotate-90 md:origin-bottom-right">
                        {{-- Esta div estava vazia, mantida assim conforme sua estrutura original --}}
                        </div>
                        <div class="translate-y-10 group-hover:translate-y-0 transition-transform duration-700 delay-100 hidden md:block">
                        {{-- Contêiner para o badge e a logo, para melhor alinhamento --}}
                            <div class="flex flex-col items-start"> {{-- Adicionado para alinhar os itens à esquerda --}}
                                {{-- Publisher com nome completo --}}
                                <span class="text-red-500 text-xs font-bold tracking-widest border border-red-500/30 px-2 py-1 rounded bg-black/64">POKÉMON COMPANY</span>
                                {{-- Logo oficial do jogo no lugar do texto "POKEMON" --}}
                                <img src="{{ asset('assets/pokemon-logo.png') }}" alt="Pokémon TCG Logo" class="mt-2 max-h-24 w-48 object-contain">
                            </div>
                        </div>
                    </div>
                </a>
                {{-- YU-GI-OH! --}}
                <a href="#" class="relative flex-1 group hover:flex-[12] transition-all duration-700 ease-in-out cursor-pointer border-r border-white/5 overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1635326444826-6d2c4b786f79?q=80&w=1000')] bg-cover bg-center transition-all duration-1000 group-hover:scale-105 grayscale group-hover:grayscale-0"></div>
                    <div class="absolute inset-0 bg-black/70 group-hover:bg-black/20 transition-all duration-700"></div>
                    <div class="absolute bottom-0 w-full p-6 md:p-12 flex flex-col justify-end h-full opacity-60 group-hover:opacity-100 transition-opacity">
                        <div class="md:hidden lg:block absolute bottom-8 right-1/2 translate-x-1/2 md:right-8 md:translate-x-0 opacity-100 group-hover:opacity-0 transition-opacity duration-300 transform md:-rotate-90 md:origin-bottom-right">
                            <span class="text-xs font-bold text-white/50 tracking-widest whitespace-nowrap">YGO</span>
                        </div>
                        <div class="translate-y-10 group-hover:translate-y-0 transition-transform duration-700 delay-100 hidden md:block">
                            <span class="text-purple-500 text-xs font-bold tracking-widest border border-purple-500/30 px-2 py-1 rounded bg-black/50">KONAMI</span>
                            <h3 class="text-6xl font-black text-white italic mt-2">YU-GI-OH!</h3>
                            <p class="text-gray-200 mt-2 max-w-sm border-l-2 border-purple-500 pl-3">Hora do Duelo.</p>
                        </div>
                    </div>
                </a>
                {{-- BATTLE SCENES --}}
                <a href="#" class="relative flex-1 group hover:flex-[12] transition-all duration-700 ease-in-out cursor-pointer border-r border-white/5 overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1635326444826-6d2c4b786f79?q=80&w=1000')] bg-cover bg-center transition-all duration-1000 group-hover:scale-105 grayscale group-hover:grayscale-0"></div>
                    <div class="absolute inset-0 bg-black/70 group-hover:bg-black/20 transition-all duration-700"></div>
                    <div class="absolute bottom-0 w-full p-6 md:p-12 flex flex-col justify-end h-full opacity-60 group-hover:opacity-100 transition-opacity">
                        <div class="md:hidden lg:block absolute bottom-8 right-1/2 translate-x-1/2 md:right-8 md:translate-x-0 opacity-100 group-hover:opacity-0 transition-opacity duration-300 transform md:-rotate-90 md:origin-bottom-right">
                            <span class="text-xs font-bold text-white/50 tracking-widest whitespace-nowrap">BATTLE</span>
                        </div>
                        <div class="translate-y-10 group-hover:translate-y-0 transition-transform duration-700 delay-100 hidden md:block">
                            <span class="text-red-600 text-xs font-bold tracking-widest border border-red-600/30 px-2 py-1 rounded bg-black/50">COPAG</span>
                            <h3 class="text-4xl md:text-5xl font-black text-white italic mt-2 leading-tight">BATTLE<br>SCENES</h3>
                            <p class="text-gray-200 mt-2 max-w-sm border-l-2 border-red-600 pl-3">O universo Marvel no Brasil.</p>
                        </div>
                    </div>
                </a>
                {{-- ONE PIECE --}}
                <a href="#" class="relative flex-1 group hover:flex-[12] transition-all duration-700 ease-in-out cursor-pointer border-r border-white/5 overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1635326444826-6d2c4b786f79?q=80&w=1000')] bg-cover bg-center transition-all duration-1000 group-hover:scale-105 grayscale group-hover:grayscale-0"></div>
                    <div class="absolute inset-0 bg-blue-900/20 mix-blend-overlay"></div>
                    <div class="absolute inset-0 bg-black/70 group-hover:bg-black/20 transition-all duration-700"></div>
                    <div class="absolute bottom-0 w-full p-6 md:p-12 flex flex-col justify-end h-full opacity-60 group-hover:opacity-100 transition-opacity">
                        <div class="md:hidden lg:block absolute bottom-8 right-1/2 translate-x-1/2 md:right-8 md:translate-x-0 opacity-100 group-hover:opacity-0 transition-opacity duration-300 transform md:-rotate-90 md:origin-bottom-right">
                            <span class="text-xs font-bold text-white/50 tracking-widest whitespace-nowrap">ONE PIECE</span>
                        </div>
                        <div class="translate-y-10 group-hover:translate-y-0 transition-transform duration-700 delay-100 hidden md:block">
                            <span class="text-blue-400 text-xs font-bold tracking-widest border border-blue-400/30 px-2 py-1 rounded bg-black/50">BANDAI</span>
                            <h3 class="text-5xl font-black text-white italic mt-2">ONE PIECE</h3>
                            <p class="text-gray-200 mt-2 max-w-sm border-l-2 border-blue-400 pl-3">A Era dos Piratas.</p>
                        </div>
                    </div>
                </a>
                {{-- LORCANA --}}
                <a href="#" class="relative flex-1 group hover:flex-[12] transition-all duration-700 ease-in-out cursor-pointer border-r border-white/5 overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1635326444826-6d2c4b786f79?q=80&w=1000')] bg-cover bg-center transition-all duration-1000 group-hover:scale-105 grayscale group-hover:grayscale-0"></div>
                    <div class="absolute inset-0 bg-black/70 group-hover:bg-black/20 transition-all duration-700"></div>
                    <div class="absolute bottom-0 w-full p-6 md:p-12 flex flex-col justify-end h-full opacity-60 group-hover:opacity-100 transition-opacity">
                        <div class="md:hidden lg:block absolute bottom-8 right-1/2 translate-x-1/2 md:right-8 md:translate-x-0 opacity-100 group-hover:opacity-0 transition-opacity duration-300 transform md:-rotate-90 md:origin-bottom-right">
                            <span class="text-xs font-bold text-white/50 tracking-widest whitespace-nowrap">LORCANA</span>
                        </div>
                        <div class="translate-y-10 group-hover:translate-y-0 transition-transform duration-700 delay-100 hidden md:block">
                            <span class="text-teal-400 text-xs font-bold tracking-widest border border-teal-400/30 px-2 py-1 rounded bg-black/50">RAVENSBURGER</span>
                            <h3 class="text-5xl font-black text-white italic mt-2">LORCANA</h3>
                            <p class="text-gray-200 mt-2 max-w-sm border-l-2 border-teal-400 pl-3">Magia Disney.</p>
                        </div>
                    </div>
                </a>
                {{-- FLESH AND BLOOD --}}
                <a href="#" class="relative flex-1 group hover:flex-[12] transition-all duration-700 ease-in-out cursor-pointer border-r border-white/5 overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1635326444826-6d2c4b786f79?q=80&w=1000')] bg-cover bg-center transition-all duration-1000 group-hover:scale-105 grayscale group-hover:grayscale-0"></div>
                    <div class="absolute inset-0 bg-black/70 group-hover:bg-black/20 transition-all duration-700"></div>
                    <div class="absolute bottom-0 w-full p-6 md:p-12 flex flex-col justify-end h-full opacity-60 group-hover:opacity-100 transition-opacity">
                        <div class="md:hidden lg:block absolute bottom-8 right-1/2 translate-x-1/2 md:right-8 md:translate-x-0 opacity-100 group-hover:opacity-0 transition-opacity duration-300 transform md:-rotate-90 md:origin-bottom-right">
                            <span class="text-xs font-bold text-white/50 tracking-widest whitespace-nowrap">FAB</span>
                        </div>
                        <div class="translate-y-10 group-hover:translate-y-0 transition-transform duration-700 delay-100 hidden md:block">
                            <span class="text-red-500 text-xs font-bold tracking-widest border border-red-500/30 px-2 py-1 rounded bg-black/50">LEGEND STORY</span>
                            <h3 class="text-5xl font-black text-white italic mt-2">FLESH AND BLOOD</h3>
                            <p class="text-gray-200 mt-2 max-w-sm border-l-2 border-red-500 pl-3">Combate visceral.</p>
                        </div>
                    </div>
                </a>
                {{-- STAR WARS UNLIMITED --}}
                <a href="#" class="relative flex-1 group hover:flex-[12] transition-all duration-700 ease-in-out cursor-pointer border-r border-white/5 overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1635326444826-6d2c4b786f79?q=80&w=1000')] bg-cover bg-center transition-all duration-1000 group-hover:scale-105 grayscale group-hover:grayscale-0"></div>
                    <div class="absolute inset-0 bg-black/70 group-hover:bg-black/20 transition-all duration-700"></div>
                    <div class="absolute bottom-0 w-full p-6 md:p-12 flex flex-col justify-end h-full opacity-60 group-hover:opacity-100 transition-opacity">
                        <div class="md:hidden lg:block absolute bottom-8 right-1/2 translate-x-1/2 md:right-8 md:translate-x-0 opacity-100 group-hover:opacity-0 transition-opacity duration-300 transform md:-rotate-90 md:origin-bottom-right">
                            <span class="text-xs font-bold text-white/50 tracking-widest whitespace-nowrap">SWU</span>
                        </div>
                        <div class="translate-y-10 group-hover:translate-y-0 transition-transform duration-700 delay-100 hidden md:block">
                            <span class="text-blue-300 text-xs font-bold tracking-widest border border-blue-300/30 px-2 py-1 rounded bg-black/50">FFG / ASMODEE</span>
                            <h3 class="text-5xl font-black text-white italic mt-2">STAR WARS</h3>
                            <p class="text-gray-200 mt-2 max-w-sm border-l-2 border-blue-300 pl-3">Unlimited.</p>
                        </div>
                    </div>
                </a>
                {{-- POKÉMON OCG JAPAN --}}
                <a href="#" class="relative flex-1 group hover:flex-[12] transition-all duration-700 ease-in-out cursor-pointer border-r border-white/5 overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1635326444826-6d2c4b786f79?q=80&w=1000')] bg-cover bg-center transition-all duration-1000 group-hover:scale-105 grayscale group-hover:grayscale-0"></div>
                    <div class="absolute inset-0 bg-black/70 group-hover:bg-black/20 transition-all duration-700"></div>
                    <div class="absolute bottom-0 w-full p-6 md:p-12 flex flex-col justify-end h-full opacity-60 group-hover:opacity-100 transition-opacity">
                        <div class="md:hidden lg:block absolute bottom-8 right-1/2 translate-x-1/2 md:right-8 md:translate-x-0 opacity-100 group-hover:opacity-0 transition-opacity duration-300 transform md:-rotate-90 md:origin-bottom-right">
                            <span class="text-xs font-bold text-white/50 tracking-widest whitespace-nowrap">PKMN JP</span>
                        </div>
                        <div class="translate-y-10 group-hover:translate-y-0 transition-transform duration-700 delay-100 hidden md:block">
                            <span class="text-yellow-200 text-xs font-bold tracking-widest border border-yellow-200/30 px-2 py-1 rounded bg-black/50">OCG / JAPAN</span>
                            <h3 class="text-4xl md:text-5xl font-black text-white italic mt-2">POKÉMON <span class="text-yellow-400 text-2xl not-italic block">OCG JAPAN</span></h3>
                            <p class="text-gray-200 mt-2 max-w-sm border-l-2 border-yellow-200 pl-3">Importados exclusivos.</p>
                        </div>
                    </div>
                </a>
                {{-- YU-GI-OH! OCG JAPAN --}}
                <a href="#" class="relative flex-1 group hover:flex-[12] transition-all duration-700 ease-in-out cursor-pointer border-r border-white/5 overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1635326444826-6d2c4b786f79?q=80&w=1000')] bg-cover bg-center transition-all duration-1000 group-hover:scale-105 grayscale group-hover:grayscale-0"></div>
                    <div class="absolute inset-0 bg-black/70 group-hover:bg-black/20 transition-all duration-700"></div>
                    <div class="absolute bottom-0 w-full p-6 md:p-12 flex flex-col justify-end h-full opacity-60 group-hover:opacity-100 transition-opacity">
                        <div class="md:hidden lg:block absolute bottom-8 right-1/2 translate-x-1/2 md:right-8 md:translate-x-0 opacity-100 group-hover:opacity-0 transition-opacity duration-300 transform md:-rotate-90 md:origin-bottom-right">
                            <span class="text-xs font-bold text-white/50 tracking-widest whitespace-nowrap">YGO JP</span>
                        </div>
                        <div class="translate-y-10 group-hover:translate-y-0 transition-transform duration-700 delay-100 hidden md:block">
                            <span class="text-purple-300 text-xs font-bold tracking-widest border border-purple-300/30 px-2 py-1 rounded bg-black/50">OCG / JAPAN</span>
                            <h3 class="text-4xl md:text-5xl font-black text-white italic mt-2">YU-GI-OH! <span class="text-purple-400 text-2xl not-italic block">OCG JAPAN</span></h3>
                            <p class="text-gray-200 mt-2 max-w-sm border-l-2 border-purple-300 pl-3">Raridades asiáticas.</p>
                        </div>
                    </div>
                </a>
            </div>
        </section>

        {{-- SEÇÃO COMO FUNCIONA --}}
        <section class="how-it-works-section py-16 bg-gray-900 text-gray-200">
            <div class="container mx-auto px-4">
                <h2 class="text-4xl font-bold text-center mb-12 text-white">Como Funciona?</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-8">
                    {{-- Passo 1: Escolha seu Universo --}}
                    <div class="flex flex-col items-center text-center p-4 bg-gray-800 rounded-lg shadow-lg">
                        <div class="icon-placeholder bg-yellow-600 text-white p-4 rounded-full mb-4 shadow-md flex items-center justify-center">
                            {{-- Ícone provisório para "Escolha seu Universo" --}}
                            <span class="text-2xl font-bold">1</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-white">1. Escolha seu Universo</h3>
                        <p class="text-gray-300">Selecione seu Trading Card Game preferido para iniciar sua jornada.</p>
                    </div>
                    {{-- Passo 2: Encontre sua Carta --}}
                    <div class="flex flex-col items-center text-center p-4 bg-gray-800 rounded-lg shadow-lg">
                        <div class="icon-placeholder bg-teal-600 text-white p-4 rounded-full mb-4 shadow-md flex items-center justify-center">
                            {{-- Ícone provisório para "Encontre sua Carta" --}}
                            <span class="text-2xl font-bold">2</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-white">2. Encontre sua Carta</h3>
                        <p class="text-gray-300">Pesquise e descubra a carta exata que você procura em nosso vasto catálogo.</p>
                    </div>
                    {{-- Passo 3: Escolha suas Lojas --}}
                    <div class="flex flex-col items-center text-center p-4 bg-gray-800 rounded-lg shadow-lg">
                        <div class="icon-placeholder bg-purple-600 text-white p-4 rounded-full mb-4 shadow-md flex items-center justify-center">
                            {{-- Ícone provisório para "Escolha suas Lojas" --}}
                            <span class="text-2xl font-bold">3</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-white">3. Escolha suas Lojas</h3>
                        <p class="text-gray-300">Compare e selecione as lojas que oferecem as melhores condições para você.</p>
                    </div>
                    {{-- Passo 4: Monte seu Carrinho --}}
                    <div class="flex flex-col items-center text-center p-4 bg-gray-800 rounded-lg shadow-lg">
                        <div class="icon-placeholder bg-orange-600 text-white p-4 rounded-full mb-4 shadow-md flex items-center justify-center">
                            {{-- Ícone provisório para "Monte seu Carrinho" --}}
                            <span class="text-2xl font-bold">4</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-white">4. Monte seu Carrinho</h3>
                        <p class="text-gray-300">Adicione todos os cards de seu interesse ao carrinho, de diferentes lojas.</p>
                    </div>
                    {{-- Passo 5: Pagamento Unificado --}}
                    <div class="flex flex-col items-center text-center p-4 bg-gray-800 rounded-lg shadow-lg">
                        <div class="icon-placeholder bg-red-600 text-white p-4 rounded-full mb-4 shadow-md flex items-center justify-center">
                            {{-- Ícone provisório para "Pagamento Unificado" --}}
                            <span class="text-2xl font-bold">5</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-white">5. Pagamento Unificado</h3>
                        <p class="text-gray-300">Realize um único pagamento seguro para todos os itens, independentemente da loja.</p>
                    </div>
                    {{-- Passo 6: Receba em Casa --}}
                    <div class="flex flex-col items-center text-center p-4 bg-gray-800 rounded-lg shadow-lg">
                        <div class="icon-placeholder bg-blue-600 text-white p-4 rounded-full mb-4 shadow-md flex items-center justify-center">
                            {{-- Ícone provisório para "Receba em Casa" --}}
                            <span class="text-2xl font-bold">6</span>
                        </div>
                        <h3 class="text-lg font-semibold mb-2 text-white">6. Receba em Casa</h3>
                        <p class="text-gray-300">Aguarde e receba seus pedidos diretamente das lojas, no conforto do seu lar.</p>
                    </div>
                </div>
            </div>
        </section>
        {{-- SEÇÃO LOJISTA PARCEIRO --}}
        <section class="relative py-24 bg-[#050505] overflow-hidden border-t border-white/10" id="lojista">
            <div class="absolute top-0 right-0 w-[800px] h-[800px] bg-blue-600/10 rounded-full blur-[120px] -translate-y-1/2 translate-x-1/3 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-[600px] h-[600px] bg-indigo-600/5 rounded-full blur-[100px] translate-y-1/3 -translate-x-1/4 pointer-events-none"></div>
            <div class="max-w-7xl mx-auto px-6 relative z-10">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <div class="text-left">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-900/30 border border-blue-500/30 text-blue-400 text-xs font-bold tracking-widest uppercase mb-6">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                            Parceiros Versus
                        </div>
                        <h2 class="text-4xl md:text-5xl font-black text-white leading-tight mb-6">
                            Sua loja merece <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-500">estrutura profissional.</span>
                        </h2>
                        <p class="text-lg text-gray-400 mb-8 leading-relaxed max-w-lg">
                            Pare de perder vendas. No <strong>Versus TCG</strong>, você tem gestão de estoque automatizada, proteção contra fraudes e envios integrados.
                        </p>
                        <div class="space-y-4 mb-10">
                            <div class="flex items-start gap-4 p-4 rounded-xl bg-white/5 border border-white/5 hover:border-blue-500/30 transition-colors group">
                                <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-white font-bold text-lg">Cadastro via Modais</h4>
                                    <p class="text-sm text-gray-400 mt-1">Encontre a carta que vc deseja cadastra e escolha apenas o valor, a quantidade e a qualidade, tudo intuitivo e rapido.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4 p-4 rounded-xl bg-white/5 border border-white/5 hover:border-blue-500/30 transition-colors group">
                                <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="text-white font-bold text-lg">Venda Segura</h4>
                                    <p class="text-sm text-gray-400 mt-1">Anti-fraude integrado e gateway de pagamento unificado. Receba por Pix ou Cartão sem dor de cabeça.</p>
                                </div>
                            </div>
                        </div>
                        {{-- Container centralizado com justify-center --}}
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">

                        {{-- Botão único linkado para a rota de planos --}}
                        <a href="{{ route('plans') }}" class="text-gray-400 font-bold py-4 px-6 hover:text-white transition flex items-center gap-2 text-lg group">
                            Conheça nossos planos 
                            {{-- A setinha se move levemente ao passar o mouse (efeito group-hover) --}}
                            <span aria-hidden="true" class="group-hover:translate-x-1 transition-transform">→</span>
                        </a>

                    </div>

                    </div>
                    <div class="relative perspective-[2000px] group">
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-1000"></div>
                        <div class="relative bg-[#0F1115] border border-gray-800 rounded-2xl p-6 shadow-2xl transform rotate-y-[-5deg] rotate-x-[5deg] group-hover:rotate-0 transition-transform duration-700 ease-out">
                            <div class="flex items-center justify-between mb-8 border-b border-gray-800 pb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                    <span class="ml-4 text-xs font-mono text-gray-500">painel.versustcg.com</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gray-700"></div>
                                    <span class="text-xs text-gray-400">Minha Loja</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div class="bg-[#18181b] p-4 rounded-xl border border-gray-800">
                                    <span class="text-xs text-gray-500 uppercase font-bold">Vendas (Dezembro)</span>
                                    <div class="flex items-end justify-between mt-2">
                                        <span class="text-2xl font-mono text-white">R$ 14.250</span>
                                        <span class="text-xs text-green-400 bg-green-400/10 px-2 py-1 rounded">+12%</span>
                                    </div>
                                </div>
                                <div class="bg-[#18181b] p-4 rounded-xl border border-gray-800">
                                    <span class="text-xs text-gray-500 uppercase font-bold">Pedidos Pendentes</span>
                                    <div class="flex items-end justify-between mt-2">
                                        <span class="text-2xl font-mono text-white">8</span>
                                        <span class="text-xs text-blue-400 bg-blue-400/10 px-2 py-1 rounded">Enviar Hoje</span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-[#18181b] p-5 rounded-xl border border-gray-800">
                                <div class="flex justify-between items-center mb-4">
                                    <span class="text-sm font-bold text-white">Desempenho Semanal</span>
                                    <span class="text-xs text-blue-400 cursor-pointer">Ver Relatório</span>
                                </div>
                                <div class="flex items-end justify-between h-32 gap-2">
                                    <div class="w-full bg-gray-800 rounded-t hover:bg-blue-600 transition-colors h-[40%] relative group/bar">
                                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover/bar:opacity-100 transition">Seg</div>
                                    </div>
                                    <div class="w-full bg-gray-800 rounded-t hover:bg-blue-600 transition-colors h-[65%] relative group/bar">
                                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover/bar:opacity-100 transition">Ter</div>
                                    </div>
                                    <div class="w-full bg-gray-800 rounded-t hover:bg-blue-600 transition-colors h-[50%] relative group/bar">
                                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] px-2 py-1 rounded opacity-0 group-hover/bar:opacity-100 transition">Qua</div>
                                    </div>
                                    <div class="w-full bg-blue-600 rounded-t h-[85%] shadow-[0_0_15px_rgba(37,99,235,0.5)] relative group/bar">
                                        <div class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white text-[10px] px-2 py-1 rounded opacity-100">Qui</div>
                                    </div>
                                    <div class="w-full bg-gray-800 rounded-t hover:bg-blue-600 transition-colors h-[60%]"></div>
                                    <div class="w-full bg-gray-800 rounded-t hover:bg-blue-600 transition-colors h-[75%]"></div>
                                    <div class="w-full bg-gray-800 rounded-t hover:bg-blue-600 transition-colors h-[90%]"></div>
                                </div>
                                <div class="border-t border-gray-700 mt-2 pt-2 flex justify-between text-[10px] text-gray-500">
                                    <span>Seg</span><span>Ter</span><span>Qua</span><span>Qui</span><span>Sex</span><span>Sab</span><span>Dom</span>
                                </div>
                            </div>
                        </div>
                        <div class="absolute -right-8 top-1/2 bg-[#18181b] border border-gray-700 p-4 rounded-xl shadow-2xl animate-bounce delay-1000 hidden lg:block">
                            <div class="flex items-center gap-3">
                                <div class="bg-green-500/20 p-2 rounded-full text-green-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Venda Realizada</p>
                                    <p class="text-sm font-bold text-white">Charizard ex (R$ 249,00)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endsection
