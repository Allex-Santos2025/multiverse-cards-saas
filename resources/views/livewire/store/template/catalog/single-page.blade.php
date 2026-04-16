<div>
    {{-- CSS DO EFEITO FOIL (Cards Menores) --}}
    <style>
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
    </style>

    {{-- BREADCRUMB (Fundo: Secundária | Texto: Contraste da Secundária) --}}
    <div class="py-2" style="background-color: var(--cor-secundaria); color: var(--cor-texto-secundaria);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-xs font-bold flex gap-2 items-center">
                <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" class="hover:underline opacity-90">Home</a>
                <span class="opacity-50">></span>
                <a href="#" class="hover:underline opacity-90">{{ ucfirst($gameSlug) }}</a>
                <span class="opacity-50">></span>
                <span>Cartas Avulsas</span>
            </nav>
        </div>
    </div>

    {{-- MIOLO PRINCIPAL --}}
    <div class="max-w-7xl mx-auto px-4 py-8">

        {{-- TÍTULO --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black italic uppercase tracking-tight" style="color: var(--cor-texto-principal);">
                CARTAS <span style="color: var(--cor-cta);">AVULSAS</span>
            </h1>
            <h2 class="text-sm mt-1 uppercase font-bold opacity-60" style="color: var(--cor-texto-principal);">Catálogo Global</h2>
        </div>

        {{-- BARRA DE FILTROS --}}
        <div class="mb-8 p-4 rounded-xl flex flex-wrap gap-4 items-center justify-between shadow-md transition-colors duration-300" 
             style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);">

            <div class="flex flex-wrap items-center gap-4 w-full md:w-auto">
                <span class="text-[10px] font-bold uppercase opacity-80" style="color: inherit;">Filtros:</span>
                
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase opacity-80" style="color: inherit;">Ordenar:</span>
                    <select wire:model="sortOrder" class="bg-white text-gray-900 border-gray-300 rounded-md text-sm px-3 py-1.5 focus:ring-2" style="--tw-ring-color: var(--cor-secundaria);">
                        <option value="name_asc">Nome [A-Z]</option>
                        <option value="name_desc">Nome [Z-A]</option>
                        <option value="price_asc">Menor Preço</option>
                        <option value="price_desc">Maior Preço</option>
                        @if($desagrupar)
                            <option value="number_asc">Número [1-9]</option>
                            <option value="number_desc">Número [9-1]</option>
                        @endif
                    </select>
                </div>

                {{-- Só mostra Raridade se estiver Desagrupado --}}
                @if($desagrupar)
                <div class="flex items-center gap-2 transition-all">
                    <span class="text-[10px] font-bold uppercase opacity-80" style="color: inherit;">Raridade:</span>
                    <select wire:model="raridade" class="bg-white text-gray-900 border-gray-300 rounded-md text-sm px-3 py-1.5 focus:ring-2" style="--tw-ring-color: var(--cor-secundaria);">
                        <option value="todas">Todas</option>
                        <option value="mythic">Mítica</option>
                        <option value="rare">Rara</option>
                        <option value="uncommon">Incomum</option>
                        <option value="common">Comum</option>
                    </select>
                </div>
                @endif

                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase opacity-80" style="color: inherit;">Cor:</span>
                    <select wire:model="cor" class="bg-white text-gray-900 border-gray-300 rounded-md text-sm px-3 py-1.5 focus:ring-2" style="--tw-ring-color: var(--cor-secundaria);">
                        <option value="todas">Todas</option>
                        <option value="W">Branco</option>
                        <option value="U">Azul</option>
                        <option value="B">Preto</option>
                        <option value="R">Vermelho</option>
                        <option value="G">Verde</option>
                        <option value="M">Multicolor</option>
                        <option value="C">Incolor</option>
                        <option value="A">Artefatos</option>
                        <option value="L">Terrenos</option>
                    </select>
                </div>

                {{-- CHECKBOXES DE CONTROLE --}}
                <div class="flex items-center gap-4 ml-2 border-l border-white/20 pl-4">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="com_estoque" wire:model="com_estoque" class="w-4 h-4 bg-white border-gray-400 rounded focus:ring-2 cursor-pointer" style="color: var(--cor-secundaria); --tw-ring-color: var(--cor-secundaria);">
                        <label for="com_estoque" class="text-xs font-bold uppercase cursor-pointer" style="color: inherit;">Com estoque</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="desagrupar" wire:model="desagrupar" class="w-4 h-4 bg-white border-gray-400 rounded focus:ring-2 cursor-pointer" style="color: var(--cor-secundaria); --tw-ring-color: var(--cor-secundaria);">
                        <label for="desagrupar" class="text-xs font-bold uppercase cursor-pointer" style="color: inherit;" title="Mostra cada edição da carta separadamente">Desagrupar Versões</label>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 w-full md:w-auto mt-4 md:mt-0">
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase opacity-80" style="color: inherit;">Exibir:</span>
                    <select wire:model="perPage" class="bg-white text-gray-900 border-gray-300 rounded-md text-sm px-3 py-1.5 focus:ring-2" style="--tw-ring-color: var(--cor-secundaria);">
                        <option value="30">30</option>
                        <option value="60">60</option>
                        <option value="90">90</option>
                        <option value="120">120</option>
                    </select>
                </div>

                <button wire:click="$refresh" class="font-bold py-1.5 px-6 rounded-lg transition-colors hover:opacity-90 uppercase text-xs shadow-sm cursor-pointer" style="background-color: var(--cor-secundaria); color: var(--cor-texto-secundaria);">
                    Filtrar
                </button>
            </div>
        </div>
        
        {{-- INFO PAGINAÇÃO E RESULTADOS --}}
        <div class="flex justify-between items-center mb-6 text-xs font-bold border-b border-gray-200 pb-2" style="color: var(--cor-texto-principal); opacity: 0.7;">
            <span class="uppercase">1-{{ $perPage ?? 30 }} de <strong style="color: var(--cor-1);">{{ method_exists($cartas, 'total') ? number_format($cartas->total(), 0, ',', '.') : $cartas->count() }}</strong> CARDS</span>

            @if(method_exists($cartas, 'links'))
                <div class="flex items-center gap-2">
                    {{ $cartas->links('livewire.store.template.components.custom-pagination') }}
                </div>
            @endif
        </div>

        {{-- GRID DE CARTAS --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 xl:grid-cols-5 gap-4 lg:gap-6 pt-4 pb-12">
            @forelse($cartas as $carta)

                {{-- LÓGICA BLINDADA: Define Etched e Foil anulando o outro --}}
                @php
                    $extrasEstoque = is_array($carta->menor_preco_extras ?? null) ? implode(' ', $carta->menor_preco_extras) : ($carta->menor_preco_extras ?? '');
                    $extrasEsgotado = is_array($carta->ultimo_preco_extras ?? null) ? implode(' ', $carta->ultimo_preco_extras) : ($carta->ultimo_preco_extras ?? '');
                    
                    $extrasStr = strtolower($extrasEstoque . ' ' . $extrasEsgotado);

                    $isEtched = str_contains($extrasStr, 'etched') || ($carta->is_etched ?? false);
                    $isFoil   = (str_contains($extrasStr, 'foil') || ($carta->is_foil ?? false)) && !$isEtched;
                @endphp

                {{-- ESTADO 1: Morfídeo (Sem estoque, sem preço) --}}
                @if($carta->total_estoque === 0 && !$carta->ultimo_preco)
                    <a href="{{ route('store.catalog.product', ['slug' => $loja->url_slug,'gameSlug' => $gameSlug, 'conceptSlug' => $carta->slug_seguro ]) }}" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" 
                         class="flex flex-col rounded-xl border transition-all duration-300 relative opacity-60 grayscale-[80%] hover:grayscale-[50%] bg-black/5 dark:bg-white/5 border-gray-200/50 dark:border-slate-700/50 cursor-default"
                         :class="hover ? 'z-[50] shadow-md' : 'z-10'">
                        <div class="relative p-3 pb-0 overflow-visible">
                            <img src="{{ $carta->imagem_final }}" alt="{{ $carta->nome_localizado }}" class="w-full h-auto aspect-[2.5/3.5] object-cover rounded-lg shadow-sm transition-all duration-500 relative z-50" onerror="this.onerror=null; this.src='https://placehold.co/250x350/eeeeee/999999?text=Erro+na+Foto';">
                        </div>
                        <div class="p-3 flex flex-col flex-grow justify-between gap-3">
                            <div>
                                <h3 class="text-xs font-bold leading-tight line-clamp-2 text-inherit opacity-80" style="color: var(--cor-texto-principal);" title="{{ $carta->nome_localizado }}">{{ $carta->nome_localizado }}</h3>
                                <p class="text-[9px] uppercase font-semibold opacity-50 mt-1" style="color: var(--cor-texto-principal);">
                                    {{ $carta->name ?? '---' }} 
                                    @if(!$carta->is_concept && isset($carta->collector_number)) &bull; #{{ $carta->collector_number }} @endif
                                </p>
                            </div>
                            <div class="flex items-center justify-between pt-2 border-t border-gray-200/50 dark:border-slate-700/50">
                                <span class="text-[10px] font-bold opacity-40" style="color: var(--cor-texto-principal);">0 un.</span>
                                <span class="text-xs font-bold opacity-40" style="color: var(--cor-texto-principal);">R$ --</span>
                            </div>
                        </div>
                    </a>

                {{-- ESTADO 2: Com Estoque --}}
                @elseif($carta->total_estoque > 0)
                    <a href="{{ route('store.catalog.product', ['slug' => $loja->url_slug,'gameSlug' => $gameSlug, 'conceptSlug' => $carta->slug_seguro ]) }}" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" 
                         class="flex flex-col bg-white dark:bg-slate-800 rounded-xl shadow-sm border transition-all duration-300 relative cursor-pointer"
                         :class="hover ? 'z-[150] shadow-2xl' : 'z-10 border-gray-200 dark:border-slate-700'"
                         :style="hover ? 'border-color: var(--cor-cta);' : 'border-color: var(--cor-3, #e5e7eb);'">
                        <div class="relative p-3 pb-0 overflow-visible">
                            
                            {{-- TORRE DE BADGES EMPILHADAS (Fitas Pousando) --}}
                            <div class="absolute top-2 left-0 z-[60] flex flex-col gap-1 transition-opacity duration-300" :class="hover ? 'opacity-0' : 'opacity-100'">
                                @if(($carta->desconto ?? 0) > 0)
                                    <span class="bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded-r shadow-md uppercase tracking-tighter">-{{ number_format($carta->desconto, 0) }}%</span>
                                @endif
                                @if($isFoil)
                                    <span class="bg-gradient-to-r from-amber-200 to-yellow-400 text-yellow-900 text-[9px] font-black px-2 py-0.5 rounded-r shadow-sm">FOIL</span>
                                @endif
                                @if($isEtched)
                                    <span class="bg-gradient-to-r from-amber-500 to-orange-700 text-white text-[9px] font-black px-2 py-0.5 rounded-r shadow-sm">FOIL ETCHED</span>
                                @endif
                            </div>

                            {{-- WRAPPER DA IMAGEM PARA O EFEITO FOIL (Isola o zoom e o brilho) --}}
                            <div class="w-full h-auto aspect-[2.5/3.5] rounded-lg shadow-[0_2px_8px_rgba(0,0,0,0.15)] transition-all duration-500 transform relative z-50 bg-gray-100 dark:bg-gray-800 {{ $isFoil || $isEtched ? 'efeito-foil-suave border border-yellow-400/50' : '' }}" 
                                 :class="hover ? 'scale-[2] shadow-2xl ring-2 ring-black/10' : ''">
                                <img src="{{ $carta->imagem_final }}" alt="{{ $carta->nome_localizado }}" class="w-full h-full object-cover rounded-lg" onerror="this.onerror=null; this.src='https://placehold.co/250x350/eeeeee/999999?text=Erro+na+Foto';">
                            </div>

                        </div>
                        <div class="p-3 flex flex-col flex-grow justify-between gap-3 transition-opacity duration-300 relative z-[40] bg-white dark:bg-slate-800 rounded-b-xl" :class="hover ? 'opacity-0' : 'opacity-100'">
                            <div>
                                <h3 class="text-sm font-bold leading-tight line-clamp-2" style="color: var(--cor-texto-principal);" title="{{ $carta->nome_localizado }}">{{ $carta->nome_localizado }}</h3>
                                <p class="text-[9px] uppercase font-semibold mt-1 opacity-70" style="color: var(--cor-texto-principal);">
                                    {{ $carta->name ?? '---' }} 
                                    @if(!$carta->is_concept && isset($carta->collector_number)) &bull; #{{ $carta->collector_number }} @endif
                                </p>
                            </div>
                            <div class="flex justify-between items-center pt-2 border-t border-gray-200/50 dark:border-slate-700/50">
                                <span class="text-[10px] font-bold opacity-60" style="color: var(--cor-texto-principal);">{{ $carta->total_estoque }} un.</span>
                                <div class="flex flex-col items-end">
                                    <span class="text-[9px] uppercase font-bold opacity-50" style="color: var(--cor-texto-principal);">A partir de</span>
                                    @if(($carta->desconto ?? 0) > 0)
                                        <span class="text-[10px] text-gray-400 line-through font-bold">R$ {{ number_format($carta->menor_preco, 2, ',', '.') }}</span>
                                        <span class="text-base font-black tracking-tight leading-none" style="color: var(--cor-1);">R$ {{ number_format($carta->preco_final, 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-base font-black tracking-tight leading-none" style="color: var(--cor-1);">R$ {{ number_format($carta->menor_preco, 2, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>

                {{-- ESTADO 3: Esgotado com preço --}}
                @elseif($carta->total_estoque === 0 && $carta->ultimo_preco > 0)
                    <a href="{{ route('store.catalog.product', ['slug' => $loja->url_slug,'gameSlug' => $gameSlug, 'conceptSlug' => $carta->slug_seguro ]) }}" x-data="{ hover: false }" @mouseenter="hover = true" @mouseleave="hover = false" 
                         class="flex flex-col bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 transition-all duration-300 relative opacity-70 grayscale-[30%] hover:grayscale-0"
                         :class="hover ? 'z-[100] shadow-xl border-gray-400 dark:border-gray-500' : 'z-10'">
                        <div class="absolute inset-x-0 top-1/3 flex justify-center z-[60] transition-opacity duration-300" :class="hover ? 'opacity-0' : 'opacity-100'">
                            <span class="bg-red-600 text-white text-[10px] font-black uppercase px-3 py-1 rounded-full shadow-lg border border-red-800">Esgotado</span>
                        </div>
                        
                        <div class="relative p-3 pb-0 overflow-visible opacity-80 transition-opacity duration-300" :class="hover ? 'opacity-100' : ''">
                            
                            {{-- TORRE DE BADGES EMPILHADAS (Fitas Pousando) --}}
                            <div class="absolute top-2 left-0 z-[60] flex flex-col gap-1 transition-opacity duration-300" :class="hover ? 'opacity-0' : 'opacity-100'">
                                @if(($carta->desconto ?? 0) > 0)
                                    <span class="bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded-r shadow-md uppercase tracking-tighter">-{{ number_format($carta->desconto, 0) }}%</span>
                                @endif
                                @if($isFoil)
                                    <span class="bg-gradient-to-r from-amber-200 to-yellow-400 text-yellow-900 text-[9px] font-black px-2 py-0.5 rounded-r shadow-sm">FOIL</span>
                                @endif
                                @if($isEtched)
                                    <span class="bg-gradient-to-r from-amber-500 to-orange-700 text-white text-[9px] font-black px-2 py-0.5 rounded-r shadow-sm">FOIL ETCHED</span>
                                @endif
                            </div>

                            {{-- WRAPPER DA IMAGEM PARA O EFEITO FOIL --}}
                            <div class="w-full h-auto aspect-[2.5/3.5] rounded-lg shadow-sm transition-all duration-500 transform relative z-50 bg-gray-100 dark:bg-gray-800 {{ $isFoil || $isEtched ? 'efeito-foil-suave border border-yellow-400/50' : '' }}" 
                                 :class="hover ? 'scale-[1.7] shadow-xl ring-1 ring-black/10' : ''">
                                <img src="{{ $carta->imagem_final }}" alt="{{ $carta->nome_localizado }}" class="w-full h-full object-cover rounded-lg" onerror="this.onerror=null; this.src='https://placehold.co/250x350/eeeeee/999999?text=Erro+na+Foto';">
                            </div>

                        </div>
                        <div class="p-3 flex flex-col flex-grow justify-between gap-3 transition-opacity duration-300 bg-white dark:bg-slate-800 relative z-[40] rounded-b-xl" :class="hover ? 'opacity-0' : 'opacity-100'">
                            <div>
                                <h3 class="text-sm font-bold leading-tight line-clamp-2" style="color: var(--cor-texto-principal);" title="{{ $carta->nome_localizado }}">{{ $carta->nome_localizado }}</h3>
                                <p class="text-[9px] uppercase font-semibold mt-1 opacity-70" style="color: var(--cor-texto-principal);">
                                    {{ $carta->name ?? '---' }} 
                                    @if(!$carta->is_concept && isset($carta->collector_number)) &bull; #{{ $carta->collector_number }} @endif
                                </p>
                            </div>
                            <div class="flex items-end justify-between pt-2 border-t border-gray-100 dark:border-slate-700">
                                <div class="flex flex-col">
                                    <span class="text-xs font-black opacity-50" style="color: var(--cor-texto-principal);">0 un.</span>
                                </div>
                                <div class="flex flex-col items-end">
                                    <span class="text-[9px] uppercase font-bold opacity-50" style="color: var(--cor-texto-principal);">Último preço</span>
                                    <span class="text-base font-black tracking-tight leading-none" style="color: var(--cor-1);">R$ {{ number_format($carta->ultimo_preco, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
            @empty
                @for ($i = 0; $i < 5; $i++)
                    <div class="flex flex-col bg-black/5 dark:bg-white/5 border border-dashed border-gray-300 dark:border-slate-700 rounded-xl items-center justify-center p-6 opacity-50 aspect-[2.5/4]">
                        <i class="ph ph-image text-3xl mb-2 opacity-30" style="color: var(--cor-texto-principal);"></i>
                        <span class="text-[10px] font-bold uppercase tracking-widest" style="color: var(--cor-texto-principal);">Sem Carta</span>
                    </div>
                @endfor
            @endforelse
        </div>

    </div>
</div>