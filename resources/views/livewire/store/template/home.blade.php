{{-- ========================================================================== --}}
{{-- A REGRA DE OURO DO LIVEWIRE: UMA DIV PARA GOVERNAR TODAS                   --}}
{{-- ========================================================================== --}}
<div>

    {{-- ========================================================================== --}}
    {{-- 1. HERO BANNER & OFERTAS SELADOS (TOPO)                                    --}}
    {{-- ========================================================================== --}}
    <section class="max-w-7xl mx-auto px-4 py-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
            
            {{-- Banner Principal --}}
            <div class="lg:col-span-8 rounded-lg overflow-hidden relative group cursor-pointer shadow-md bg-gray-900 min-h-[350px] flex items-center justify-center border border-gray-800">
                <img src="https://images.unsplash.com/photo-1605806616949-1e87b487cb2a?q=80&w=1000&auto=format&fit=crop" class="absolute inset-0 w-full h-full object-cover opacity-40 group-hover:scale-105 transition-transform duration-700">
                <div class="text-center z-10 px-4 relative">
                    <span class="bg-main-1 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider mb-3 inline-block font-black">Lançamento</span>
                    <h2 class="text-white text-4xl md:text-5xl font-black uppercase tracking-tight text-shadow">Pré-venda: Phyrexia</h2>
                    <p class="text-gray-200 mt-2 text-lg md:text-xl font-black uppercase">Garanta suas caixas com preço especial.</p>
                </div>
            </div>

            {{-- Sidebar Direita (Selados/Acessórios) --}}
            <div class="lg:col-span-4 flex flex-col gap-4">
                {{-- Card Promo Selado --}}
                <div class="bg-main-1 rounded-lg p-5 shadow-md flex-1 flex flex-col justify-center relative overflow-hidden bg-cover bg-center border border-gray-800" style="background-image: linear-gradient(to right, rgba(0,0,0,0.8), rgba(0,0,0,0.4)), url('https://images.unsplash.com/photo-1611996575749-79a3a250f948?q=80&w=500&auto=format&fit=crop');">
                    <span class="bg-accent-1 text-xs font-bold px-2 py-1 rounded uppercase tracking-wider w-max mb-2 relative z-10 font-black">Desconto Extra</span>
                    <h3 class="text-white text-2xl font-black relative z-10 uppercase">Booster Box</h3>
                    <p class="text-sm text-gray-200 mb-4 relative z-10 uppercase font-bold text-xs">Levando 2, a 3ª sai com 50% OFF</p>
                    <button class="bg-white text-main-1 font-black py-2 px-4 rounded w-max hover:bg-gray-100 transition relative z-10 text-[10px] uppercase">Ver Ofertas</button>
                </div>

                {{-- Card Acessórios --}}
                <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex-1 flex items-center justify-between group cursor-pointer hover:border-main-1 transition-colors">
                    <div>
                        <h3 class="font-black text-gray-800 text-lg uppercase tracking-tighter">Acessórios Premium</h3>
                        <p class="text-sm text-gray-500 uppercase font-bold text-[10px]">Sleeves, Playmats e mais.</p>
                    </div>
                    <div class="bg-gray-50 p-2 rounded-full group-hover:bg-main-1 transition-colors">
                        <i class="ph ph-arrow-right text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ========================================================================== --}}
    {{-- 2. CORPO DA LOJA (SIDEBAR + PRATELEIRAS)                                   --}}
    {{-- ========================================================================== --}}
    <section class="max-w-7xl mx-auto px-4 py-4 mb-32 relative z-0">
        <div class="flex flex-col lg:flex-row gap-8 overflow-visible">
            
            {{-- LADO ESQUERDO: SIDEBAR DE PROMOÇÕES --}}
            <div class="w-full lg:w-1/4">
                <div class="sticky top-24 flex flex-col gap-6 overflow-visible">
                    
                    <div class="border-b-2 border-gray-800 pb-2 mb-2">
                        <h2 class="text-xl font-black text-gray-800 uppercase tracking-tight flex items-center font-black">
                            <i class="ph-fill ph-lightning text-accent-1 text-2xl mr-2"></i> Ofertas do Dia
                        </h2>
                    </div>

                    {{-- Oferta 1: Black Lotus (Mantém tons de alerta vermelhos fixos para "Tempo esgotando") --}}
                    <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false" 
                         class="bg-white border border-red-200 rounded-lg shadow-md relative block cursor-pointer transition-all duration-300"
                         :class="open ? 'z-[100] border-red-500 shadow-2xl' : 'z-20'">
                        <div class="bg-red-600 text-white text-center py-2 text-sm font-bold uppercase tracking-widest relative z-30 font-black">
                            Termina em: <div class="flex justify-center space-x-2 mt-1 text-lg font-black"><span>04h</span>:<span>12m</span>:<span>59s</span></div>
                        </div>
                        <div class="p-4 flex flex-col items-center relative">
                            <img src="https://cards.scryfall.io/large/front/b/4/b4aad0f4-563d-4e46-af98-2e9e6ab6548d.jpg?1562933099" 
                                 class="w-32 rounded-md mb-3 shadow-sm transform transition-all duration-500 relative z-20"
                                 :class="open ? 'scale-[1.4] -rotate-0 shadow-2xl ring-1 ring-black' : '-rotate-2'">
                            <h3 class="font-black text-gray-800 text-center leading-tight transition-colors uppercase text-sm" :class="open ? 'text-red-600' : ''">Black Lotus (Alpha)</h3>
                            <div class="text-center w-full mt-2">
                                <span class="text-xs text-gray-400 line-through font-bold uppercase tracking-tight">R$ 15.000,00</span>
                                <div class="text-2xl font-black text-red-600 tracking-tighter">R$ 12.500,00</div>
                            </div>
                        </div>
                    </div>

                    {{-- Oferta 2: Charizard (Destaque atrelado à marca da loja) --}}
                    <div x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false"
                         class="bg-white border border-gray-200 rounded-lg shadow-sm relative block cursor-pointer transition-all duration-300"
                         :class="open ? 'z-[100] border-main-1 shadow-2xl' : 'z-20'">
                        <div class="bg-main-1 text-center py-1 text-xs font-bold uppercase relative z-30 tracking-widest font-black">Destaque da Semana</div>
                        <div class="p-4 flex flex-col items-center">
                            <img src="https://images.pokemontcg.io/base1/4_hires.png" 
                                 class="w-28 rounded-md mb-3 shadow-sm transition-all duration-500 relative z-20"
                                 :class="open ? 'scale-[1.4] shadow-2xl ring-1 ring-black' : ''">
                            <h3 class="font-black text-gray-800 text-center leading-tight transition-colors uppercase text-sm" :class="open ? 'text-main-1' : ''">Charizard - Base Set</h3>
                            <div class="text-xl font-black text-main-1 mt-2 tracking-tighter">R$ 2.450,00</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- LADO DIREITO: AS PRATELEIRAS --}}
            <div class="w-full lg:w-3/4 flex flex-col gap-24 overflow-visible">
                
                {{-- 2.1 PRATELEIRA: ÚLTIMAS ADIÇÕES (DINÂMICO DO BANCO) --}}
                <div class="relative overflow-visible">
                    <div class="flex justify-between items-end mb-6 border-b border-gray-200 pb-2">
                        <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tight">Últimas Adições</h2>
                        <a href="#" class="text-main-1 text-xs font-black hover:underline uppercase tracking-widest">Ver todas</a>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-6 overflow-visible">
                        @forelse ($ultimasAdicoes as $item)
                        @php
                            $nomeItem = $item->catalogPrint->printed_name 
                                     ?? $item->concept->name 
                                     ?? 'Nome Indisponível';

                            $imagemBruta = $item->catalogPrint->image_url 
                                        ?? $item->catalogPrint->image_path 
                                        ?? $item->concept->image_url 
                                        ?? $item->concept->image_path 
                                        ?? 'https://cards.scryfall.io/large/front/3/4/3462a3d0-5552-49fa-9eb7-100960c55891.jpg?1562828007';

                            $imagemFinal = filter_var($imagemBruta, FILTER_VALIDATE_URL) 
                                         ? $imagemBruta 
                                         : asset($imagemBruta); 
                        @endphp

                        <a href="#" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false"
                           class="block bg-white border border-gray-100 rounded-xl p-3 shadow-sm transition-all duration-300 h-full flex flex-col justify-between relative"
                           :class="hover ? 'z-[150] shadow-2xl border-main-1' : 'z-10'">
                            
                            <div class="relative aspect-[2.5/3.5] bg-gray-50 mb-4 overflow-visible">
                                {{-- FLAGS --}}
                                <div class="absolute top-2 left-0 z-[60] flex flex-col gap-1 transition-opacity duration-300" :class="hover ? 'opacity-0' : 'opacity-100'">
                                    @if($item->is_promotion && $item->discount_percent > 0)
                                        <span class="bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded-r shadow-md uppercase tracking-tighter">-{{ number_format($item->discount_percent, 0) }}%</span>
                                    @endif

                                    @if(!empty($item->extras['foil']))
                                        <span class="bg-gradient-to-r from-yellow-400 to-yellow-600 text-white text-[10px] font-black px-2 py-0.5 rounded-r shadow-md uppercase tracking-widest">Foil</span>
                                    @endif
                                </div>
                                
                                {{-- QUANTIDADE --}}
                                <div class="absolute bottom-2 right-2 bg-gray-900/80 backdrop-blur-md border border-white/20 text-white text-center px-2 py-1 rounded-lg shadow-lg z-[60] min-w-[2.8rem] transition-opacity duration-300" :class="hover ? 'opacity-0' : 'opacity-100'">
                                    <span class="block text-sm font-black leading-none tracking-tight">{{ $item->quantity }}</span>
                                    <span class="block text-[8px] uppercase tracking-tighter text-gray-300 mt-0.5 font-bold">UNID</span>
                                </div>
                                
                                <img src="{{ $imagemFinal }}" 
                                     class="w-full h-full object-cover rounded-lg shadow-sm transition-all duration-500 transform relative z-50"
                                     :class="hover ? 'scale-[1.7] shadow-[0_25px_60px_rgba(0,0,0,0.8)] ring-1 ring-black' : ''">
                            </div>
                            
                            <div class="text-center mt-auto transition-opacity duration-300" :class="hover ? 'opacity-0' : 'opacity-100'">
                                <h3 class="text-xs font-bold text-gray-600 line-clamp-1 uppercase px-1" title="{{ $nomeItem }}">{{ $nomeItem }}</h3>
                                
                                @if($item->is_promotion && $item->discount_percent > 0)
                                    <span class="text-[10px] text-gray-400 line-through font-bold uppercase tracking-tight block mt-1">R$ {{ number_format($item->price, 2, ',', '.') }}</span>
                                    <p class="text-lg font-black text-main-1 mt-0 tracking-tighter">R$ {{ number_format($item->final_price, 2, ',', '.') }}</p>
                                @else
                                    <p class="text-lg font-black text-main-1 mt-1 tracking-tighter">R$ {{ number_format($item->price, 2, ',', '.') }}</p>
                                @endif
                            </div>
                        </a>
                        @empty
                            <div class="col-span-full py-12 text-center text-gray-500 border-2 border-dashed border-gray-200 rounded-lg">
                                <i class="ph ph-cards text-4xl mb-2 text-gray-300"></i>
                                <p class="font-bold">Nenhuma carta em estoque ainda.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- 2.2 PRATELEIRAS CONFIGURÁVEIS (Estáticas por enquanto) --}}
                @foreach(['Destaques Pokémon', 'Mais Procuradas', 'Commander Staples', 'Yu-Gi-Oh! TCG', 'Energias & Terrenos'] as $titulo)
                <div class="relative overflow-visible">
                    <div class="flex justify-between items-end mb-4 border-b border-gray-200 pb-2">
                        <h2 class="text-2xl font-black text-gray-800 uppercase tracking-tight">{{ $titulo }}</h2>
                    </div>
                    <div class="relative group">
                        <button class="absolute -left-6 top-1/2 -translate-y-1/2 z-[200] bg-white shadow-lg rounded-full w-10 h-10 text-gray-400 hover:text-main-1 hidden lg:flex items-center justify-center border border-gray-100 transition-all hover:scale-110 -mt-6"><i class="ph ph-caret-left text-2xl"></i></button>
                        
                        <div class="flex lg:grid lg:grid-cols-5 gap-4 lg:gap-6 overflow-x-auto lg:overflow-visible snap-x snap-mandatory pb-8 lg:pb-0 pt-4 px-2 -mx-2 lg:px-0 lg:mx-0" style="scrollbar-width: none; -ms-overflow-style: none;">
                            @for ($i = 0; $i < 5; $i++)
                            <div class="flex-none w-[80%] sm:w-[45%] lg:w-full snap-start py-2 lg:py-0 overflow-visible">
                                <a href="#" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false"
                                   class="block bg-white border border-gray-100 rounded-xl p-3 shadow-sm transition-all duration-300 h-full flex flex-col justify-between relative"
                                   :class="hover ? 'z-[150] shadow-2xl border-main-1' : 'z-10'">
                                    
                                    <div class="relative aspect-[2.5/3.5] bg-gray-50 mb-3 overflow-visible">
                                        <div class="absolute bottom-2 right-2 bg-gray-900/80 backdrop-blur-md border border-white/20 text-white text-center px-2 py-1 rounded-lg shadow-lg z-[60] min-w-[2.8rem] transition-opacity duration-300" :class="hover ? 'opacity-0' : 'opacity-100'">
                                            <span class="block text-sm font-black leading-none uppercase">1</span>
                                            <span class="block text-[8px] uppercase tracking-tighter text-gray-300 mt-0.5 font-bold">UNID</span>
                                        </div>
                                        <img src="https://images.pokemontcg.io/swsh4/44_hires.png" 
                                             class="w-full h-full object-cover rounded-lg shadow-sm transition-all duration-500 transform relative z-50"
                                             :class="hover ? 'scale-[1.7] shadow-[0_25px_60px_rgba(0,0,0,0.8)] ring-1 ring-black' : ''">
                                    </div>
                                    
                                    <div class="text-center mt-auto transition-opacity duration-300" :class="hover ? 'opacity-0' : 'opacity-100'">
                                        <h3 class="text-xs font-bold text-gray-600 line-clamp-1 uppercase px-1 font-black">Pikachu VMAX</h3>
                                        <p class="text-lg font-black text-main-1 mt-1 tracking-tighter">R$ 120,00</p>
                                    </div>
                                </a>
                            </div>
                            @endfor
                        </div>

                        <button class="absolute -right-6 top-1/2 -translate-y-1/2 z-[200] bg-white shadow-lg rounded-full w-10 h-10 text-gray-400 hover:text-main-1 hidden lg:flex items-center justify-center border border-gray-100 transition-all hover:scale-110 -mt-6"><i class="ph ph-caret-right text-2xl"></i></button>
                    </div>
                </div>
                @endforeach

            </div>
        </div>
    </section>

    {{-- ========================================================================== --}}
    {{-- 3. AVALIAÇÕES                                                              --}}
    {{-- ========================================================================== --}}
    <section class="max-w-7xl mx-auto px-4 py-12 border-t border-gray-200">
        <h2 class="text-2xl font-black text-center text-gray-800 uppercase tracking-tight mb-8">O que dizem nossos clientes</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @for ($i = 0; $i < 3; $i++)
            <div class="bg-white border border-gray-100 p-6 rounded-lg shadow-sm hover:shadow-md transition">
                <div class="flex text-accent-1 mb-3 text-lg"><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i></div>
                <p class="text-gray-600 text-sm italic font-medium uppercase text-xs">"Cartas chegaram perfeitas, embalagem muito segura com toploader e envio rápido!"</p>
                <div class="mt-4 flex items-center gap-3">
                    <div class="w-8 h-8 bg-main-1 rounded-full text-white flex items-center justify-center font-black text-xs uppercase">VS</div>
                    <p class="font-black text-sm text-gray-800 uppercase tracking-tighter">Cliente Versus</p>
                </div>
            </div>
            @endfor
        </div>
    </section>

    {{-- ========================================================================== --}}
    {{-- 4. NEWSLETTER                                                              --}}
    {{-- ========================================================================== --}}
    <section class="bg-secondary-1 py-12 border-t-4 border-main-1 relative z-0">
        <div class="max-w-3xl mx-auto px-4 text-center">
            <h2 class="text-2xl md:text-3xl font-black text-white uppercase mb-6 tracking-tight">Inscreva-se na nossa Newsletter</h2>
            <form class="flex flex-col sm:flex-row gap-2 max-w-xl mx-auto">
                <input type="email" placeholder="Seu melhor e-mail..." class="flex-1 px-4 py-3 rounded-md focus:outline-none focus:border-main-1 focus:ring-1 focus:ring-main-1 text-gray-800 font-bold uppercase text-[10px]">
                <button type="button" class="bg-accent-1 hover:opacity-80 text-gray-900 font-black px-8 py-3 rounded-md transition-colors uppercase tracking-wide">Assinar</button>
            </form>
        </div>
    </section>

</div>
{{-- ========================================================================== --}}
{{-- FIM DA DIV PAI DO LIVEWIRE                                                 --}}
{{-- ========================================================================== --}}