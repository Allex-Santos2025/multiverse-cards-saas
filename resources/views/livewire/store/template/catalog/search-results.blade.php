<div> {{-- ESTE É O NOVO ELEMENTO RAIZ --}}

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
        {{-- TÍTULO (mesmo estilo da tela de singles) --}}
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
                {{ count($exactResults) + count($relatedResults) }} resultado(s) encontrado(s)
            </h2>
        </div>

        {{-- CONTEÚDO PRINCIPAL --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            {{-- Nenhum resultado --}}
            @if(empty($exactResults) && empty($relatedResults))
                <div class="flex flex-col items-center justify-center py-24 text-center text-gray-400">
                    <i class="ph ph-magnifying-glass text-6xl mb-4 opacity-30"></i>
                    <p class="text-lg font-semibold">Nenhum resultado encontrado</p>
                    <p class="text-sm mt-1">Tente verificar a ortografia ou use termos mais simples.</p>
                </div>
            @else

                {{-- Resultados Exatos --}}
                @if(!empty($exactResults))
                    <div class="mb-10">
                        <h2 class="text-xs font-black uppercase tracking-widest text-gray-500 border-b border-gray-200 pb-2 mb-4">
                            Carta encontrada
                        </h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach($exactResults as $item)
                                @include('partials.template.search-card', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Resultados Relacionados --}}
                @if(!empty($relatedResults))
                    <div>
                        <h2 class="text-xs font-black uppercase tracking-widest text-gray-500 border-b border-gray-200 pb-2 mb-4">
                            Sugestões relacionadas
                        </h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            @foreach($relatedResults as $item)
                                @include('partials.template.search-card', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                @endif

            @endif
        </div>
    </div>
</div> {{-- FIM DO NOVO ELEMENTO RAIZ --}}