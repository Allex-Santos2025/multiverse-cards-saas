<div>
    {{-- BREADCRUMB --}}
    <div class="py-2" style="background-color: var(--cor-secundaria); color: var(--cor-texto-secundaria);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-xs font-bold flex gap-2 items-center">
                <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" class="hover:underline opacity-90">Home</a>
                <span class="opacity-50">></span>
                <span>Busca Global</span>
            </nav>
        </div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- TÍTULO --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black italic uppercase tracking-tight"
                style="color: var(--cor-texto-principal);">
                RESULTADOS PARA
                <span style="color: var(--cor-cta);">
                    "{{ $query }}"
                </span>
            </h1>
            <h2 class="text-sm mt-1 uppercase font-bold opacity-60"
                style="color: var(--cor-texto-principal);">
                {{ $totalResultados }} resultado(s) encontrado(s)
            </h2>
        </div>

        {{-- CONTEÚDO PRINCIPAL --}}
        <div>
            @if(empty($resultsByGame))
                <div class="flex flex-col items-center justify-center py-24 text-center text-gray-400">
                    <i class="ph ph-magnifying-glass text-6xl mb-4 opacity-30"></i>
                    <p class="text-lg font-semibold">Nenhum resultado encontrado</p>
                    <p class="text-sm mt-1">Tente verificar a ortografia ou use termos mais simples.</p>
                </div>
            @else
                <div class="space-y-16">
                    @foreach($resultsByGame as $gameData)
                        <div class="border border-gray-200 dark:border-slate-800 rounded-2xl p-6 bg-white dark:bg-slate-900 shadow-sm">
                            
                            {{-- CABEÇALHO DO JOGO --}}
                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-800 pb-4 mb-6">
                                <div class="flex items-center gap-3">
                                    <span class="w-3 h-3 rounded-full bg-red-600 inline-block"></span>
                                    <h2 class="text-xl font-black uppercase tracking-tight text-gray-900 dark:text-white">
                                        {{ $gameData['game_name'] }}
                                    </h2>
                                </div>
                                <span class="text-xs font-bold px-3 py-1 bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-gray-300 rounded-full uppercase">
                                    {{ count($gameData['estoque']) + count($gameData['fantasmas']) }} cartas
                                </span>
                            </div>

                            {{-- CARTAS EM ESTOQUE / CADASTRADAS --}}
                            @if(!empty($gameData['estoque']))
                                <div class="mb-8">
                                    <div class="flex items-center gap-2 mb-4">
                                        <i class="ph ph-check-circle text-emerald-600 text-lg"></i>
                                        <h3 class="text-xs font-black uppercase tracking-widest text-gray-700 dark:text-gray-300">
                                            Estoque da Loja
                                        </h3>
                                        <span class="bg-emerald-100 text-emerald-800 text-[10px] px-2 py-0.5 rounded-full font-bold">
                                            {{ count($gameData['estoque']) }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                                        @foreach($gameData['estoque'] as $item)
                                            @include('partials.template.search-card', ['item' => $item])
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- FANTASMAS (SÓ LOJISTA) --}}
                            @if($isLojista && !empty($gameData['fantasmas']))
                                <div class="mt-6 pt-6 border-t border-dashed border-gray-200 dark:border-slate-800 bg-red-50/20 dark:bg-red-950/10 p-4 rounded-xl">
                                    <div class="flex items-center gap-2 mb-3">
                                        <i class="ph ph-ghost text-red-500 text-lg"></i>
                                        <h3 class="text-xs font-black uppercase tracking-widest text-red-600 dark:text-red-400">
                                            Catálogo Global (Sugestões de Cadastro)
                                        </h3>
                                        <span class="bg-red-100 text-red-700 text-[10px] px-2 py-0.5 rounded-full font-bold">
                                            {{ count($gameData['fantasmas']) }} sugestões
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mb-4">Itens sem estoque na sua loja. Clique para cadastrar e precificar.</p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 opacity-80 hover:opacity-100 transition-opacity">
                                        @foreach($gameData['fantasmas'] as $item)
                                            @include('partials.template.search-card', ['item' => $item])
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
