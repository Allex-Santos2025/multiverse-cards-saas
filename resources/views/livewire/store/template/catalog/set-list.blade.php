<div>
    {{-- Barra de Navegação (Breadcrumb) --}}
    <div class="py-2" style="background-color: var(--cor-secundaria); color: var(--cor-texto-secundaria);">
        <div class="max-w-7xl mx-auto px-4">
            <nav class="text-xs font-bold flex gap-2">
                <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" class="hover:underline opacity-90">Home</a>
                <span class="opacity-50">></span>
                <span>{{ $game->name }} - Edições</span>
            </nav>
        </div>
    </div>

    {{-- A MÁGICA: Fundo da Loja + Herança do Texto Principal (com o Salva-Vidas) --}}
    <main class="flex-grow py-8 min-h-screen transition-colors duration-300" style="background-color: var(--cor-bg-loja); color: var(--cor-texto-principal);">
        <div class="max-w-7xl mx-auto px-4">
            
            {{-- Cabeçalho e Filtros --}}
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 gap-4">
                
                {{-- Título: Herda a cor segura, jogo com a cor CTA --}}
                <h2 class="text-3xl font-black italic uppercase leading-none text-inherit">
                    Edições de <span style="color: var(--cor-cta);">{{ $game->name }}</span>
                </h2>
                
                <div class="flex items-center gap-4 w-full md:w-auto bg-black/5 dark:bg-white/5 p-2 rounded-xl shadow-sm border border-gray-200/30 dark:border-slate-700/50">
                    <select wire:model.live="sortOrder" class="w-full md:w-auto bg-transparent border-none text-sm font-bold outline-none focus:ring-0 cursor-pointer text-inherit">
                        <option value="release_desc" class="text-gray-900">Lançamento (Mais Novas)</option>
                        <option value="release_asc" class="text-gray-900">Lançamento (Mais Antigas)</option>
                        <option value="az" class="text-gray-900">Nome da Edição [A-Z]</option>
                        <option value="za" class="text-gray-900">Nome da Edição [Z-A]</option>
                    </select>
                </div>
            </div>

            {{-- Filtro de Alfabeto --}}
            @if($viewMode === 'alphabetical')
                <div class="flex flex-wrap justify-center gap-1 mb-10 bg-black/5 dark:bg-white/5 p-3 rounded-xl shadow-sm border border-gray-200/30 dark:border-slate-700/50">
                    <button wire:click="filterByLetter(null)" class="w-8 h-8 flex items-center justify-center rounded text-xs font-black transition-all" style="{{ is_null($activeLetter) ? 'background-color: var(--cor-1); color: var(--cor-texto-btn-1);' : 'background-color: transparent; color: inherit;' }}">#</button>
                    @foreach($alphabet as $letter)
                        <button wire:click="filterByLetter('{{ $letter }}')" class="w-8 h-8 flex items-center justify-center rounded text-xs font-black transition-all opacity-80 hover:opacity-100" style="{{ $activeLetter === $letter ? 'background-color: var(--cor-1); color: var(--cor-texto-btn-1); opacity: 1;' : 'background-color: transparent; color: inherit;' }}">{{ $letter }}</button>
                    @endforeach
                </div>
            @endif
            
            @if($groupedSets->isEmpty())
                <div class="text-center py-20 rounded-2xl border border-dashed border-gray-300 dark:border-slate-700/50">
                    <i class="ph ph-package text-6xl mb-3 opacity-50" style="color: var(--cor-terciaria);"></i>
                    <p class="font-bold text-inherit opacity-80">Nenhuma coleção encontrada.</p>
                </div>
            @else

                {{-- MODO 1: ALFABÉTICO --}}
                @if($viewMode === 'alphabetical')
                    <div class="columns-1 md:columns-2 lg:columns-3 gap-8 space-y-8">
                        @foreach($groupedSets as $letter => $setsInLetter)
                            <div class="break-inside-avoid bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-slate-700">
                                <h3 class="text-2xl font-black border-b border-gray-100 dark:border-slate-700 pb-2 mb-4" style="color: var(--cor-terciaria);">{{ $letter }}</h3>
                                <ul class="space-y-4">
                                    @foreach($setsInLetter as $set)
                                        <li>
                                            {{-- Herdando a Cor do Main --}}
                                            <a href="#" class="flex items-center gap-4 text-sm font-bold transition-all group/link hover:text-[var(--cor-cta)] text-inherit">
                                                <div class="relative w-8 h-8 flex items-center justify-center shrink-0 transition-transform group-hover/link:scale-125">
                                                    <x-set-symbol :path="''" :code="$set->code ?? ''" rarity="common" size="w-8 h-8" />
                                                </div>
                                                <div class="flex flex-col">
                                                    <span class="truncate leading-tight">{{ $set->name }}</span>
                                                    <span class="text-[10px] opacity-60 font-mono uppercase tracking-widest">{{ $set->code }}</span>
                                                </div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>

                {{-- MODO 2: TIMELINE --}}
                @else
                    <div class="relative w-full max-w-5xl mx-auto py-10">
                        
                        {{-- Linha Central (Cor Terciária) --}}
                        <div class="absolute top-0 bottom-0 left-8 md:left-1/2 w-0.5 transform md:-translate-x-1/2 opacity-30" style="background-color: var(--cor-terciaria);"></div>

                        @php $counter = 0; @endphp
                        @foreach($groupedSets as $year => $setsInYear)
                            @php $isEven = ($counter % 2 == 0); $counter++; @endphp

                            <div class="relative flex flex-col md:flex-row items-center justify-between mb-16 group">
                                
                                {{-- Bolha do Ano (Cor CTA) --}}
                                <div class="absolute left-8 md:left-1/2 transform -translate-x-1/2 flex items-center justify-center w-14 h-14 bg-white dark:bg-slate-900 border-[3px] rounded-full z-10 transition-transform duration-500 group-hover:scale-110 shadow-sm" style="border-color: var(--cor-cta);">
                                    <span class="text-sm font-black tracking-tight" style="color: var(--cor-cta);">{{ $year }}</span>
                                </div>

                                @if($isEven)
                                    {{-- Anos Gigantes de Fundo (Cor Terciária) --}}
                                    <div class="hidden md:flex w-5/12 justify-end pr-16 opacity-10 group-hover:opacity-100 transition-opacity duration-500">
                                        <span class="text-8xl font-black tracking-tighter leading-none" style="color: var(--cor-terciaria);">{{ $year }}</span>
                                    </div>
                                    <div class="w-full md:w-5/12 pl-24 md:pl-12">
                                        <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-2xl p-6 shadow-sm border border-gray-200/50 dark:border-slate-700/50 transition-all duration-300 hover:shadow-xl relative overflow-hidden group/card hover:border-[var(--cor-cta)]">
                                            <ul class="space-y-4 relative z-10">
                                                @foreach($setsInYear as $set)
                                                    <li>
                                                        {{-- Herdando o texto principal --}}
                                                        <a href="#" class="flex items-center gap-4 text-sm font-bold transition-all group/link hover:text-[var(--cor-cta)] text-inherit">
                                                            <div class="relative w-8 h-8 flex items-center justify-center shrink-0 transition-transform group-hover/link:scale-125">
                                                                <x-set-symbol :path="''" :code="$set->code ?? ''" rarity="common" size="w-8 h-8" />
                                                            </div>
                                                            <div class="flex flex-col">
                                                                <span class="truncate leading-tight">{{ $set->name }}</span>
                                                                <span class="text-[10px] opacity-60 font-mono uppercase tracking-widest">{{ $set->code }}</span>
                                                            </div>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @else
                                    <div class="w-full md:w-5/12 pl-24 md:pl-0 md:pr-12 text-left">
                                        <div class="bg-white/60 dark:bg-slate-800/60 backdrop-blur-sm rounded-2xl p-6 shadow-sm border border-gray-200/50 dark:border-slate-700/50 transition-all duration-300 hover:shadow-xl relative overflow-hidden group/card hover:border-[var(--cor-cta)]">
                                            <ul class="space-y-4 relative z-10">
                                                @foreach($setsInYear as $set)
                                                    <li>
                                                        {{-- Herdando o texto principal --}}
                                                        <a href="#" class="flex items-center gap-4 text-sm font-bold transition-all group/link hover:text-[var(--cor-cta)] text-inherit">
                                                            <div class="relative w-8 h-8 flex items-center justify-center shrink-0 transition-transform group-hover/link:scale-125">
                                                                <x-set-symbol :path="''" :code="$set->code ?? ''" rarity="common" size="w-8 h-8" />
                                                            </div>
                                                            <div class="flex flex-col">
                                                                <span class="truncate leading-tight">{{ $set->name }}</span>
                                                                <span class="text-[10px] opacity-60 font-mono uppercase tracking-widest">{{ $set->code }}</span>
                                                            </div>
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    {{-- Anos Gigantes de Fundo (Cor Terciária) --}}
                                    <div class="hidden md:flex w-5/12 justify-start pl-16 opacity-10 group-hover:opacity-100 transition-opacity duration-500">
                                        <span class="text-8xl font-black tracking-tighter leading-none" style="color: var(--cor-terciaria);">{{ $year }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </main>
</div>