<div class="space-y-6">

    {{-- CABEÇALHO SUPERIOR (VOLTAR E INFORMAÇÕES DO DECK) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4 min-w-0">
            <button wire:click="$parent.closeDeckBuilder" class="flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-900 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 transition shrink-0">
                <i class="ph-bold ph-arrow-left"></i>
                <span>Voltar aos Decks</span>
            </button>

            <div class="h-5 w-px bg-slate-200 hidden sm:block"></div>

            {{-- Miniatura da Box --}}
            @php
                $boxBg = match($deck->box_color ?? 'mat-case-red') {
                    'mat-case-black'  => 'from-slate-700 to-slate-950',
                    'mat-case-blue'   => 'from-blue-600 to-blue-900',
                    'mat-case-amber'  => 'from-amber-600 to-amber-900',
                    'mat-case-purple' => 'from-purple-600 to-purple-950',
                    default           => 'from-red-600 to-red-900',
                };
            @endphp
            <div class="w-10 h-13 rounded-lg bg-gradient-to-br {{ $boxBg }} shadow-sm flex flex-col justify-between p-1.5 shrink-0 border border-white/20">
                <span class="text-[6px] font-black text-white/70 uppercase">DECK</span>
                <span class="text-[7px] font-black text-white text-center font-mono">{{ $totalCards }}</span>
            </div>

            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="text-base font-black text-slate-900 truncate leading-none">{{ $deck->name }}</h2>
                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase bg-slate-100 text-slate-600 border border-slate-200">
                        {{ $deck->game->name ?? 'TCG' }}
                    </span>
                    <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-200">
                        {{ ucfirst($deck->format) }}
                    </span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">Construtor Híbrido: acervo físico sincronizado com wishlist</p>
            </div>
        </div>

        {{-- MÉTRICAS E AÇÕES --}}
        <div class="flex items-center gap-3 shrink-0 self-end md:self-center">
            <div class="flex items-center gap-2 text-xs font-semibold bg-slate-50 border border-slate-100 px-3 py-1.5 rounded-xl">
                <span class="text-emerald-600 flex items-center gap-1 font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $physicalCount }} Físicas
                </span>
                <span class="text-slate-300">/</span>
                <span class="text-indigo-600 flex items-center gap-1 font-bold">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> {{ $wishlistCount }} Wishlist
                </span>
                <span class="text-slate-300">|</span>
                <span class="font-black text-slate-800 font-mono">{{ $totalCards }} Total</span>
            </div>

            <a href="#" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black uppercase rounded-xl transition shadow-sm flex items-center gap-1.5">
                <i class="ph-bold ph-play"></i> Mesa Sandbox
            </a>
        </div>
    </div>

    {{-- GRID PRINCIPAL: GAVETA À ESQUERDA (5 COLUNAS) + ZONAS À DIREITA (7 COLUNAS) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        {{-- GAVETA DE PESQUISA E ADIÇÃO --}}
        <div class="lg:col-span-5 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
            
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 space-y-3">
                <div class="relative">
                    <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Buscar por nome da carta..." 
                        class="w-full bg-white border border-slate-200 rounded-xl pl-9 pr-3.5 py-2 text-xs font-medium text-slate-900 placeholder:text-slate-400 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
                    >
                </div>

                {{-- Alternador Acervo vs Catálogo --}}
                <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 rounded-xl text-xs font-bold">
                    <button 
                        type="button"
                        wire:click="setSourceTab('collection')"
                        class="py-1.5 rounded-lg transition-all flex items-center justify-center gap-1.5 {{ $sourceTab === 'collection' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Meu Acervo Real
                    </button>
                    <button 
                        type="button"
                        wire:click="setSourceTab('catalog')"
                        class="py-1.5 rounded-lg transition-all flex items-center justify-center gap-1.5 {{ $sourceTab === 'catalog' ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Catálogo (Wishlist)
                    </button>
                </div>

                {{-- Seletor da Zona de Destino --}}
                <div class="flex items-center justify-between text-xs pt-1">
                    <span class="font-bold text-slate-500">Adicionar em:</span>
                    <div class="flex items-center gap-1">
                        @foreach($zones as $zKey => $zMeta)
                            @php
                                $zoneLabel = is_array($zMeta) ? ($zMeta['label'] ?? $zMeta['name'] ?? $zKey) : $zMeta;
                            @endphp
                            <button 
                                type="button"
                                wire:click="setTargetBoard('{{ $zKey }}')"
                                class="px-2.5 py-1 rounded-lg font-bold text-[11px] transition {{ $targetBoard === $zKey ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                {{ $zoneLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Resultados --}}
            <div class="p-3 max-h-[580px] overflow-y-auto space-y-2 divide-y divide-slate-100">
                @if(strlen($search) < 2)
                    <div class="py-12 flex flex-col items-center justify-center text-center text-slate-400">
                        <i class="ph ph-cards text-4xl mb-2 text-slate-300"></i>
                        <p class="text-xs font-semibold">Digite ao menos 2 letras para pesquisar cartas.</p>
                        <span class="text-[11px] text-slate-400 mt-0.5">
                            {{ $sourceTab === 'collection' ? 'Buscando nos seus fichários e caixas' : 'Buscando em todo o catálogo do jogo' }}
                        </span>
                    </div>
                @elseif($searchResults->isEmpty())
                    <div class="py-10 text-center text-slate-400 text-xs">
                        Nenhuma carta encontrada para "{{ $search }}".
                    </div>
                @else
                    @foreach($searchResults as $item)
                        <div class="pt-2 flex items-center justify-between gap-3 group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-10 h-14 rounded bg-slate-900 overflow-hidden shrink-0 border border-slate-200 shadow-sm relative">
                                    <img 
                                        src="{{ $item['image'] }}" 
                                        alt="{{ $item['name'] }}" 
                                        class="w-full h-full object-cover"
                                        onerror="this.src='/images/placeholder-card.png'"
                                    >
                                </div>

                                <div class="min-w-0">
                                    <h5 class="text-xs font-black text-slate-800 truncate leading-snug group-hover:text-orange-600 transition">
                                        {{ $item['name'] }}
                                    </h5>
                                    <div class="flex items-center gap-1.5 text-[10px] text-slate-500 font-mono mt-0.5">
                                        <span class="uppercase font-bold text-slate-700">{{ $item['set_code'] }}</span>
                                        @if(!empty($item['collector_number']))
                                            <span>#{{ $item['collector_number'] }}</span>
                                        @endif
                                    </div>

                                    @if($item['type'] === 'collection')
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                {{ $item['available'] }} Disp. ({{ $item['condition'] }})
                                            </span>
                                        </div>
                                    @else
                                        <div class="mt-1">
                                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                Catálogo Oficial
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="shrink-0">
                                @if($item['type'] === 'collection')
                                    <button 
                                        type="button"
                                        wire:click="addFromCollection({{ $item['item_id'] }})"
                                        @disabled($item['available'] <= 0)
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1 {{ $item['available'] > 0 ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                                        <i class="ph-bold ph-plus"></i> Add
                                    </button>
                                @else
                                    <button 
                                        type="button"
                                        wire:click="addAsWishlist({{ $item['print_id'] }})"
                                        class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-sm flex items-center gap-1">
                                        <i class="ph-bold ph-plus"></i> Wish
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

        </div>

        {{-- ZONAS DE MONTAGEM DO DECK --}}
        <div class="lg:col-span-7 space-y-5">
            @foreach($zones as $zoneKey => $zoneMeta)
                @php
                    $zoneCards = $deck->cards->where('board_type', $zoneKey);
                    $zoneTotal = $zoneCards->sum('quantity');
                    $zoneLabel = is_array($zoneMeta) ? ($zoneMeta['label'] ?? $zoneMeta['name'] ?? $zoneKey) : $zoneMeta;
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    
                    {{-- Cabeçalho da Zona --}}
                    <div class="px-5 py-3.5 bg-slate-50/70 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full {{ $zoneKey === 'mainboard' ? 'bg-emerald-500' : 'bg-amber-500' }}"></div>
                            <h4 class="font-black text-xs uppercase tracking-wider text-slate-900">
                                {{ $zoneLabel }}
                            </h4>
                        </div>
                        <span class="text-xs font-mono font-bold text-slate-500">
                            {{ $zoneTotal }} cartas
                        </span>
                    </div>

                    {{-- Lista de Cartas da Zona --}}
                    <div class="p-4">
                        @if($zoneCards->isEmpty())
                            <div class="py-8 text-center text-slate-400 text-xs font-medium">
                                Nenhuma carta alocada nesta zona. Use a busca ao lado para adicionar.
                            </div>
                        @else
                            <div class="divide-y divide-slate-100">
                                @foreach($zoneCards as $card)
                                    @php
                                        $print = $card->catalogPrint;
                                        $cardName = $print ? ($print->printed_name ?: ($print->concept->name ?? 'Carta')) : 'Carta';
                                        $imgUrl = $print ? ($print->image_path ?: '/images/placeholder-card.png') : '/images/placeholder-card.png';
                                        $isCover = $deck->cover_catalog_print_id === $card->catalog_print_id;
                                    @endphp

                                    <div class="py-2.5 flex items-center justify-between gap-3 group">
                                        
                                        <div class="flex items-center gap-1.5 shrink-0 bg-slate-50 border border-slate-200 rounded-lg p-0.5">
                                            <button 
                                                type="button" 
                                                wire:click="decrementCard({{ $card->id }})" 
                                                class="w-6 h-6 rounded flex items-center justify-center text-slate-500 hover:bg-white hover:text-slate-900 transition">
                                                <i class="ph-bold ph-minus text-xs"></i>
                                            </button>
                                            <span class="w-6 text-center text-xs font-black font-mono text-slate-800">
                                                {{ $card->quantity }}
                                            </span>
                                            <button 
                                                type="button" 
                                                wire:click="incrementCard({{ $card->id }})" 
                                                class="w-6 h-6 rounded flex items-center justify-center text-slate-500 hover:bg-white hover:text-slate-900 transition">
                                                <i class="ph-bold ph-plus text-xs"></i>
                                            </button>
                                        </div>

                                        <div class="w-8 h-11 rounded bg-slate-900 overflow-hidden shrink-0 border border-slate-200 relative shadow-sm">
                                            <img 
                                                src="{{ $imgUrl }}" 
                                                alt="{{ $cardName }}" 
                                                class="w-full h-full object-cover"
                                                onerror="this.src='/images/placeholder-card.png'"
                                            >
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2">
                                                <h5 class="text-xs font-black text-slate-800 truncate" title="{{ $cardName }}">
                                                    {{ $cardName }}
                                                </h5>
                                                @if($card->is_virtual)
                                                    <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                        Wishlist
                                                    </span>
                                                @else
                                                    <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.2 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                        Física
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] text-slate-400 font-mono">
                                                {{ $print->set->code ?? '' }} #{{ $print->collector_number ?? '' }}
                                            </span>
                                        </div>

                                        <div class="flex items-center gap-1 shrink-0">
                                            <button 
                                                type="button"
                                                wire:click="setAsCover({{ $card->catalog_print_id }}, '{{ $imgUrl }}')"
                                                class="px-2 py-1 text-[10px] font-bold rounded transition flex items-center gap-1 {{ $isCover ? 'text-amber-600 bg-amber-50 border border-amber-200' : 'text-slate-400 hover:text-slate-700 hover:bg-slate-100' }}"
                                                title="Definir esta carta como Capa">
                                                <i class="ph-bold ph-star {{ $isCover ? 'ph-fill text-amber-500' : '' }}"></i>
                                                <span>Capa</span>
                                            </button>

                                            <button 
                                                type="button"
                                                wire:click="decrementCard({{ $card->id }})"
                                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded transition"
                                                title="Remover cópia">
                                                <i class="ph-bold ph-trash text-xs"></i>
                                            </button>
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            @endforeach
        </div>

    </div>

</div>
