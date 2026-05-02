<div class="space-y-4">
    
    {{-- CABEÇALHO --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2">
        <div>
            <h2 class="text-xl font-black text-slate-900 tracking-tight">Want List (Favoritos)</h2>
            <p class="text-xs text-slate-500 mt-1">Acesse a loja para escolher a edição exata da carta conceito e adicionar ao carrinho.</p>
        </div>
        <span class="bg-slate-100 text-slate-600 font-bold text-xs px-3 py-1.5 rounded-lg border border-slate-200">
            {{ count($itensWantlist) }} Cartas Salvas
        </span>
    </div>

    {{-- LISTA COMPACTA DE CARTAS (CONCEITO) --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="divide-y divide-slate-100">
            @foreach($itensWantlist as $item)
                <div class="p-3 flex flex-col sm:flex-row sm:items-center justify-between hover:bg-slate-50 transition-colors group gap-3 sm:gap-0">
                    
                    {{-- 1. Ícone e Nome do Conceito --}}
                    <div class="flex items-center gap-3 min-w-[250px] flex-1">
                        <div class="w-8 h-10 bg-slate-100 rounded flex-shrink-0 border border-slate-200 shadow-sm flex items-center justify-center text-slate-400">
                            <i class="ph-fill ph-cards text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm leading-tight">{{ $item['nome'] }}</h3>
                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">Múltiplas Edições Disponíveis</p>
                        </div>
                    </div>

                    {{-- 2. Loja Favoritada --}}
                    <div class="hidden md:flex items-center gap-2 w-[200px]">
                        <div class="w-6 h-6 rounded {{ $item['loja_cor'] }} text-white flex items-center justify-center font-black text-[7px] uppercase shadow-sm shrink-0">
                            {{ $item['loja_sigla'] }}
                        </div>
                        <span class="text-[11px] font-bold text-slate-600 truncate">{{ $item['loja_nome'] }}</span>
                    </div>

                    {{-- 3. Preço Mínimo --}}
                    <div class="w-[120px] text-left sm:text-right pr-4">
                        <p class="text-[9px] font-black uppercase text-slate-400 tracking-wider">Mínimo</p>
                        <p class="text-sm font-black text-emerald-600">R$ {{ number_format($item['preco_minimo'], 2, ',', '.') }}</p>
                    </div>

                    {{-- 4. Ações (Lixeira e Ver na Loja) --}}
                    <div class="flex items-center justify-end gap-2 sm:w-auto w-full border-t sm:border-none border-slate-100 pt-3 sm:pt-0">
                        <button wire:click="removerDaLista({{ $item['id'] }})" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-300 hover:bg-red-50 hover:text-red-600 transition-colors" title="Remover dos Favoritos">
                            <i class="ph-bold ph-trash text-lg"></i>
                        </button>
                        <a href="{{ $item['url_produto'] }}" class="bg-slate-900 hover:bg-orange-500 text-white font-black text-[10px] uppercase tracking-wider py-2 px-4 rounded-lg transition-colors shadow-sm flex items-center gap-1.5">
                            Ver Loja <i class="ph-bold ph-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>