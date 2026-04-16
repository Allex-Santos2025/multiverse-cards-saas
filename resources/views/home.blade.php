@extends('layouts.app')

@section('title', 'Um login. Infinitos Universos.')

@push('head')
    <style>
        /* =========================================
           COMPONENTES ESPECÍFICOS DA HOME
           ========================================= */
        
        /* EFEITO FOIL BLINDADO */
        .efeito-foil-suave {
            position: relative;
            overflow: hidden;
        }
        .efeito-foil-suave::after {
            content: '';
            position: absolute;
            top: 0; left: -100%; width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            animation: brilho-foil 4s infinite;
            pointer-events: none;
            mix-blend-mode: overlay;
            z-index: 20;
        }
        @keyframes brilho-foil {
            0% { left: -100%; }
            20% { left: 200%; }
            100% { left: 200%; }
        }

        .glow-text {
            text-shadow: 0 0 20px rgba(245, 158, 11, 0.5);
        }

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
    {{-- BANNER PRINCIPAL --}}
    <div class="pt-24 pb-10 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="promo-banner rounded-2xl p-8 md:p-16 flex flex-col md:flex-row items-center justify-between shadow-2xl border border-white/10 group">
                <div class="promo-shine"></div> 
                <div class="relative z-10 max-w-xl text-center md:text-left">
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
        </div>
    </div>

    {{-- SEÇÃO: RECENTEMENTE ADICIONADOS --}}
    @if($ultimasAdicoes->count() > 0)
    <section class="py-12 px-4 bg-[#050505]">
        <div class="max-w-7xl mx-auto">
            <div class="mb-8 flex items-end justify-between border-b border-white/5 pb-4">
                <div>
                    <h2 class="text-2xl font-black italic uppercase tracking-tighter text-white">Recém <span class="text-yellow-500">Chegados</span></h2>
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mt-1">As últimas raridades do estoque</p>
                </div>
                <a href="{{ route('store.catalog.search', ['slug' => $loja->url_slug, 'gameSlug' => 'magic']) }}" class="text-[10px] font-black uppercase text-gray-400 hover:text-yellow-500 transition">Ver Catálogo Completo →</a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                @foreach($ultimasAdicoes as $item)
                    @php
                        // Lógica de Badges para StockItem
                        $extrasRaw = $item->extras;
                        $extrasStr = strtolower(is_array($extrasRaw) ? implode(' ', $extrasRaw) : (string)$extrasRaw);
                        
                        $catalogEtched = $item->catalogPrint->specific->is_etched ?? $item->catalogPrint->specific->has_etched ?? false;
                        $catalogFoil   = $item->catalogPrint->specific->is_foil ?? $item->catalogPrint->specific->has_foil ?? false;

                        $isEtched = str_contains($extrasStr, 'etched') || $catalogEtched;
                        $isFoil   = (str_contains($extrasStr, 'foil') || $catalogFoil) && !$isEtched;

                        // Imagem Final
                        $img = $item->catalogPrint->image_path;
                        $imagemFinal = $img ? (filter_var($img, FILTER_VALIDATE_URL) ? $img : asset($img)) : 'https://placehold.co/250x350/eeeeee/999999?text=X';
                    @endphp

                    <a href="{{ route('store.catalog.product', ['slug' => $loja->url_slug, 'gameSlug' => 'magic', 'conceptSlug' => $item->catalogPrint->concept->slug]) }}" 
                       x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false"
                       class="flex flex-col bg-[#0F1115] rounded-xl shadow-lg border border-white/5 transition-all duration-300 relative group/card"
                       :class="hover ? 'z-[150] shadow-2xl border-yellow-500/50' : 'z-10'">
                        
                        <div class="relative p-3 pb-0 overflow-visible">
                            {{-- BADGES --}}
                            <div class="absolute top-2 left-0 z-[60] flex flex-col gap-1 transition-opacity duration-300" :class="hover ? 'opacity-0' : 'opacity-100'">
                                @if($isFoil)
                                    <span class="bg-gradient-to-r from-amber-200 to-yellow-400 text-yellow-900 text-[9px] font-black px-2 py-0.5 rounded-r shadow-sm">FOIL</span>
                                @endif
                                @if($isEtched)
                                    <span class="bg-gradient-to-r from-amber-500 to-orange-700 text-white text-[9px] font-black px-2 py-0.5 rounded-r shadow-sm">FOIL ETCHED</span>
                                @endif
                            </div>

                            {{-- IMAGEM COM EFEITO --}}
                            <div class="w-full h-auto aspect-[2.5/3.5] rounded-lg shadow-md transition-all duration-500 transform relative z-50 bg-gray-800 {{ $isFoil || $isEtched ? 'efeito-foil-suave border border-yellow-400/20' : '' }}" 
                                 :class="hover ? 'scale-[2] shadow-2xl ring-2 ring-black/20' : ''">
                                <img src="{{ $imagemFinal }}" alt="{{ $item->catalogPrint->printed_name }}" class="w-full h-full object-cover rounded-lg">
                            </div>
                        </div>

                        <div class="p-3 flex flex-col flex-grow justify-between gap-3 transition-opacity duration-300 relative z-[40]" :class="hover ? 'opacity-0' : 'opacity-100'">
                            <div>
                                <h3 class="text-xs font-bold leading-tight line-clamp-2 text-white">{{ $item->catalogPrint->printed_name ?? $item->catalogPrint->concept->name }}</h3>
                                <p class="text-[9px] uppercase font-bold mt-1 text-gray-500">{{ $item->catalogPrint->set->name ?? 'Coleção' }}</p>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-white/5">
                                <span class="text-[10px] font-bold text-gray-400">{{ $item->quantity }} un.</span>
                                <span class="text-sm font-black text-yellow-500">R$ {{ number_format($item->price, 2, ',', '.') }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- PORTAIS DE JOGOS --}}
    <section class="w-full bg-black relative z-10 border-t border-white/10">
        {{-- ... O resto do seu código de portais (Magic, Pokemon, etc) continua igual aqui ... --}}
        <div class="absolute top-4 left-0 w-full text-center z-20 pointer-events-none">
            <h2 class="text-white/30 text-[10px] md:text-xs font-bold tracking-[0.8em] uppercase drop-shadow-md">Selecione o Card Game</h2>
        </div>
        <div class="flex flex-col md:flex-row h-[100vh] md:h-[85vh] w-full bg-black overflow-x-hidden">
            {{-- MAGIC --}}
            <a href="{{ route('marketplace.magic.home') }}" class="relative flex-1 group hover:flex-[12] transition-all duration-700 ease-in-out cursor-pointer border-r border-white/5 overflow-hidden">
                <div class="absolute inset-0 bg-cover bg-center transition-all duration-1000 group-hover:scale-105 grayscale group-hover:grayscale-0" style="background-image: url('{{ asset('assets/magic-background.jpg') }}');"></div>
                <div class="absolute inset-0 bg-black/70 group-hover:bg-black/20 transition-all duration-700"></div>
                <div class="absolute bottom-0 w-full p-6 md:p-12 flex flex-col justify-end h-full opacity-60 group-hover:opacity-100 transition-opacity">
                    <div class="translate-y-10 group-hover:translate-y-0 transition-transform duration-700 delay-100 hidden md:block">
                        <div class="flex flex-col items-start">
                            <span class="text-orange-500 text-xs font-bold tracking-widest border border-orange-500/30 px-2 py-1 rounded bg-black/64">WIZARDS</span>
                            <img src="{{ asset('assets/magic-logo.png') }}" alt="Magic: The Gathering Logo" class="mt-2 max-h-24 w-48 object-contain">
                        </div>
                    </div>
                </div>
            </a>
            {{-- POKEMON --}}
            <a href="#" class="relative flex-1 group hover:flex-[12] transition-all duration-700 ease-in-out cursor-pointer border-r border-white/5 overflow-hidden">
                <div class="absolute inset-0 bg-cover bg-center transition-all duration-1000 group-hover:scale-105 grayscale group-hover:grayscale-0" style="background-image: url('{{ asset('assets/pokemon_TCG-background.png') }}');"></div>
                <div class="absolute inset-0 bg-black/70 group-hover:bg-black/20 transition-all duration-700"></div>
                <div class="absolute bottom-0 w-full p-6 md:p-12 flex flex-col justify-end h-full opacity-60 group-hover:opacity-100 transition-opacity">
                    <div class="translate-y-10 group-hover:translate-y-0 transition-transform duration-700 delay-100 hidden md:block">
                        <div class="flex flex-col items-start">
                            <span class="text-red-500 text-xs font-bold tracking-widest border border-red-500/30 px-2 py-1 rounded bg-black/64">POKÉMON COMPANY</span>
                            <img src="{{ asset('assets/pokemon-logo.png') }}" alt="Pokémon TCG Logo" class="mt-2 max-h-24 w-48 object-contain">
                        </div>
                    </div>
                </div>
            </a>
            {{-- Outros jogos... --}}
        </div>
    </section>

    {{-- COMO FUNCIONA E LOJISTA PARCEIRO --}}
    {{-- ... Mantenha o restante do código que você enviou ... --}}
@endsection