<div class="w-full flex flex-col items-center">

    @php
        $isBlueBinder = ($binder->cover_material === 'cover-blue');
        $isBoxBinder  = ($binder->cover_material === 'cover-box');

        $coverTheme = match($binder->cover_material) {
            'cover-blue' => 'bg-gradient-to-br from-blue-700 via-blue-900 to-slate-950 border border-blue-500/50 shadow-[0_20px_50px_rgba(37,99,235,0.25)]',
            'cover-box'  => 'bg-gradient-to-br from-amber-800 via-amber-950 to-stone-950 border border-amber-600/50 shadow-[0_20px_50px_rgba(217,119,6,0.2)]',
            default      => 'bg-gradient-to-br from-slate-800 via-slate-900 to-black border border-slate-700 shadow-[0_20px_50px_rgba(0,0,0,0.6)]',
        };

        $strapTheme = match($binder->strap_color) {
            'cyan'  => 'bg-sky-500 shadow-[2px_0_6px_rgba(0,0,0,0.6),-2px_0_6px_rgba(0,0,0,0.6)]',
            'gold'  => 'bg-amber-500 shadow-[2px_0_6px_rgba(0,0,0,0.6),-2px_0_6px_rgba(0,0,0,0.6)]',
            default => 'bg-orange-500 shadow-[2px_0_6px_rgba(0,0,0,0.6),-2px_0_6px_rgba(0,0,0,0.6)]',
        };
        $coverArt = $binder->cover_image_path;
        if (!empty($coverArt) && !str_starts_with($coverArt, "http://") && !str_starts_with($coverArt, "https://")) {
            $coverArt = "/" . ltrim($coverArt, "/");
        }
    @endphp

    {{-- CENA 1: CAPA FECHADA EM TAMANHO AMPLIADO (RESPONSIVO PC/NOTEBOOK) --}}
    @if(!$isCoverOpen)
        <div class="w-full max-w-4xl mx-auto flex flex-col items-center justify-center py-4 sm:py-8 animate-fade-in">
            
            {{-- A PASTA FECHADA (CLICÁVEL) --}}
            <div wire:click="openCover" 
                 class="relative w-full max-w-[360px] sm:max-w-[440px] md:max-w-[480px] lg:max-w-[520px] aspect-[1/1.35] rounded-3xl cursor-pointer select-none group transition-all duration-300 hover:scale-[1.02] hover:-translate-y-1.5 shadow-2xl {{ $coverTheme }}">
                
                {{-- Container interno com overflow-hidden para a arte e a lombada respeitarem os cantos arredondados --}}
                <div class="absolute inset-0 rounded-3xl overflow-hidden pointer-events-none">
                    {{-- Imagem de Fundo cobrindo toda a capa --}}
                    @if(!empty($coverArt))
                        <img src="{{ $coverArt }}" alt="{{ $binder->name }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-black/40"></div>
                    @endif

                    {{-- Textura e Lombada Esquerda --}}
                    <div class="absolute inset-y-0 left-0 w-8 sm:w-10 bg-gradient-to-r from-black/85 via-white/[0.08] to-black/60 border-r border-black/60 rounded-l-3xl z-10"></div>
                </div>

                {{-- Costura perimetral pontilhada clássica --}}
                <div class="absolute inset-3 border-2 border-dashed border-white/30 rounded-2xl pointer-events-none z-10"></div>

                {{-- Cantoneiras metálicas douradas --}}
                <div class="absolute top-3 right-3 w-5 h-5 border-t-2 border-r-2 border-amber-400 rounded-tr-lg pointer-events-none z-10"></div>
                <div class="absolute bottom-3 right-3 w-5 h-5 border-b-2 border-r-2 border-amber-400 rounded-br-lg pointer-events-none z-10"></div>

                {{-- Elástico vertical de fechamento --}}
                <div class="absolute inset-y-0 right-8 sm:right-10 w-4 sm:w-5 {{ $strapTheme }} pointer-events-none z-20"></div>

                {{-- Conteúdo sobreposto na Capa --}}
                <div class="relative z-10 h-full flex flex-col items-center justify-between text-center pl-8 sm:pl-10 pr-12 sm:pr-14 py-5 sm:py-7 pointer-events-none">
                    
                    {{-- Topo da Capa --}}
                    <div class="w-full flex items-center justify-between">
                        <span class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-white/90 bg-black/60 backdrop-blur-md px-3 py-1 rounded-lg border border-white/10 shadow">
                            {{ $binder->pocket_layout }} &bull; {{ $binder->current_sheets_count }} Folhas
                        </span>
                        @if($binder->is_locked_sheets)
                            <span class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-amber-400 bg-amber-950/70 backdrop-blur-md px-2.5 py-1 rounded-lg border border-amber-500/30 flex items-center gap-1 shadow">
                                <i class="ph-bold ph-lock-key"></i> Travado
                            </span>
                        @endif
                    </div>

                    {{-- Centro quando não tiver capa --}}
                    @if(empty($coverArt))
                        <div class="my-auto flex flex-col items-center gap-2 text-white/50">
                            <i class="ph-bold ph-book-bookmark text-6xl text-orange-500"></i>
                            <span class="text-sm font-black uppercase tracking-widest text-white">Versus TCG</span>
                        </div>
                    @else
                        <div></div>
                    @endif

                    {{-- Título e Descrição da Pasta --}}
                    <div class="w-full bg-black/75 backdrop-blur-md p-3.5 sm:p-4 rounded-2xl border border-white/15 shadow-xl">
                        <h2 class="text-base sm:text-xl font-black text-white tracking-wide truncate">{{ $binder->name }}</h2>
                        @if(!empty($binder->description))
                            <p class="text-xs sm:text-sm text-gray-300 line-clamp-1 mt-0.5">{{ $binder->description }}</p>
                        @endif
                    </div>

                </div>

                {{-- Botão de Ação Destacado Flutuante (100% visível, sem corte) --}}
                <div class="absolute -bottom-5 inset-x-0 flex justify-center z-30">
                    <button type="button" 
                            class="px-6 py-2.5 rounded-full bg-orange-600 group-hover:bg-orange-500 text-white text-xs sm:text-sm font-black tracking-wider uppercase shadow-[0_8px_20px_rgba(234,88,12,0.5)] border border-orange-400/40 flex items-center gap-2 transition-all transform group-hover:scale-105">
                        <i class="ph-bold ph-book-open text-base"></i>
                        <span>Abrir Fichário</span>
                    </button>
                </div>

            </div>

            <p class="text-xs sm:text-sm text-gray-400 font-medium mt-10 flex items-center gap-1.5 animate-pulse">
                <i class="ph-bold ph-cursor-click text-orange-400"></i>
                Clique no álbum para folhear as cartas
            </p>

        </div>
    @else
    {{-- CENA 2: MIOLO DO FICHÁRIO ABERTO --}}
    <!-- Barra Superior -->
    <div class="w-full bg-[#161b22] border border-[#30363d] rounded-2xl p-4 mb-6 flex flex-wrap items-center justify-between gap-4 text-white">
        <div class="flex items-center gap-3">


            <button wire:click="closeCover" class="flex items-center gap-1.5 text-xs font-bold text-orange-400 hover:text-orange-300 px-3 py-1.5 rounded-lg bg-orange-950/40 hover:bg-orange-950/70 border border-orange-500/30 transition shadow-sm">
                <i class="ph-bold ph-book text-sm"></i>
                <span>Fechar Capa</span>
            </button>
            <button wire:click="$parent.closeBinder" class="flex items-center gap-2 text-xs font-semibold text-gray-300 hover:text-white px-3 py-1.5 rounded-lg bg-[#21262d] hover:bg-[#30363d] transition">
                <i class="ph-bold ph-arrow-left"></i>
                <span>Voltar às Pastas</span>
            </button>
            <div class="h-4 w-px bg-gray-700"></div>
            <div class="flex items-center gap-2.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <h1 class="text-base font-bold text-white tracking-wide">{{ $binder->name }}</h1>
                <span class="text-[10px] font-bold uppercase tracking-wider bg-sky-500/10 text-sky-400 border border-sky-500/20 px-2.5 py-0.5 rounded-md">
                    {{ $currentLayout }} ({{ $pocketsPerPage }}-Pocket)
                </span>
                @if($binder->is_locked_sheets)
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-0.5 rounded-md flex items-center gap-1">
                        <i class="ph-bold ph-lock-key"></i> Costurado Fixo
                    </span>
                @else
                    <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded-md flex items-center gap-1">
                        <i class="ph-bold ph-lock-key-open"></i> Expansível
                    </span>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center bg-[#21262d] p-1 rounded-xl border border-[#30363d]">
                <button wire:click="setViewMode('book')" class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $viewMode === 'book' ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:text-white' }} transition">Livro</button>
                <button wire:click="setViewMode('zoom')" class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $viewMode === 'zoom' ? 'bg-emerald-600 text-white' : 'text-gray-400 hover:text-white' }} transition">Foco</button>
            </div>
        </div>
    </div>

    @php
        $isBlueBinder = ($binder->cover_material === 'cover-blue');
        $isBoxBinder  = ($binder->cover_material === 'cover-box');

        // Moldura externa da capa
        $outerBinderTheme = match($binder->cover_material) {
            'cover-box'  => 'bg-gradient-to-br from-[#3b2210] via-[#24150b] to-[#120a05] border-[#6b3c1b]/60',
            'cover-blue' => 'bg-gradient-to-br from-[#0a1e3a] via-[#061426] to-[#020912] border-[#2563eb]/50 shadow-[0_20px_60px_rgba(37,99,235,0.15)]',
            default      => 'bg-gradient-to-br from-[#1c2128] via-[#14181f] to-[#0d1117] border-white/10',
        };

        // Contracapas frontal e traseira
        $insideCoverTheme = match($binder->cover_material) {
            'cover-box'  => 'from-[#422713] via-[#2c190c] to-[#1a0e07] border-[#78350f]/40',
            'cover-blue' => 'from-[#0e274a] via-[#091b33] to-[#050f1d] border-[#3b82f6]/40',
            default      => 'from-[#1e2633] via-[#141a24] to-[#0f141c] border-white/5',
        };

        // Cor do tema dos detalhes (se for azul safira ou elástico ciano, usa paleta ciano)
        $accentTheme = ($binder->strap_color === 'cyan' || $isBlueBinder) ? 'cyan' : ($binder->strap_color === 'gold' ? 'gold' : 'orange');

        $cornerColor = match($accentTheme) {
            'cyan'  => 'border-cyan-400',
            'gold'  => 'border-amber-400',
            default => 'border-orange-500',
        };

        // Hover do bolso vazio
        $pocketHover = match($accentTheme) {
            'cyan'  => 'hover:border-cyan-400/80 hover:bg-cyan-500/10 hover:shadow-[0_0_12px_rgba(6,182,212,0.25)]',
            'gold'  => 'hover:border-amber-400/80 hover:bg-amber-500/10 hover:shadow-[0_0_12px_rgba(245,158,11,0.25)]',
            default => 'hover:border-orange-500/80 hover:bg-orange-500/10 hover:shadow-[0_0_12px_rgba(249,115,22,0.25)]',
        };

        // Ícone e texto do bolso vazio
        $pocketAccentText = match($accentTheme) {
            'cyan'  => 'group-hover:text-cyan-400 group-hover:border-cyan-400',
            'gold'  => 'group-hover:text-amber-400 group-hover:border-amber-400',
            default => 'group-hover:text-orange-500 group-hover:border-orange-500',
        };

        // Borda ativa quando um bolso é clicado
        $pocketActiveRing = match($accentTheme) {
            'cyan'  => 'ring-2 ring-cyan-400 border-cyan-400 shadow-[0_0_16px_rgba(6,182,212,0.4)]',
            'gold'  => 'ring-2 ring-amber-400 border-amber-400 shadow-[0_0_16px_rgba(245,158,11,0.4)]',
            default => 'ring-2 ring-orange-500 border-orange-500 shadow-[0_0_16px_rgba(249,115,22,0.4)]',
        };

        $colsClass = match($currentLayout) {
            '2x2' => 'grid-cols-2',
            '4x4' => 'grid-cols-4',
            default => 'grid-cols-3',
        };

        $isLeftInsideCover = ($viewMode === 'book' && $currentPageIndex === 1);
        $leftPageNum = $isLeftInsideCover ? null : ($viewMode === 'book' ? $currentPageIndex : $currentPageIndex);
        $rightPageNum = ($viewMode === 'book' && $currentPageIndex === 1) ? 1 : ($currentPageIndex + 1);
        $isRightInsideCover = ($viewMode === 'book' && $rightPageNum > $totalPages);
    @endphp

    <!-- Álbum Físico Aberto -->
    <div class="w-full rounded-2xl p-4 sm:p-6 lg:p-8 relative border shadow-[0_25px_50px_-12px_rgba(0,0,0,0.8),0_0_0_1px_rgba(255,255,255,0.08)] {{ $outerBinderTheme }}">
        
        <!-- Cantoneiras Metálicas -->
        <div class="absolute top-2 left-2 w-6 h-6 border-t-2 border-l-2 rounded-tl-lg pointer-events-none {{ $cornerColor }}"></div>
        <div class="absolute top-2 right-2 w-6 h-6 border-t-2 border-r-2 rounded-tr-lg pointer-events-none {{ $cornerColor }}"></div>
        <div class="absolute bottom-2 left-2 w-6 h-6 border-b-2 border-l-2 rounded-bl-lg pointer-events-none {{ $cornerColor }}"></div>
        <div class="absolute bottom-2 right-2 w-6 h-6 border-b-2 border-r-2 rounded-br-lg pointer-events-none {{ $cornerColor }}"></div>

        <div class="flex flex-col lg:flex-row items-stretch justify-center gap-0 relative">
            
            {{-- LADO ESQUERDO: CONTRACAPA OU PÁGINA PAR --}}
            @if($isLeftInsideCover)
                <div class="flex-1 rounded-xl p-6 sm:p-8 flex flex-col justify-between items-center text-center relative z-10 min-h-[460px] bg-gradient-to-br shadow-[inset_0_0_30px_rgba(0,0,0,0.8),0_8px_30px_rgba(0,0,0,0.5)] border {{ $insideCoverTheme }}">
                    <div class="absolute inset-3 border border-dashed border-white/20 rounded-lg pointer-events-none"></div>
                    
                    <div class="w-full flex items-center justify-between text-xs text-gray-400 font-mono">
                        <span>Contracapa Frontal</span>
                        <span>Versus TCG Vault</span>
                    </div>

                    <div class="my-auto flex flex-col items-center">
                        <div class="w-16 h-16 rounded-2xl bg-black/40 border border-white/10 flex items-center justify-center mb-3 text-white/70 shadow-inner">
                            <i class="ph-bold ph-shield-check text-2xl {{ $isBlueBinder ? 'text-cyan-400' : 'text-amber-400' }}"></i>
                        </div>
                        <h3 class="text-sm font-extrabold text-white tracking-widest uppercase">{{ $binder->name }}</h3>
                        <p class="text-xs text-gray-300 mt-1 max-w-xs">{{ $binder->description ?: 'Pasta do Acervo Particular' }}</p>
                        <span class="mt-4 text-[10px] font-mono uppercase tracking-widest {{ $isBlueBinder ? 'text-cyan-300 border-cyan-500/30' : 'text-amber-300 border-amber-500/30' }} bg-black/40 px-3 py-1 rounded-full border">
                            {{ $binder->pocket_layout }} &bull; {{ $binder->current_capacity }} bolsos
                        </span>
                    </div>

                    <div class="w-full text-right text-[10px] text-gray-400 font-mono">
                        Folha 1 inicia à direita &rarr;
                    </div>
                </div>
            @else
                <div class="flex-1 rounded-xl p-4 sm:p-5 flex flex-col relative z-10 hover:z-30 border border-white/5 bg-[#12161c] shadow-[inset_0_0_0_1px_rgba(255,255,255,0.06),0_8px_30px_rgba(0,0,0,0.5)]">
                    <div class="flex items-center justify-between pb-3 mb-3 border-b border-white/10 text-xs text-gray-400 font-medium">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $isBlueBinder ? 'bg-cyan-400' : 'bg-sky-400' }}"></span>
                            <span>Página {{ $leftPageNum }}</span>
                        </div>
                        <span class="text-[11px] text-gray-500 font-mono">Verso da Folha</span>
                    </div>

                    <div class="grid gap-2.5 sm:gap-3 flex-1 {{ $colsClass }}">
                        @for($i = 0; $i < $pocketsPerPage; $i++)
                            @php
                                $slotKey = "{$leftPageNum}_{$i}";
                                $card = $slotsMap[$slotKey] ?? null;
                                $isActive = ($activeSlotKey === $slotKey);
                            @endphp
                            <div wire:click="handleSlotClick('{{ $slotKey }}')" class="aspect-[2.5/3.5] relative rounded-lg border border-dashed border-white/10 bg-[#12161c]/70 transition-all cursor-pointer group hover:z-50 {{ $pocketHover }} {{ $isActive ? $pocketActiveRing : '' }}">
                                <div class="absolute top-0 inset-x-1 h-[3px] rounded-full bg-gradient-to-r from-transparent via-white/20 to-transparent z-10 pointer-events-none"></div>
                                
                                @if(!$card)
                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-600 transition">
                                        <div class="w-7 h-7 rounded-full border border-dashed border-gray-600 flex items-center justify-center transition {{ $pocketAccentText }}">
                                            <i class="ph-bold ph-plus text-xs"></i>
                                        </div>
                                        <span class="text-[9px] font-mono mt-1 opacity-60">Colocar</span>
                                    </div>
                                @else
                                    @if(($card['copies'] ?? 1) >= 3)
                                        <div class="absolute inset-1 rounded-md overflow-hidden -translate-x-[3px] -translate-y-[3px] -rotate-1 opacity-85 brightness-75 bg-black/40 border border-white/20">
                                            <img src="{{ $card['image'] }}" class="w-full h-full object-cover">
                                        </div>
                                    @endif
                                    @if(($card['copies'] ?? 1) >= 2)
                                        <div class="absolute inset-1 rounded-md overflow-hidden -translate-x-[1.5px] -translate-y-[1.5px] -rotate-0.5 opacity-95 brightness-90 bg-black/60 border border-white/30">
                                            <img src="{{ $card['image'] }}" class="w-full h-full object-cover">
                                        </div>
                                    @endif
                                    <div class="absolute inset-1 rounded-md overflow-hidden bg-black border border-white/20 transition-all duration-200 shadow-[0_4px_10px_rgba(0,0,0,0.5)] {{ $viewMode === 'book' ? 'group-hover:scale-[2.3] group-hover:z-50 group-hover:shadow-[0_25px_50px_rgba(0,0,0,0.95)] group-hover:border-orange-500/80 group-hover:ring-2 group-hover:ring-orange-500/40' : '' }}">
                                        <img src="{{ $card['image'] }}" alt="{{ $card['name'] }}" class="w-full h-full object-cover">
                                        @if(($card['copies'] ?? 1) > 1)
                                            <div class="absolute top-1 right-1 bg-black/85 text-amber-400 font-extrabold text-[9px] px-1 py-0.5 rounded border border-amber-400/30">
                                                {{ $card['copies'] }}/4
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endfor
                    </div>

                    <div class="pt-3 mt-3 border-t border-white/5 flex items-center justify-between text-[11px] text-gray-500">
                        <span>Side-loading 100μm</span>
                        <span>{{ $pocketsPerPage }} bolsos</span>
                    </div>
                </div>
            @endif

            {{-- ANÉIS METÁLICOS CENTRAIS --}}
            @if($viewMode === 'book')
                <div class="w-8 lg:w-12 my-2 lg:my-0 flex flex-row lg:flex-col items-center justify-around py-4 relative z-0 shadow-2xl rounded-sm bg-gradient-to-r from-[#090d12] via-[#2d333b] to-[#090d12]">
                    <div class="w-4 h-6 lg:w-6 lg:h-4 my-2 rounded-full bg-gradient-to-b from-[#8b949e] via-[#f0f6fc] to-[#30363d] shadow-[2px_4px_8px_rgba(0,0,0,0.6)]"></div>
                    <div class="w-4 h-6 lg:w-6 lg:h-4 my-2 rounded-full bg-gradient-to-b from-[#8b949e] via-[#f0f6fc] to-[#30363d] shadow-[2px_4px_8px_rgba(0,0,0,0.6)]"></div>
                    <div class="w-4 h-6 lg:w-6 lg:h-4 my-2 rounded-full bg-gradient-to-b from-[#8b949e] via-[#f0f6fc] to-[#30363d] shadow-[2px_4px_8px_rgba(0,0,0,0.6)]"></div>
                </div>

                {{-- LADO DIREITO: PÁGINA ÍMPAR OU CONTRACAPA TRASEIRA --}}
                @if($isRightInsideCover)
                    <div class="flex-1 rounded-xl p-6 sm:p-8 flex flex-col justify-between items-center text-center relative z-10 min-h-[460px] bg-gradient-to-br shadow-[inset_0_0_30px_rgba(0,0,0,0.8),0_8px_30px_rgba(0,0,0,0.5)] border {{ $insideCoverTheme }}">
                        <div class="absolute inset-3 border border-dashed border-white/20 rounded-lg pointer-events-none"></div>
                        
                        <div class="w-full flex items-center justify-between text-xs text-gray-400 font-mono">
                            <span>Fim do Álbum</span>
                            <span>Contracapa Traseira</span>
                        </div>

                        <div class="my-auto flex flex-col items-center">
                            <div class="w-16 h-16 rounded-2xl bg-black/40 border border-white/10 flex items-center justify-center mb-3 text-white/70 shadow-inner">
                                <i class="ph-bold ph-bookmarks text-2xl {{ $isBlueBinder ? 'text-cyan-400' : 'text-amber-400' }}"></i>
                            </div>
                            <h3 class="text-sm font-extrabold text-white uppercase tracking-wider">Capacidade Total Atingida</h3>
                            <p class="text-xs text-gray-300 mt-1 max-w-xs">
                                Este fichário possui {{ $binder->current_sheets_count }} folhas físicas ({{ $totalPages }} páginas utilizáveis).
                            </p>
                        </div>

                        <div class="w-full text-left text-[10px] text-gray-400 font-mono">
                            &larr; Última folha à esquerda
                        </div>
                    </div>
                @else
                    <div class="flex-1 rounded-xl p-4 sm:p-5 flex flex-col relative z-10 hover:z-30 border border-white/5 bg-[#12161c] shadow-[inset_0_0_0_1px_rgba(255,255,255,0.06),0_8px_30px_rgba(0,0,0,0.5)]">
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-white/10 text-xs text-gray-400 font-medium">
                            <span class="text-[11px] text-gray-500 font-mono">Frente da Folha</span>
                            <div class="flex items-center gap-2">
                                <span>Página {{ $rightPageNum }}</span>
                                <span class="w-2 h-2 rounded-full {{ $isBlueBinder ? 'bg-cyan-400' : 'bg-emerald-400' }}"></span>
                            </div>
                        </div>

                        <div class="grid gap-2.5 sm:gap-3 flex-1 {{ $colsClass }}">
                            @for($i = 0; $i < $pocketsPerPage; $i++)
                                @php
                                    $slotKey = "{$rightPageNum}_{$i}";
                                    $card = $slotsMap[$slotKey] ?? null;
                                    $isActive = ($activeSlotKey === $slotKey);
                                @endphp
                                <div wire:click="handleSlotClick('{{ $slotKey }}')" class="aspect-[2.5/3.5] relative rounded-lg border border-dashed border-white/10 bg-[#12161c]/70 transition-all cursor-pointer group hover:z-50 {{ $pocketHover }} {{ $isActive ? $pocketActiveRing : '' }}">
                                    <div class="absolute top-0 inset-x-1 h-[3px] rounded-full bg-gradient-to-r from-transparent via-white/20 to-transparent z-10 pointer-events-none"></div>
                                    
                                    @if(!$card)
                                        <div class="absolute inset-0 flex flex-col items-center justify-center text-gray-600 transition">
                                            <div class="w-7 h-7 rounded-full border border-dashed border-gray-600 flex items-center justify-center transition {{ $pocketAccentText }}">
                                                <i class="ph-bold ph-plus text-xs"></i>
                                            </div>
                                            <span class="text-[9px] font-mono mt-1 opacity-60">Colocar</span>
                                        </div>
                                    @else
                                        @if(($card['copies'] ?? 1) >= 3)
                                            <div class="absolute inset-1 rounded-md overflow-hidden -translate-x-[3px] -translate-y-[3px] -rotate-1 opacity-85 brightness-75 bg-black/40 border border-white/20">
                                                <img src="{{ $card['image'] }}" class="w-full h-full object-cover">
                                            </div>
                                        @endif
                                        @if(($card['copies'] ?? 1) >= 2)
                                            <div class="absolute inset-1 rounded-md overflow-hidden -translate-x-[1.5px] -translate-y-[1.5px] -rotate-0.5 opacity-95 brightness-90 bg-black/60 border border-white/30">
                                                <img src="{{ $card['image'] }}" class="w-full h-full object-cover">
                                            </div>
                                        @endif
                                        <div class="absolute inset-1 rounded-md overflow-hidden bg-black border border-white/20 transition-all duration-200 shadow-[0_4px_10px_rgba(0,0,0,0.5)] {{ $viewMode === 'book' ? 'group-hover:scale-[2.3] group-hover:z-50 group-hover:shadow-[0_25px_50px_rgba(0,0,0,0.95)] group-hover:border-orange-500/80 group-hover:ring-2 group-hover:ring-orange-500/40' : '' }}">
                                            <img src="{{ $card['image'] }}" alt="{{ $card['name'] }}" class="w-full h-full object-cover">
                                            @if(($card['copies'] ?? 1) > 1)
                                                <div class="absolute top-1 right-1 bg-black/85 text-amber-400 font-extrabold text-[9px] px-1 py-0.5 rounded border border-amber-400/30">
                                                    {{ $card['copies'] }}/4
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endfor
                        </div>

                        <div class="pt-3 mt-3 border-t border-white/5 flex items-center justify-between text-[11px] text-gray-500">
                            <span>{{ $pocketsPerPage }} bolsos</span>
                            <span>Versus TCG Vault</span>
                        </div>
                    </div>
                @endif
            @endif

        </div>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-white/10">
            <div class="flex items-center gap-2">
                <button wire:click="prevPage" class="px-4 py-2 rounded-xl bg-[#21262d] hover:bg-[#30363d] text-white text-xs font-semibold flex items-center gap-2 border border-white/10 transition shadow-lg">
                    <i class="ph-bold ph-caret-left"></i>
                    <span>Folha Anterior</span>
                </button>
                <button wire:click="nextPage({{ $totalPages }})" class="px-4 py-2 rounded-xl bg-[#21262d] hover:bg-[#30363d] text-white text-xs font-semibold flex items-center gap-2 border border-white/10 transition shadow-lg">
                    <span>Próxima Folha</span>
                    <i class="ph-bold ph-caret-right"></i>
                </button>
            </div>

            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-gray-300 font-mono">
                    @if($viewMode === 'book')
                        @if($currentPageIndex === 1)
                            Abertura &bull; Página 1 de {{ $totalPages }}
                        @elseif($rightPageNum > $totalPages)
                            Página {{ $leftPageNum }} &bull; Fechamento do Álbum
                        @else
                            Páginas {{ $leftPageNum }} - {{ $rightPageNum }} de {{ $totalPages }}
                        @endif
                    @else
                        Página {{ $currentPageIndex }} de {{ $totalPages }}
                    @endif
                </span>

                @if(!$binder->is_locked_sheets)
                    <button wire:click="addNewSheet" class="text-xs px-3 py-1.5 rounded-lg {{ $isBlueBinder ? 'bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-400 border-cyan-500/30' : 'bg-sky-500/10 hover:bg-sky-500/20 text-sky-400 border-sky-500/30' }} border transition flex items-center gap-1.5 font-medium">
                        <i class="ph-bold ph-plus text-[10px]"></i>
                        <span>Adicionar Folha</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- MODAL 1: GAVETA DO ACERVO -->
    @if($modalPickerOpen)
        <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-[#161b22] border border-white/10 rounded-3xl max-w-2xl w-full p-6 shadow-2xl relative text-white max-h-[88vh] flex flex-col">
                <button wire:click="closeModals" class="absolute top-5 right-5 text-gray-400 hover:text-white">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>

                <div>
                    <h3 class="text-base font-black text-white">Adicionar Carta neste Bolso</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Selecione uma carta disponível do seu Acervo Geral (Pág. {{ $selectedPage }}, Posição #{{ $selectedSlotPos + 1 }}).</p>
                </div>

                <div class="mt-4 relative">
                    <input type="text" wire:model.live.debounce.250ms="pickerSearch" placeholder="Buscar por nome ou número..." class="w-full bg-[#0d1117] border border-white/10 rounded-xl px-3.5 py-2 text-xs text-white placeholder-gray-500 focus:outline-none {{ $isBlueBinder ? 'focus:border-cyan-400' : 'focus:border-orange-500' }}">
                </div>

                <div class="mt-4 flex-1 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 pr-1">
                    @forelse($availableCollectionItems as $item)
                        @php $print = $item->catalogPrint; @endphp
                        <div wire:click="assignCardToSlot({{ $item->id }})" class="p-2 rounded-xl bg-[#0d1117] border border-white/5 hover:border-cyan-400 hover:bg-cyan-500/10 cursor-pointer transition flex flex-col justify-between group">
                            <div class="aspect-[2.5/3.5] rounded-lg overflow-hidden bg-black/50 mb-2">
                                <img src="{{ $print->image_path ?: '/images/placeholder-card.png' }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                            </div>
                            <div>
                                <span class="text-[8px] font-mono font-bold text-cyan-400 uppercase">{{ $print->set->code }} #{{ $print->collector_number }}</span>
                                <h5 class="text-xs font-bold text-white truncate leading-tight">{{ $print->printed_name ?: ($print->concept->name ?? 'Carta') }}</h5>
                                <div class="flex items-center justify-between text-[10px] text-gray-400 mt-1">
                                    <span>{{ strtoupper($item->language) }} &bull; {{ $item->condition }}</span>
                                    <span class="font-extrabold text-emerald-400">{{ $item->quantity }}x</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-12 text-center text-xs text-gray-500">
                            Nenhuma carta encontrada no seu acervo.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4 pt-3 border-t border-white/10 flex justify-end">
                    <button wire:click="closeModals" class="px-4 py-2 rounded-xl bg-white/5 hover:bg-white/10 text-xs font-bold text-gray-300">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 2: AJUSTE DE CÓPIAS / RETIRAR DO BOLSO -->
    @if($modalSlotOpen && $activeSlotCard)
        <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-[#161b22] border border-white/10 rounded-2xl max-w-md w-full p-6 shadow-2xl relative text-white">
                <button wire:click="closeModals" class="absolute top-4 right-4 text-gray-400 hover:text-white text-sm w-8 h-8 rounded-full bg-[#21262d] flex items-center justify-center transition">
                    <i class="ph-bold ph-x"></i>
                </button>

                <div class="flex gap-4">
                    <img src="{{ $activeSlotCard['image'] }}" class="w-24 rounded-lg shadow-md aspect-[2.5/3.5] bg-black object-cover shrink-0">
                    <div class="flex-1 min-w-0">
                        <span class="text-[10px] uppercase font-bold tracking-wider text-cyan-400">{{ $activeSlotCard['code'] ?? 'CARD' }} #{{ $activeSlotCard['number'] ?? '---' }}</span>
                        <h3 class="text-base font-bold text-white leading-tight mt-0.5 truncate">{{ $activeSlotCard['name'] }}</h3>
                        <p class="text-xs text-gray-400 mt-1">{{ $activeSlotCard['set'] ?? 'Coleção' }}</p>
                        
                        <div class="mt-3 bg-[#0d1117] p-2 rounded-xl border border-white/5 flex items-center justify-between text-xs">
                            <span class="text-gray-400">{{ $activeSlotCard['language'] ?? 'PT' }} &bull; {{ $activeSlotCard['condition'] ?? 'NM' }}</span>
                            <span class="font-bold text-emerald-400">{{ $activeSlotCard['price'] ?? 'R$ 0,00' }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-white/10">
                    <label class="block text-xs font-semibold text-gray-300 mb-2">Cartas físicas neste bolso (Máx. 4):</label>
                    <div class="flex items-center justify-between bg-[#0d1117] p-2 rounded-xl border border-white/10">
                        <button wire:click="adjustCopies(-1)" class="w-8 h-8 rounded-lg bg-[#21262d] hover:bg-[#30363d] text-white flex items-center justify-center font-bold">
                            <i class="ph-bold ph-minus"></i>
                        </button>
                        <span class="font-bold text-white text-base">{{ $activeSlotCard['copies'] }} de {{ $activeSlotCard['max_avail'] ?? 4 }} cópia(s)</span>
                        <button wire:click="adjustCopies(1)" class="w-8 h-8 rounded-lg bg-[#21262d] hover:bg-[#30363d] text-white flex items-center justify-center font-bold">
                            <i class="ph-bold ph-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-between pt-2">
                    <button wire:click="removeCardFromSlot" class="px-3.5 py-2 rounded-xl text-xs font-bold text-rose-400 hover:bg-rose-500/10 transition flex items-center gap-1.5">
                        <i class="ph-bold ph-arrow-u-up-left"></i> Devolver ao Acervo
                    </button>
                    <button wire:click="closeModals" class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold transition text-xs shadow-md">
                        Concluir
                    </button>
                </div>
            </div>
        </div>
    @endif
    @endif
</div>
