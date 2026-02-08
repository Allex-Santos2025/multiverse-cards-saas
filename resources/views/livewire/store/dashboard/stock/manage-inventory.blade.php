{{-- 1. Abertura do Layout Original --}}
<div class="min-h-screen flex flex-col font-sans bg-gray-100 dark:bg-[#0b1222] text-gray-900 dark:text-[#d4d4d8] transition-colors duration-300 relative" 
     x-data="{ 
        sidebarCollapsed: false,
        toggleSidebar() { this.sidebarCollapsed = !this.sidebarCollapsed },
        expanded: false, 
        activeTab: @entangle('searchType'),
        showModal: false,
        activeModalTab: 'comment',
        
        openModal(id, tab, existingComment = '') {
            this.showModal = true;
            this.activeModalTab = tab;
            $wire.set('editingPrintId', id, true);
            $wire.set('modalComment', existingComment, true);
        }
     }">

    <style>
        body { font-family: 'Inter', sans-serif;} 
        .no-scrollbar::-webkit-scrollbar { display: none; }
        
        .compact-tab { padding: 8px 24px; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; border-top-left-radius: 6px; border-top-right-radius: 6px; border: 1px solid transparent; position: relative; bottom: -1px; transition: all 0.2s; }
        .compact-tab.active { background: #f3f4f6; color: #ea580c; border-color: #e5e7eb; border-bottom-color: #f3f4f6; }
        .dark .compact-tab.active { background: #0f172a; color: #ea580c; border-color: rgba(255,255,255,0.1); border-bottom-color: #0f172a; }
        .compact-tab.inactive { background: #e5e7eb; color: #6b7280; border-bottom: 1px solid #d1d5db; }
        .dark .compact-tab.inactive { background: rgba(15, 23, 42, 0.4); color: #71717a; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .compact-tab.inactive:hover { background: #d1d5db; color: #374151; }
        .dark .compact-tab.inactive:hover { background: rgba(15, 23, 42, 0.8); color: #d4d4d8; }

        #sidebar { transition: width 0.3s ease-in-out; width: 240px; will-change: width; }
        #sidebar.collapsed { width: 68px; } 
        #sidebar.collapsed .nav-label, #sidebar.collapsed .group-title, #sidebar.collapsed .sidebar-footer-text { display: none; opacity: 0; }
        #sidebar.collapsed .nav-link { justify-content: center; padding-left: 0; padding-right: 0; }
        #sidebar.collapsed .nav-icon { margin-right: 0; font-size: 20px; }
    
        .card-image-preview { position: fixed; z-index: 1000; pointer-events: none; max-width: 320px; height: auto; border: 2px solid #ff9900; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3); display: none; transition: opacity 0.2s ease-in-out; }
        .quantity-input { -moz-appearance: textfield; }
        .quantity-input::-webkit-outer-spin-button, .quantity-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
        [x-cloak] { display: none !important; }
    </style>

    {{-- BARRA DE ABAS SUPERIOR --}}
    <div class="pt-3 px-6 bg-gray-200 dark:bg-[#0b1222] shrink-0 flex items-end gap-1 transition-colors duration-300 overflow-x-auto no-scrollbar flex-nowrap">
        @php
            $activeName = ($gameSlug == 'magic') ? 'Magic: The Gathering' : 'Pokémon TCG';
            $inactiveSlug = ($gameSlug == 'magic') ? 'pokemon' : 'magic';
            $inactiveName = ($gameSlug == 'magic') ? 'Pokémon TCG' : 'Magic: The Gathering';
        @endphp
        
        <a href="{{ route('store.dashboard.stock.index', ['slug' => $slug, 'game_slug' => $gameSlug]) }}" wire:navigate class="compact-tab active flex-shrink-0 min-w-[150px] font-black uppercase text-[11px] tracking-wider shadow-[0_-4px_10px_rgba(0,0,0,0.1)]">{{ $activeName }}</a>
        <a href="{{ route('store.dashboard.stock.index', ['slug' => $slug, 'game_slug' => $inactiveSlug]) }}" wire:navigate class="compact-tab inactive flex-shrink-0 opacity-70 hover:opacity-100 transition-opacity">{{ $inactiveName }}</a>
        @for ($i = 0; $i < 8; $i++) <button type="button" class="compact-tab inactive flex-shrink-0 w-16 flex items-center justify-center cursor-not-allowed opacity-30" disabled><i class="ph ph-lock-simple text-sm"></i></button> @endfor
    </div>

    {{-- AREA PRINCIPAL --}}
    <div class="flex-1 flex overflow-hidden bg-gray-100 dark:bg-[#0f172a] border-t border-gray-200 dark:border-white/10 relative transition-colors duration-300">
        
        {{-- SIDEBAR --}}
        <aside id="sidebar" class="bg-white dark:bg-black/20 border-r border-gray-200 dark:border-white/5 flex flex-col shrink-0 group transition-colors duration-300" :class="{ 'collapsed': sidebarCollapsed }">
            <div class="p-3 flex justify-end border-b border-gray-200 dark:border-white/5">
                <button @click="toggleSidebar()" class="text-gray-500 hover:text-gray-900 dark:text-zinc-500 dark:hover:text-white p-1 rounded transition-colors"><i class="ph text-xl" :class="sidebarCollapsed ? 'ph-arrows-out-line-horizontal' : 'ph-arrows-in-line-horizontal'"></i></button>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 space-y-6">
                <div>
                    <span class="group-title px-4 text-[8px] font-black text-gray-500 dark:text-zinc-600 uppercase tracking-widest block mb-2">Entrada</span>
                    <ul class="space-y-1 px-2">
                        <li><a href="#" class="nav-link flex items-center px-3 py-2 rounded bg-orange-100 dark:bg-orange-600/10 text-orange-600 dark:text-orange-500 font-black text-[10px] uppercase"><i class="ph ph-list-plus nav-icon text-lg mr-3"></i> <span class="nav-label">Grade Magic</span></a></li>
                        <li><a href="#" class="nav-link flex items-center px-3 py-2 rounded text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-black dark:hover:text-white transition-all font-bold text-[10px] uppercase"><i class="ph ph-file-txt nav-icon text-lg mr-3"></i> <span class="nav-label">Importar Bulk</span></a></li>
                    </ul>
                </div>
                <div>
                    <span class="group-title px-4 text-[8px] font-black text-gray-500 dark:text-zinc-600 uppercase tracking-widest block mb-2">Gestão</span>
                    <ul class="space-y-1 px-2">
                        <li><a href="#" class="nav-link flex items-center px-3 py-2 rounded text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-black dark:hover:text-white transition-all font-bold text-[10px] uppercase"><i class="ph ph-chart-line-up nav-icon text-lg mr-3"></i> <span class="nav-label">Curva ABC</span></a></li>
                        <li><a href="#" class="nav-link flex items-center px-3 py-2 rounded text-gray-600 dark:text-zinc-400 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-black dark:hover:text-white transition-all font-bold text-[10px] uppercase"><i class="ph ph-tags nav-icon text-lg mr-3"></i> <span class="nav-label">Precificação</span></a></li>
                    </ul>
                </div>
            </nav>
            <div class="p-4 border-t border-gray-200 dark:border-white/5 bg-gray-50 dark:bg-black/20">
                <button class="w-full bg-white dark:bg-zinc-800 border border-gray-200 dark:border-none hover:bg-gray-50 dark:hover:bg-zinc-700 text-gray-600 dark:text-zinc-300 py-2 rounded font-black text-[9px] uppercase flex items-center justify-center transition-all shadow-sm dark:shadow-none"><i class="ph ph-gear text-lg"></i> <span class="sidebar-footer-text ml-2">Config. Magic</span></button>
            </div>
        </aside>
    
        {{-- MAIN CONTENT --}}
        <main class="flex-grow p-6 md:p-10 w-full max-w-8xl overflow-x-auto no-scrollbar overflow-y-auto" x-data="cardPreview()">
        
            {{-- ÁREA DE BUSCA --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 mb-8 transition-colors duration-300">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        Busca
                    </h2>
                </div>

                {{-- TIPO DE BUSCA --}}
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-2 pl-1">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Origem da Busca</label>
                        <button class="text-red-400 hover:text-red-500 cursor-help" title="Filtre por relevância de vendas"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></button>
                    </div>
                    <div class="bg-gray-100 dark:bg-gray-700 p-1 rounded-lg inline-flex w-full md:w-auto">
                        @foreach(['padrao' => 'Padrão', 'marketplace' => 'Marketplace', 'minhaLoja' => 'Minha Loja'] as $key => $label)
                            <button type="button" @click="activeTab = '{{ $key }}'" class="flex-1 md:flex-none px-4 py-2 rounded-md text-sm font-medium transition-all duration-200" :class="activeTab === '{{ $key }}' ? 'bg-white dark:bg-gray-600 text-orange-600 dark:text-orange-400 shadow-sm ring-1 ring-gray-200 dark:ring-gray-500' : 'text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-200'">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- BARRA DE INPUT + BOTÃO BUSCAR (LARANJA) --}}
                <div class="flex items-center gap-3 relative z-10">
                    <div class="relative flex-grow">
                        <input type="text" wire:model="search" wire:keydown.enter="applyFilters" placeholder="Digite o nome da carta..." class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-gray-900 dark:text-white placeholder-gray-400 transition-all shadow-sm">
                    </div>
                    <button wire:click="applyFilters" class="hidden md:flex shrink-0 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium px-6 py-3 rounded-lg transition-colors items-center gap-2 shadow-sm">
                        <span wire:loading.remove wire:target="applyFilters">Buscar</span>
                        <span wire:loading wire:target="applyFilters" class="flex items-center gap-2"><svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></span>
                    </button>
                </div>

                {{-- TOGGLE FILTROS --}}
                <div class="mt-4 flex items-center justify-between">
                    <button type="button" @click="expanded = !expanded" class="text-sm font-medium text-orange-600 dark:text-orange-400 hover:text-orange-700 flex items-center gap-2 focus:outline-none select-none">
                        <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                        <span x-text="expanded ? 'Busca simples' : 'Busca avançada'"></span>
                    </button>
                    <button wire:click="applyFilters" class="md:hidden text-orange-600 font-bold text-sm">Buscar Agora</button>
                </div>

                {{-- FILTROS AVANÇADOS --}}
                <div x-show="expanded" x-collapse x-cloak class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex flex-wrap justify-center gap-4">
                        <div class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(30%-1rem)] min-w-[200px] max-w-sm flex-1">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase">Cor</label>
                            <select wire:model="filterColor" class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500 dark:text-white">
                                <option value="">Todas as Cores</option><option value="W">⚪ Branco</option><option value="U">🔵 Azul</option><option value="B">⚫ Preto</option><option value="R">🔴 Vermelho</option><option value="G">🟢 Verde</option><option value="C">Incolor</option><option value="M">Multicolor</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(30%-1rem)] min-w-[200px] max-w-sm flex-1">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase">Edição</label>
                            <input type="text" wire:model="filterSet" placeholder="Nome ou Código" class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500 dark:text-white">
                        </div>
                        <div class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(30%-1rem)] min-w-[200px] max-w-sm flex-1">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase">Qualidade</label>
                            <select wire:model="filterCondition" class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500 dark:text-white">
                                <option value="">Todas</option>
                                <option value="M">Mint (M)</option>
                                <option value="NM">Near Mint (NM)</option>
                                <option value="LP">Slight played (SP)</option>
                                <option value="MP">Moderately Played (MP)</option>
                                <option value="HP">Heavily Played (HP)</option>
                                <option value="DMG">Damaged (D)</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(30%-1rem)] min-w-[200px] max-w-sm flex-1">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase">Raridade</label>
                            <select wire:model="filterRarity" class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500 dark:text-white">
                                <option value="">Todas</option><option value="common">Comum</option><option value="uncommon">Incomum</option><option value="rare">Rara</option><option value="mythic">Mítica</option>
                            </select>
                        </div>
                        <div class="w-full sm:w-[calc(50%-1rem)] lg:w-[calc(30%-1rem)] min-w-[200px] max-w-sm flex-1">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase">Idioma</label>
                            <select wire:model="filterLanguage" class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500 dark:text-white">
                                <option value="">Todos</option>
                                <optgroup label="Mais Comuns"><option value="en">🇺🇸 Inglês</option><option value="pt">🇧🇷 Português</option><option value="jp">🇯🇵 Japonês</option></optgroup>
                                <optgroup label="Outros"><option value="zhs">🇨🇳 Chinês S.</option><option value="zht">🇹🇼 Chinês T.</option><option value="ko">🇰🇷 Coreano</option><option value="it">🇮🇹 Italiano</option><option value="fr">🇫🇷 Francês</option><option value="de">🇩🇪 Alemão</option><option value="es">🇪🇸 Espanhol</option><option value="ru">🇷🇺 Russo</option></optgroup>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABELA DE RESULTADOS --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm dark:shadow-lg border border-gray-200 dark:border-none transition-colors duration-300">
                <div class="flex items-center justify-between mb-4 flex-wrap">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Cadastrar Cards de Magic</h2>
                    <div class="flex items-center space-x-4 mt-4 md:mt-0">
                        
                        {{-- BOTÃO SALVAR (AGORA AZUL: bg-blue-600) --}}
                        <button onclick="submitInventoryForm()" 
                                wire:loading.attr="disabled"
                                class="relative inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition-all">
                            
                            <span wire:loading.remove wire:target="saveForm">Salvar</span>
                            
                            <span wire:loading wire:target="saveForm">
                                <i class="ph ph-circle-notch animate-spin"></i> Salvando...
                            </span>
                        </button>

                        <div class="flex items-center">
                            <label for="sort" class="mr-2 text-sm text-gray-600 dark:text-gray-400">Ordenar:</label>
                            <select wire:model.live="sortOption" id="sort" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md px-3 py-1.5 focus:outline-none focus:border-orange-500 text-sm text-gray-900 dark:text-white transition-colors">
                                <option value="name_asc">Nome [A-Z]</option>
                                <option value="name_desc">Nome [Z-A]</option>
                                <option value="name_en_asc">Nome em Inglês [A-Z]
                                </option>
                                <option value="name_en_desc">Nome em Inglês [Z-A]</option>
                                <option value="price_asc">Preço [0-9]</option>
                                <option value="price_desc">Preço [9-0]</option>
                                <option value="quantity_asc">Estoque [0-9]</option>
                                <option value="quantity_desc">Estoque [9-0]</option>
                                <option value="number_asc">Numeração [0-9]</option>
                                <option value="number_desc">Numeração [9-0]</option>
                            </select>
                        </div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                            Mostrando {{ $items->firstItem() }}-{{ $items->lastItem() }} de {{ number_format($items->total(), 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                {{-- LOADING DA TABELA --}}
                <div wire:loading.flex wire:target="previousPage, nextPage, gotoPage, search, filterSet, saveForm" class="fixed inset-0 z-[9999] items-center justify-center bg-black/20 dark:bg-black/50 backdrop-blur-sm">
                    <div class="bg-white dark:bg-[#1a2233] p-10 rounded-3xl shadow-xl flex flex-col items-center">
                        <i class="ph-bold ph-circle-notch animate-spin text-5xl text-orange-600"></i>
                        <span class="mt-4 text-sm font-bold uppercase text-orange-600">Processando...</span>
                    </div>
                </div>

                {{-- FORMULÁRIO ENVOLVENDO A TABELA --}}
                <form id="inventoryForm" onsubmit="event.preventDefault();">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estoque</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Preço</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Idioma</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Qualidade</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Extras</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Card</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($items as $print)
                                    @php
                                        $rarity = strtolower($print->rarity ?? 'common');
                                        $colors = match($rarity) {
                                            'common' => ['text-gray-400', 'bg-gray-500'],
                                            'uncommon' => ['text-slate-400', 'bg-slate-500'],
                                            'rare' => ['text-yellow-500', 'bg-yellow-600'],
                                            'mythic' => ['text-orange-500', 'bg-orange-600'],
                                            default => ['text-gray-300', 'bg-gray-400'],
                                        };
                                        [$textColor, $iconBgColor] = $colors;
                                        
                                        $stock = $print->stockItems->first();
                                        $qty = $stock ? $stock->quantity : 0;
                                        $dbPrice = $stock ? $stock->price : '';
                                        $condition = $stock ? $stock->condition : 'NM';
                                        $currentExtras = $stock ? ($stock->extras ?? []) : [];
                                    @endphp
                                    
                                    <tr wire:key="row-{{ $print->id }}">
                                        
                                        {{-- COLUNA ESTOQUE --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div x-data="{ qty: {{ $qty }} }" class="flex items-center space-x-2">
                                                <button type="button" @click="qty > 0 ? qty-- : 0" class="text-gray-400 hover:text-orange-500 focus:outline-none">
                                                    <i class="ph ph-minus-circle text-xl"></i>
                                                </button>

                                                <input type="number" 
                                                       name="items[{{ $print->id }}][qty]" 
                                                       x-model.number="qty" 
                                                       class="w-16 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm text-center text-gray-900 dark:text-white focus:outline-none focus:border-orange-500 transition-colors">
                                                
                                                <button type="button" @click="qty++" class="text-gray-400 hover:text-orange-500 focus:outline-none">
                                                    <i class="ph ph-plus-circle text-xl"></i>
                                                </button>
                                            </div>
                                        </td>
                                        
                                        {{-- COLUNA PREÇO --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="relative rounded-md shadow-sm">
                                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2 text-gray-500 sm:text-sm">R$</span>
                                                <input type="number" 
                                                       name="items[{{ $print->id }}][price]"
                                                       value="{{ $dbPrice }}"
                                                       step="0.01" 
                                                       class="block w-24 rounded-md border-0 py-1.5 pl-8 pr-2 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:text-sm dark:bg-gray-700 dark:text-white dark:ring-gray-600" placeholder="0.00">
                                            </div>
                                        </td>

                                        {{-- COLUNA IDIOMA --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{-- AQUI ESTÁ O TRUQUE: Input hidden envia o idioma original da carta (pt, en, jp) --}}
                                            <input type="hidden" name="items[{{ $print->id }}][language]" value="{{ $print->language_code ?? 'en' }}">

                                            <div class="relative">
                                                <select disabled class="appearance-none w-full bg-gray-100 dark:bg-[#1e293b] border border-gray-300 dark:border-[#334155] rounded px-3 py-2 text-sm text-gray-500 dark:text-gray-400 font-bold uppercase cursor-not-allowed opacity-80">
                                                    <option selected>{{ strtoupper($print->language_code ?? 'EN') }}</option>
                                                </select>
                                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400"><i class="ph ph-lock-key text-xs"></i></div>
                                            </div>
                                        </td>

                                        {{-- COLUNA QUALIDADE --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <select name="items[{{ $print->id }}][condition]" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-2 py-2 text-sm focus:outline-none focus:border-orange-500 text-gray-900 dark:text-white transition-colors">
                                                <option value="M" {{ $condition == 'M' ? 'selected' : '' }}>M</option>
                                                <option value="NM" {{ $condition == 'NM' ? 'selected' : '' }}>NM</option>
                                                <option value="SP" {{ $condition == 'SP' ? 'selected' : '' }}>SP</option>
                                                <option value="MP" {{ $condition == 'MP' ? 'selected' : '' }}>MP</option>
                                                <option value="HP" {{ $condition == 'HP' ? 'selected' : '' }}>HP</option>
                                                <option value="D" {{ $condition == 'D' ? 'selected' : '' }}>D</option>
                                            </select>
                                        </td>

                                        {{-- COLUNA EXTRAS --}}
{{-- COLUNA EXTRAS (LARGURA CONFORTÁVEL + SCROLL HIDDEN) --}}
<td class="px-6 py-4 whitespace-nowrap" 
    x-data="{ 
        open: false,
        selections: {{ json_encode($currentExtras) }},
        style: { left: '0px', top: 'auto', bottom: 'auto', width: 'auto', maxHeight: '250px' },
        
        toggle() {
            if (this.open) return this.close();
            this.position(); 
            this.open = true;
        },
        close() {
            this.open = false;
        },
        position() {
            if (!$refs.button) return;

            const rect = $refs.button.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            
            // --- AQUI ESTÁ A MUDANÇA: LARGURA MÍNIMA DE 220px ---
            // Math.max garante que nunca fique menor que 220px, mas pode crescer se precisar
            this.style.width = Math.max(rect.width, 220) + 'px';
            
            this.style.left = rect.left + 'px';

            if (spaceBelow >= 200 || spaceBelow > spaceAbove) {
                this.style.top = rect.bottom + 'px';
                this.style.bottom = 'auto';
                this.style.maxHeight = (spaceBelow - 20) + 'px';
            } else {
                this.style.top = 'auto';
                this.style.bottom = (window.innerHeight - rect.top) + 'px';
                this.style.maxHeight = (spaceAbove - 20) + 'px';
            }
        }
    }"
    @scroll.window="close()" 
    @resize.window="close()"
>
    @php
        try {
            $options = \App\Enums\StockExtra::options();
        } catch (\Throwable $e) {
            $options = ['foil' => 'Foil', 'etched' => 'Etched', 'promo' => 'Promo', 'textless' => 'Textless'];
        }
    @endphp

    {{-- BOTÃO GATILHO --}}
    <button 
        x-ref="button"
        @click="toggle()"
        type="button"
        class="w-full flex items-center justify-between bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded px-2 py-2 text-sm text-gray-900 dark:text-white transition-colors hover:border-blue-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
    >
        <span class="truncate select-none">
            <span x-show="selections.length > 0" class="text-blue-500 font-bold">
                <span x-text="selections.length"></span> item(s)
            </span>
            <span x-show="selections.length === 0" class="opacity-80">Extras</span>
        </span>
        <svg class="w-3 h-3 text-gray-500 dark:text-gray-300 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
    </button>

    {{-- MENU FLUTUANTE --}}
    <template x-teleport="body">
        <div 
            x-show="open"
            @click.outside="close()"
            class="fixed z-[9999] bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded shadow-xl flex flex-col"
            :style="`left: ${style.left}; top: ${style.top}; bottom: ${style.bottom}; width: ${style.width}; max-height: ${style.maxHeight};`"
            style="display: none;" 
        >
            {{-- Hack CSS Inline para esconder scrollbar --}}
            <style>
                .hide-scroll::-webkit-scrollbar { display: none; }
                .hide-scroll { -ms-overflow-style: none; scrollbar-width: none; }
            </style>
            
            <div class="overflow-y-auto p-1 hide-scroll">
                @foreach($options as $value => $label)
                    <label class="flex items-center gap-2 px-3 py-2.5 rounded cursor-pointer hover:bg-blue-600 hover:text-white group/item transition-colors">
                        <input type="checkbox" 
                               value="{{ $value }}" 
                               x-model="selections"
                               name="items[{{ $print->id }}][extras][]"
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-500 dark:bg-gray-600 shrink-0">
                        {{-- Aumentei um pouco o texto base também --}}
                        <span class="text-sm font-medium select-none text-gray-700 dark:text-gray-200 group-hover:text-white whitespace-nowrap">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </template>
</td>

                                        {{-- COLUNA CARD INFO --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3 {{ $textColor }}">
                                                <div class="relative w-6 h-6 flex items-center justify-center shrink-0">
                                                    <x-set-symbol :path="''" :code="$print->set->code ?? ''" :rarity="$rarity" size="w-6 h-6" />
                                                </div>
                                                <div class="flex items-baseline gap-2">
                                                    <span class="text-blue-400 font-base tracking-tight text-[15px] cursor-help hover:brightness-125 transition-all"
                                                          @mouseenter="showCard('{{ asset($print->image_path) }}', $event)"
                                                          @mouseleave="hideCard()"
                                                          @mousemove="moveCard($event)">
                                                        {{ $print->printed_name ?? $print->concept->name }}
                                                    </span>
                                                    <span class="text-[11px] text-black dark:text-white font-normal ml-1.5">({{ $print->set->code }} #{{ $print->collector_number }})</span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- AÇÕES (BOTÕES) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end gap-3">
                                                <button type="button" @click="openModal({{ $print->id }}, 'comment')" class="text-gray-400 hover:text-yellow-500 dark:hover:text-yellow-400 transition-colors" title="Observações">
                                                    <i class="ph ph-chat-text text-xl"></i>
                                                </button>
                                                <button type="button" @click="openModal({{ $print->id }}, 'photo')" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Upload Foto">
                                                    <i class="ph ph-camera-plus text-xl"></i>
                                                </button>
                                                <button class="transition-colors group relative flex items-center justify-center w-6 h-6" 
                                                        x-data="{ active: false }" @click="active = !active" title="Monitorar">
                                                    <i class="ph ph-heart text-xl text-gray-400 group-hover:text-red-500 dark:text-gray-400" x-show="!active"></i>
                                                    <i class="ph-fill ph-heart text-xl text-red-500" x-show="active" style="display: none;"></i>
                                                </button>
                                                <div class="relative ml-1">
                                                    <button class="text-gray-400 hover:text-gray-800 dark:hover:text-white transition-colors flex items-center">
                                                        <i class="ph ph-dots-three-vertical text-xl"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-10 text-center text-gray-500 dark:text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="ph ph-ghost text-4xl mb-2 text-gray-300 dark:text-gray-600"></i>
                                                <span>Nenhum card encontrado com estes filtros.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </form>

                {{-- Paginação --}}
                <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
                    {{ $items->links() }}
                </div>
            </div>

            {{-- Imagem de Preview --}}
            <img x-ref="previewImg" src="" class="card-image-preview" :class="{ 'opacity-100 block': show, 'opacity-0 hidden': !show }" style="display: none;">
        </main>
    </div>

    {{-- MODAL GLOBAL --}}
    <div x-show="showModal" x-cloak style="display: none;" class="fixed inset-0 z-[999] flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div x-show="showModal" x-transition.opacity @click="showModal = false" class="absolute inset-0 bg-gray-500/75 dark:bg-black/80 backdrop-blur-sm"></div>
        <div x-show="showModal" x-transition.scale.95 class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden">
            <div wire:loading.flex wire:target="loadModalData" class="absolute inset-0 z-50 bg-white/50 dark:bg-gray-900/50 backdrop-blur-[2px] flex items-center justify-center">
                <i class="ph-bold ph-circle-notch animate-spin text-3xl text-blue-600"></i>
            </div>
            <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="h-6 w-1 bg-blue-600 dark:bg-blue-500 rounded-full"></div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white tracking-wide" x-text="activeModalTab === 'comment' ? 'Editar Observação' : 'Gerenciar Foto'"></h3>
                </div>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-5">
                <div x-show="activeModalTab === 'comment'">
                    <div class="space-y-3">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Descrição do Dano</label>
                        <textarea wire:model.defer="modalComment" rows="6" class="w-full bg-gray-5 dark:bg-gray-800 text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none"></textarea>
                    </div>
                </div>
                <div x-show="activeModalTab === 'photo'">
                    <div class="space-y-4">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Foto do Produto</label>
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-full aspect-video bg-gray-100 dark:bg-gray-800 rounded-lg border border-dashed border-gray-300 dark:border-gray-700 flex items-center justify-center overflow-hidden relative">
                                @if ($modalPhoto)
                                    <img src="{{ $modalPhoto->temporaryUrl() }}" class="w-full h-full object-contain">
                                @elseif ($existingPhotoUrl)
                                    <img src="{{ asset('storage/' . $existingPhotoUrl) }}" class="w-full h-full object-contain">
                                @else
                                    <span class="text-sm text-gray-500">Sem foto</span>
                                @endif
                            </div>
                            <label class="flex items-center justify-center w-full px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <span class="text-sm text-gray-700 dark:text-gray-300">Selecionar Arquivo</span>
                                <input type="file" wire:model="modalPhoto" class="hidden">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-end gap-3 p-5 pt-0 bg-white dark:bg-gray-900">
                <button @click="showModal = false" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">Cancelar</button>
                <button wire:click="saveDetails" @click="showModal = false" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-all">Salvar</button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    // Função para capturar o formulário e enviar via Livewire
    function submitInventoryForm() {
        const form = document.getElementById('inventoryForm');
        const formData = new FormData(form);
        const data = {};

        // Converter FormData em Objeto Estruturado (items[1][qty])
        for (let [key, value] of formData.entries()) {
            const match = key.match(/items\[(\d+)\]\[(.*?)\](\[\])?/);
            if (match) {
                const id = match[1];
                const field = match[2];
                const isArray = match[3];

                if (!data.items) data.items = {};
                if (!data.items[id]) data.items[id] = {};

                if (isArray) {
                    if (!data.items[id][field]) data.items[id][field] = [];
                    data.items[id][field].push(value);
                } else {
                    data.items[id][field] = value;
                }
            }
        }

        // Chamar o método do componente PHP
        @this.saveForm(data);
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('cardPreview', () => ({
            show: false,
            showCard(url, event) {
                this.$refs.previewImg.src = url;
                this.show = true;
                this.$refs.previewImg.style.display = 'block';
                this.moveCard(event);
            },
            hideCard() {
                this.show = false;
                setTimeout(() => { if(!this.show) this.$refs.previewImg.style.display = 'none'; }, 200);
            },
            moveCard(event) {
                const img = this.$refs.previewImg;
                const offset = 20;
                let top = event.clientY + offset;
                let left = event.clientX + offset;
                if (left + 320 > window.innerWidth) left = event.clientX - 270;
                if (top + 350 > window.innerHeight) top = window.innerHeight - 360;
                img.style.top = `${top}px`;
                img.style.left = `${left}px`;
            }
        }))
    })
</script>
@endpush