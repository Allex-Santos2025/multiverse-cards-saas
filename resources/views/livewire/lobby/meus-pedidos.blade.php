<div>
    {{-- EFEITO FOIL EXATO DO SISTEMA (Responsivo via Porcentagem) --}}
    <style>
        .efeito-foil-grande {
            position: relative;
            overflow: hidden;
        }
        .efeito-foil-grande::after {
            content: '';
            position: absolute;
            top: 0; left: -150%; width: 50%; height: 100%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.2) 50%, rgba(255,255,255,0) 100%);
            transform: skewX(-25deg);
            animation: brilho-foil-grande 6s ease-in-out infinite;
            pointer-events: none;
            mix-blend-mode: overlay;
            z-index: 20;
            border-radius: 0.75rem;
        }
        @keyframes brilho-foil-grande {
            0% { left: -150%; }
            30% { left: 250%; }
            100% { left: 250%; }
        }
    </style>

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
                            @if(!empty($pedido['loja_avatar']))
                                <div class="w-10 h-10 rounded-lg shadow-sm shrink-0 overflow-hidden border border-slate-100 bg-white">
                                    <img src="{{ $pedido['loja_avatar'] }}" alt="{{ $pedido['loja'] }}" class="w-full h-full object-contain p-0.5">
                                </div>
                                <div>
                                    <h3 class="font-black text-slate-900 text-sm leading-tight">{{ $pedido['loja'] }}</h3>
                                    <p class="text-[10px] font-medium text-slate-400 mt-0.5">Pedido {{ $pedido['codigo'] }} • {{ $pedido['data'] }}</p>
                                </div>

                            @elseif(!empty($pedido['loja_logo']))
                                <div class="h-10 shrink-0 overflow-hidden flex items-center justify-start">
                                    <img src="{{ $pedido['loja_logo'] }}" alt="{{ $pedido['loja'] }}" class="h-full object-contain max-w-[140px]">
                                </div>
                                <div class="flex flex-col justify-center ml-2">
                                    <p class="text-[10px] font-medium text-slate-400 mt-0.5">Pedido {{ $pedido['codigo'] }} • {{ $pedido['data'] }}</p>
                                </div>

                            @else
                                <div class="w-10 h-10 rounded-lg text-white flex items-center justify-center font-black text-[10px] uppercase shadow-sm shrink-0" style="background-color: {{ $pedido['loja_cor_hex'] }}">
                                    {{ $pedido['loja_sigla'] }}
                                </div>
                                <div>
                                    <h3 class="font-black text-slate-900 text-sm leading-tight">{{ $pedido['loja'] }}</h3>
                                    <p class="text-[10px] font-medium text-slate-400 mt-0.5">Pedido {{ $pedido['codigo'] }} • {{ $pedido['data'] }}</p>
                                </div>
                            @endif
                        </div>

                        {{-- 2. Barra de Progresso e Status --}}
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
                                    <div class="relative" 
                                         x-data="{ showCard: false, mouseX: 0, mouseY: 0 }"
                                         @mousemove="mouseX = $event.clientX; mouseY = $event.clientY">
                                         
                                        <div class="flex items-center justify-between bg-white border p-4 rounded-xl shadow-sm transition-all 
                                                    {{ $item['is_foil'] ? 'border-amber-200 bg-gradient-to-r from-amber-50/40 to-transparent' : 'border-slate-200 hover:border-blue-300' }}"
                                             @mouseenter="showCard = true" 
                                             @mouseleave="showCard = false">
                                            
                                            <div class="flex items-center gap-4">
                                                <div class="w-9 h-9 flex items-center justify-center bg-slate-100 text-slate-600 font-black text-xs rounded-lg border border-slate-200 shrink-0">
                                                    {{ $item['qtd'] }}x
                                                </div>
                                                
                                                <div class="flex items-center gap-3 flex-wrap">
                                                    <p class="text-sm font-bold {{ $item['is_foil'] ? 'text-amber-900' : 'text-slate-900' }} tracking-tight">
                                                        {{ $item['nome'] }}
                                                    </p>
                                                    
                                                    <div class="h-4 w-[1px] bg-slate-200 mx-1"></div>
                                                    
                                                    <div class="flex items-center gap-2 text-[10px] font-semibold text-slate-500">
                                                        <span>{{ $item['edicao'] }}</span>
                                                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                        <span class="text-blue-500 font-bold uppercase">{{ $item['linguagem'] }}</span>
                                                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                        <span class="uppercase">{{ $item['condicao'] }}</span>
                                                        
                                                        {{-- LÓGICA DE EXTRAS COM CORES EXATAS DA LOJA --}}
                                                        @foreach($item['extras'] as $extra)
                                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                                            
                                                            @if($extra['key'] === 'foil')
                                                                <span class="text-red-600 font-black uppercase flex items-center gap-1">
                                                                    <i class="ph-fill ph-sparkle text-[12px]"></i> {{ $extra['label'] }}
                                                                </span>
                                                            @elseif($extra['key'] === 'foil_etched' || $extra['key'] === 'etched')
                                                                <span class="text-orange-500 font-black uppercase flex items-center gap-1">
                                                                    <i class="ph-fill ph-sparkle text-[12px]"></i> {{ $extra['label'] }}
                                                                </span>
                                                            @else
                                                                <span class="text-slate-600 font-bold uppercase">{{ $extra['label'] }}</span>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="text-sm font-black text-emerald-600 ml-auto pl-6 shrink-0">
                                                R$ {{ number_format($item['preco'], 2, ',', '.') }}
                                            </div>
                                        </div>

                                        {{-- HOVER DA IMAGEM COM ANIMAÇÃO "SURGINDO" E BRILHO FOIL DA LOJA --}}
                                        @if(!empty($item['foto']))
                                            <template x-teleport="body">
                                                <div x-show="showCard" 
                                                     x-transition:enter="transition-all ease-out duration-300"
                                                     x-transition:enter-start="opacity-0 translate-y-8 scale-90"
                                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                                     x-transition:leave="transition-all ease-in duration-200"
                                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                                     x-transition:leave-end="opacity-0 translate-y-8 scale-90"
                                                     class="fixed z-[9999] pointer-events-none"
                                                     :style="`top: ${mouseY - 260}px; left: ${mouseX + 20}px;`">
                                                    
                                                    {{-- O CONTAINER APLICA O EFEITO FOIL SE A CARTA FOR FOIL --}}
                                                    <div class="relative w-48 shadow-2xl rounded-xl border-4 bg-white {{ $item['is_foil'] ? 'efeito-foil-grande border-yellow-400 ring-2 ring-yellow-400' : 'border-white overflow-hidden' }}">
                                                        
                                                        <img src="{{ $item['foto'] }}" class="w-full h-auto block relative z-10 rounded-lg">
                                                        
                                                    </div>
                                                </div>
                                            </template>
                                        @endif
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
</div>