<div class="relative">
    {{-- A mágica do Livewire 3: Isso força a tela a atualizar ao clicar em Adicionar --}}
    <span style="display: none;">{{ $cartTrigger }}</span>

    <style>
        .cart-scroll-invisible::-webkit-scrollbar { display: none; }
        .cart-scroll-invisible { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    <div x-data="{ open: false, hoverImg: null, mouseX: 0, mouseY: 0 }" 
         @mousemove="mouseX = $event.clientX; mouseY = $event.clientY"
         class="relative" 
         @mouseenter="open = true" 
         @mouseleave="open = false; hoverImg = null">
        
        <div class="flex flex-col items-center hover:opacity-75 transition-opacity cursor-pointer">
            <div class="relative">
                <i class="ph ph-shopping-cart text-[28px]"></i>
                
                @if($totalQuantity > 0)
                    <span class="absolute -top-1.5 -right-2 w-5 h-5 flex items-center justify-center text-[10px] font-black rounded-full shadow-sm text-white" style="background-color: var(--cor-cta);">
                        {{ $totalQuantity > 99 ? '99+' : $totalQuantity }}
                    </span>
                @endif
            </div>
            <span class="text-xs font-medium mt-1 uppercase">CARRINHO</span>
        </div>

        <div x-show="open" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-1"
             class="absolute right-0 mt-2 w-80 bg-white dark:bg-slate-800 rounded-xl shadow-[0_20px_50px_rgba(0,0,0,0.3)] border border-gray-100 dark:border-slate-700 z-[9999]"
             style="display: none;"
             x-cloak>
            
            <div class="p-4 text-center rounded-t-xl" style="background-color: var(--cor-1);">
                <h3 class="text-xs font-black text-white uppercase tracking-widest">Meu Carrinho</h3>
            </div>

            <div class="max-h-[320px] overflow-y-auto p-4 flex flex-col gap-1 cart-scroll-invisible">
                @forelse($cartItems as $item)
                    @php
                        $stock = $item->stockItem;
                        $print = $stock ? $stock->catalogPrint : null;
                        
                        $condicao = strtoupper($stock->condition ?? '');
                        $idioma = strtoupper($stock->language ?? $print->language_code ?? '');
                        $codigoEdicao = strtoupper($print->set->code ?? 'N/A');
                    @endphp

                    <div class="flex justify-between items-start border-b border-gray-50 dark:border-slate-700/50 pb-3 last:border-0 last:pb-0"
                         @mouseenter="hoverImg = '{{ $item->imagem_final }}'"
                         @mouseleave="hoverImg = null">
                        
                        <div class="flex-1 min-w-0 pr-4 cursor-default">
                            <p class="text-[12px] leading-tight mb-1">
                                <span class="font-black text-gray-900 dark:text-white" style="color: var(--cor-1);">{{ $item->quantity }}x</span>
                                <span class="font-bold text-gray-800 dark:text-gray-200 ml-1">{{ $item->nome_localizado }}</span>
                            </p>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                                {{ $condicao }} • {{ $idioma }} • {{ $codigoEdicao }}
                            </p>
                        </div>
                        <div class="text-[13px] font-black text-gray-900 dark:text-white whitespace-nowrap">
                            R$ {{ number_format($item->price, 2, ',', '.') }}
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center flex flex-col items-center justify-center gap-3">
                        <i class="ph ph-shopping-cart text-4xl text-gray-200 dark:text-gray-700"></i>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Carrinho Vazio</p>
                    </div>
                @endforelse
            </div>

            @if($totalQuantity > 0)
                <div class="p-5 border-t border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/20 rounded-b-xl">
                    <div class="flex justify-between items-center mb-5">
                        <span class="text-[11px] font-black text-gray-400 uppercase tracking-widest">Subtotal</span>
                        <span class="text-lg font-black text-gray-900 dark:text-white">R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex flex-col gap-2">
                        <a href="#" class="block w-full py-3 text-center text-[11px] font-black uppercase tracking-widest text-white rounded-lg transition hover:opacity-95 shadow-md" style="background-color: var(--cor-cta);">
                            Finalizar Pedido
                        </a>
                        <a href="#" class="text-center text-[10px] font-bold text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition uppercase tracking-tighter py-1">
                            Ver detalhes do carrinho
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <template x-if="hoverImg">
            <img :src="hoverImg" 
                 class="fixed z-[100000] w-40 rounded-xl shadow-2xl pointer-events-none transition-opacity duration-75" 
                 :style="'top: ' + (mouseY + 15) + 'px; left: ' + (mouseX - 170) + 'px;'"
                 x-cloak>
        </template>
    </div>
</div>