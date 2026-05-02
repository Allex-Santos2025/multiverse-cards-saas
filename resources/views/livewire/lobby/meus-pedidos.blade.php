<div class="space-y-4">
    
    {{-- CABEÇALHO E FILTROS --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-2">
        <h2 class="text-xl font-black text-slate-900 tracking-tight">Histórico de Compras</h2>
        
        <div class="flex items-center gap-1 bg-white p-1 rounded-lg shadow-sm border border-slate-200">
            <button 
                wire:click="setFiltro('todos')" 
                class="px-3 py-1 rounded text-xs font-bold transition-colors {{ $filtroAtivo === 'todos' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">
                Todos
            </button>
            <button 
                wire:click="setFiltro('caminho')" 
                class="px-3 py-1 rounded text-xs font-bold transition-colors {{ $filtroAtivo === 'caminho' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">
                A Caminho
            </button>
            <button 
                wire:click="setFiltro('entregues')" 
                class="px-3 py-1 rounded text-xs font-bold transition-colors {{ $filtroAtivo === 'entregues' ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-50' }}">
                Entregues
            </button>
        </div>
    </div>

    {{-- LISTA DE PEDIDOS COMPACTA --}}
    <div class="space-y-2.5">
        @foreach($pedidos as $pedido)
            <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden transition-all duration-200 hover:border-slate-300">
                
                {{-- LINHA PRINCIPAL VISÍVEL --}}
                <div class="p-4 flex flex-col lg:flex-row lg:items-center gap-4">
                    
                    {{-- 1. Loja e Info Básica --}}
                    <div class="flex items-center gap-3 min-w-[220px]">
                        <div class="w-10 h-10 rounded-lg {{ $pedido['loja_cor'] }} text-white flex items-center justify-center font-black text-[8px] uppercase shadow-sm shrink-0">
                            {{ $pedido['loja_sigla'] }}
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 text-sm leading-tight">{{ $pedido['loja'] }}</h3>
                            <p class="text-[10px] font-medium text-slate-400 mt-0.5">Pedido {{ $pedido['codigo'] }} • {{ $pedido['data'] }}</p>
                        </div>
                    </div>

                    {{-- 2. Barra de Progresso e Status (CENTRALIZADA COMO NO PRINT) --}}
                    <div class="flex-1 w-full lg:px-8">
                        <div class="flex justify-between items-end mb-1.5">
                            <span class="text-[11px] font-black {{ $pedido['status_cor'] }}">{{ $pedido['status_texto'] }}</span>
                            <span class="text-[10px] font-bold text-slate-400">{{ $pedido['info_extra_1'] }}</span>
                        </div>
                        <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $pedido['barra_cor'] }} rounded-full" style="width: {{ $pedido['progresso'] }};"></div>
                        </div>
                        @if($pedido['info_extra_2'])
                            <div class="mt-1.5">
                                <span class="text-[10px] font-bold text-slate-400">{{ $pedido['info_extra_2'] }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- 3. Total e Botão Dropdown --}}
                    <div class="flex items-center justify-between lg:justify-end gap-5 min-w-[180px] mt-2 lg:mt-0 pt-2 lg:pt-0 border-t lg:border-none border-slate-100">
                        <div class="text-right">
                            <p class="text-[8px] font-black uppercase text-slate-400 tracking-wider">Total</p>
                            <p class="text-sm font-black text-slate-900">R$ {{ number_format($pedido['total'], 2, ',', '.') }}</p>
                        </div>
                        
                        <button 
                            @click="open = !open" 
                            class="flex items-center gap-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 px-3 py-1.5 rounded-lg transition-colors">
                            <span class="text-xs font-bold text-slate-700">Ver {{ $pedido['qtd_itens'] }} Itens <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 inline-block ml-1" :class="open ? 'rotate-180' : ''"></i></span>
                        </button>
                    </div>

                </div>

                {{-- ÁREA EXPANSÍVEL (ITENS DO PEDIDO) --}}
                <div x-show="open" x-collapse x-cloak>
                    <div class="bg-slate-50 border-t border-slate-100 p-4">
                        <div class="space-y-2">
                            @foreach($pedido['itens'] as $item)
                                <div class="flex items-center justify-between bg-white border border-slate-200 p-2.5 rounded-lg shadow-sm">
                                    <div class="flex items-center gap-2.5">
                                        <div class="bg-slate-100 text-slate-500 font-black text-[10px] px-1.5 py-0.5 rounded">
                                            {{ $item['qtd'] }}x
                                        </div>
                                        <div class="flex items-baseline gap-2">
                                            <p class="text-xs font-bold text-slate-900">{{ $item['nome'] }}</p>
                                            <p class="text-[9px] font-bold text-slate-400">{{ $item['edicao'] }} • {{ $item['condicao'] }}</p>
                                        </div>
                                    </div>
                                    <div class="text-xs font-black text-emerald-600">
                                        R$ {{ number_format($item['preco'], 2, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-3 flex justify-end gap-2">
                            <button class="text-[10px] font-bold text-slate-500 hover:text-slate-900 transition-colors px-3 py-1.5">Problemas?</button>
                            <button class="text-[10px] font-bold bg-white border border-slate-200 hover:border-orange-500 hover:text-orange-600 transition-colors px-3 py-1.5 rounded-md shadow-sm">Detalhes Completos</button>
                        </div>
                    </div>
                </div>

            </div>
        @endforeach
    </div>

</div>