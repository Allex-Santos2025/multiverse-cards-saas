{{-- ESTADO 1: Fantasma (Nunca teve estoque, sem preço) --}}
@if($item['status'] === 'ghost')
    <a href="{{ $item['url'] }}"
       x-data="{ hover: false }"
       @mouseenter="hover = true"
       @mouseleave="hover = false"
       class="flex flex-col rounded-xl border transition-all duration-300 relative opacity-60 grayscale-[80%] hover:grayscale-[50%] bg-black/5 dark:bg-white/5 border-gray-200/50 dark:border-slate-700/50 cursor-default"
       :class="hover ? 'z-[50] shadow-md' : 'z-10'">

        <div class="relative p-3 pb-0 overflow-visible">
            <img src="{{ $item['imagem_final'] }}"
                 alt="{{ $item['nome_localizado'] }}"
                 class="w-full h-auto aspect-[2.5/3.5] object-cover rounded-lg shadow-sm transition-all duration-500 relative z-50"
                 onerror="this.onerror=null; this.src='https://placehold.co/250x350/eeeeee/999999?text=Erro+na+Foto';">
        </div>

        <div class="p-3 flex flex-col flex-grow justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold leading-tight line-clamp-2 text-inherit opacity-80"
                    style="color: var(--cor-texto-principal);"
                    title="{{ $item['nome_localizado'] }}">
                    {{ $item['nome_localizado'] }}
                </h3>
                <p class="text-[9px] uppercase font-semibold opacity-50 mt-1"
                   style="color: var(--cor-texto-principal);">
                    {{ $item['name'] ?? '---' }}
                    @if(!empty($item['set_name']))
                        &bull; {{ $item['set_name'] }}
                    @endif
                </p>
            </div>
            
            {{-- Rodapé Padronizado --}}
            <div class="flex items-center justify-between pt-2 border-t border-gray-200/50 dark:border-slate-700/50">
                <span class="text-[10px] font-bold opacity-40" style="color: var(--cor-texto-principal);">
                    0 un.
                </span>
                <div class="flex flex-col items-end">
                    <span class="text-[9px] uppercase font-bold opacity-30" style="color: var(--cor-texto-principal);">
                        Sem Preço
                    </span>
                    <span class="text-base font-black tracking-tight leading-none opacity-40" style="color: var(--cor-texto-principal);">
                        R$ --
                    </span>
                </div>
            </div>
        </div>
    </a>

