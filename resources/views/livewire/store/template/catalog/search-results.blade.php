<div>
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
                {{ count($estoqueResults) + count($globalResults) }} resultado(s) encontrado(s)
            </h2>
        </div>

        {{-- CONTEÚDO PRINCIPAL --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{-- Nenhum resultado --}}
            @if(empty($estoqueResults) && empty($globalResults))
                <div class="flex flex-col items-center justify-center py-24 text-center text-gray-400">
                    <i class="ph ph-magnifying-glass text-6xl mb-4 opacity-30"></i>
                    <p class="text-lg font-semibold">Nenhum resultado encontrado</p>
                    <p class="text-sm mt-1">Tente verificar a ortografia ou use termos mais simples.</p>
                </div>
            @else

                {{-- GRID 1: Meu Estoque (Cadastrados) --}}
                @if(!empty($estoqueResults))
                    <div class="mb-12">
                        <div class="flex items-center gap-3 mb-6 border-b border-gray-200 pb-2">
                            <h2 class="text-sm font-black uppercase tracking-widest text-gray-900 dark:text-white">
                                Meu Estoque
                            </h2>
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ count($estoqueResults) }} itens</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach($estoqueResults as $item)
                                @include('partials.template.search-card', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- GRID 2: Catálogo Global (Sugestões de Cadastro - Só aparece se tiver itens globais) --}}
                @if(!empty($globalResults))
                    <div class="mt-12 bg-gray-50/50 dark:bg-slate-900/30 p-6 rounded-2xl border border-gray-100 dark:border-slate-800">
                        <div class="flex items-center gap-3 mb-6 border-b border-gray-200 dark:border-slate-700 pb-2">
                            <h2 class="text-sm font-black uppercase tracking-widest text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                <i class="ph ph-globe text-lg"></i> Catálogo Global (Não Cadastrados)
                            </h2>
                            <span class="bg-gray-200 dark:bg-slate-700 text-gray-600 dark:text-gray-300 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ count($globalResults) }} sugestões</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-6">Estes itens pertencem ao banco de dados global da plataforma e não possuem estoque na sua loja. Clique em um deles para iniciar a ingestão.</p>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 opacity-90">
                            @foreach($globalResults as $item)
                                @include('partials.template.search-card', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                @endif

            @endif
        </div>
    </div>
</div>