{{-- ESTADO 2: Com Estoque --}}
@elseif($item['status'] === 'available')
    <a href="{{ $item['url'] }}"
       x-data="{ hover: false }"
       @mouseenter="hover = true"
       @mouseleave="hover = false"
       class="flex flex-col bg-white dark:bg-slate-800 rounded-xl shadow-sm border transition-all duration-300 relative cursor-pointer"
       :class="hover ? 'z-[150] shadow-2xl' : 'z-10 border-gray-200 dark:border-slate-700'"
       :style="hover ? 'border-color: var(--cor-cta);' : 'border-color: var(--cor-3, #e5e7eb);'">

        <div class="relative p-3 pb-0 overflow-visible">
            <img src="{{ $item['imagem_final'] }}"
                 alt="{{ $item['nome_localizado'] }}"
                 class="w-full h-auto aspect-[2.5/3.5] object-cover rounded-lg shadow-[0_2px_8px_rgba(0,0,0,0.15)] transition-all duration-500 transform relative z-50 bg-gray-100 dark:bg-gray-800"
                 :class="hover ? 'scale-[2] shadow-2xl ring-2 ring-black/10' : ''"
                 onerror="this.onerror=null; this.src='https://placehold.co/250x350/eeeeee/999999?text=Erro+na+Foto';">
        </div>

        <div class="p-3 flex flex-col flex-grow justify-between gap-3 transition-opacity duration-300 relative z-[40] bg-white dark:bg-slate-800 rounded-b-xl"
             :class="hover ? 'opacity-0' : 'opacity-100'">
            <div>
                <h3 class="text-sm font-bold leading-tight line-clamp-2"
                    style="color: var(--cor-texto-principal);"
                    title="{{ $item['nome_localizado'] }}">
                    {{ $item['nome_localizado'] }}
                </h3>
                <p class="text-[9px] uppercase font-semibold mt-1 opacity-70"
                   style="color: var(--cor-texto-principal);">
                    {{ $item['name'] ?? '---' }}
                    @if(!empty($item['set_name']))
                        &bull; {{ $item['set_name'] }}
                    @endif
                </p>
            </div>
            
            {{-- Rodapé Padronizado --}}
            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-slate-700">
                <span class="text-[10px] font-bold opacity-60" style="color: var(--cor-texto-principal);">
                    {{ $item['total_estoque'] }} un.
                </span>
                <div class="flex flex-col items-end">
                    <span class="text-[9px] uppercase font-bold opacity-50" style="color: var(--cor-texto-principal);">
                        A partir de
                    </span>
                    <span class="text-base font-black tracking-tight leading-none" style="color: var(--cor-1);">
                        R$ {{ number_format($item['menor_preco'], 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </a>

{{-- ESTADO 3: Esgotado (Já teve estoque, tem último preço) --}}
@elseif($item['status'] === 'out_of_stock')
    <a href="{{ $item['url'] }}"
       x-data="{ hover: false }"
       @mouseenter="hover = true"
       @mouseleave="hover = false"
       class="flex flex-col bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 transition-all duration-300 relative opacity-70 grayscale-[30%] hover:grayscale-0"
       :class="hover ? 'z-[100] shadow-xl border-gray-400 dark:border-gray-500' : 'z-10'">

        <div class="absolute inset-x-0 top-1/3 flex justify-center z-[60] transition-opacity duration-300"
             :class="hover ? 'opacity-0' : 'opacity-100'">
            <span class="bg-red-600 text-white text-[10px] font-black uppercase px-3 py-1 rounded-full shadow-lg border border-red-800">
                Esgotado
            </span>
        </div>

        <div class="relative p-3 pb-0 overflow-visible opacity-80 transition-opacity duration-300"
             :class="hover ? 'opacity-100' : ''">
            <img src="{{ $item['imagem_final'] }}"
                 alt="{{ $item['nome_localizado'] }}"
                 class="w-full h-auto aspect-[2.5/3.5] object-cover rounded-lg shadow-sm transition-all duration-500 transform relative z-50 bg-gray-100 dark:bg-gray-800"
                 :class="hover ? 'scale-[1.7] shadow-xl ring-1 ring-black/10' : ''"
                 onerror="this.onerror=null; this.src='https://placehold.co/250x350/eeeeee/999999?text=Erro+na+Foto';">
        </div>

        <div class="p-3 flex flex-col flex-grow justify-between gap-3 transition-opacity duration-300 bg-white dark:bg-slate-800 relative z-[40] rounded-b-xl"
             :class="hover ? 'opacity-0' : 'opacity-100'">
            <div>
                <h3 class="text-sm font-bold leading-tight line-clamp-2"
                    style="color: var(--cor-texto-principal);"
                    title="{{ $item['nome_localizado'] }}">
                    {{ $item['nome_localizado'] }}
                </h3>
                <p class="text-[9px] uppercase font-semibold mt-1 opacity-70"
                   style="color: var(--cor-texto-principal);">
                    {{ $item['name'] ?? '---' }}
                    @if(!empty($item['set_name']))
                        &bull; {{ $item['set_name'] }}
                    @endif
                </p>
            </div>
            
            {{-- Rodapé Padronizado --}}
            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-slate-700">
                <span class="text-[10px] font-bold opacity-50" style="color: var(--cor-texto-principal);">
                    0 un.
                </span>
                <div class="flex flex-col items-end">
                    <span class="text-[9px] uppercase font-bold opacity-50" style="color: var(--cor-texto-principal);">
                        Último preço
                    </span>
                    <span class="text-base font-black tracking-tight leading-none" style="color: var(--cor-1);">
                        R$ {{ number_format($item['ultimo_preco'], 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </a>
@endif