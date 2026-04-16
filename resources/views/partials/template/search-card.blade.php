{{-- CSS DO EFEITO FOIL --}}
@once
<style>
    .efeito-foil-suave {
        position: relative;
        overflow: hidden;
    }
    .efeito-foil-suave::after {
        content: '';
        position: absolute;
        top: 0; left: -100%; width: 50%; height: 100%;
        background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
        transform: skewX(-25deg);
        animation: brilho-foil 4s infinite;
        pointer-events: none;
        mix-blend-mode: overlay;
    }
    @keyframes brilho-foil {
        0%   { left: -100%; }
        20%  { left: 200%;  }
        100% { left: 200%;  }
    }
</style>
@endonce

@php
    $isEtched = $item['is_etched'] ?? false;
    $isFoil   = ($item['is_foil'] ?? false) && !$isEtched;
    $desconto = $item['desconto'] ?? 0;
@endphp

{{-- ESTADO 1: Fantasma --}}
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
            <div class="flex items-center justify-between pt-2 border-t border-gray-200/50 dark:border-slate-700/50">
                <span class="text-[10px] font-bold opacity-40" style="color: var(--cor-texto-principal);">0 un.</span>
                <div class="flex flex-col items-end">
                    <span class="text-[9px] uppercase font-bold opacity-30" style="color: var(--cor-texto-principal);">Sem Preço</span>
                    <span class="text-base font-black tracking-tight leading-none opacity-40" style="color: var(--cor-texto-principal);">R$ --</span>
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

            {{-- BADGES: desconto + foil + etched --}}
            <div class="absolute top-2 left-0 z-[60] flex flex-col gap-1 transition-opacity duration-300"
                 :class="hover ? 'opacity-0' : 'opacity-100'">
                @if($desconto > 0)
                    <span class="bg-red-600 text-white text-[10px] font-black px-2 py-0.5 rounded-r shadow-md uppercase tracking-tighter">
                        -{{ number_format($desconto, 0) }}%
                    </span>
                @endif
                @if($isFoil)
                    <span class="bg-gradient-to-r from-amber-200 to-yellow-400 text-yellow-900 text-[9px] font-black px-2 py-0.5 rounded-r shadow-sm">FOIL</span>
                @endif
                @if($isEtched)
                    <span class="bg-gradient-to-r from-amber-500 to-orange-700 text-white text-[9px] font-black px-2 py-0.5 rounded-r shadow-sm">FOIL ETCHED</span>
                @endif
            </div>

            {{-- WRAPPER DA IMAGEM COM EFEITO FOIL --}}
            <div class="w-full h-auto aspect-[2.5/3.5] rounded-lg shadow-[0_2px_8px_rgba(0,0,0,0.15)] transition-all duration-500 transform relative z-50 bg-gray-100 dark:bg-gray-800 {{ $isFoil || $isEtched ? 'efeito-foil-suave border border-yellow-400/50' : '' }}"
                 :class="hover ? 'scale-[2] shadow-2xl ring-2 ring-black/10' : ''">
                <img src="{{ $item['imagem_final'] }}"
                     alt="{{ $item['nome_localizado'] }}"
                     class="w-full h-full object-cover rounded-lg"
                     onerror="this.onerror=null; this.src='https://placehold.co/250x350/eeeeee/999999?text=Erro+na+Foto';">
            </div>
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
            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-slate-700">
                <span class="text-[10px] font-bold opacity-60" style="color: var(--cor-texto-principal);">
                    {{ $item['total_estoque'] }} un.
                </span>
                <div class="flex flex-col items-end">
                    <span class="text-[9px] uppercase font-bold opacity-50" style="color: var(--cor-texto-principal);">A partir de</span>
                    @if($desconto > 0)
                        <span class="text-[10px] text-gray-400 line-through font-bold">
                            R$ {{ number_format($item['menor_preco'], 2, ',', '.') }}
                        </span>
                        <span class="text-base font-black tracking-tight leading-none" style="color: var(--cor-1);">
                            R$ {{ number_format($item['preco_final'], 2, ',', '.') }}
                        </span>
                    @else
                        <span class="text-base font-black tracking-tight leading-none" style="color: var(--cor-1);">
                            R$ {{ number_format($item['menor_preco'], 2, ',', '.') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </a>

{{-- ESTADO 3: Esgotado --}}
@elseif($item['status'] === 'out_of_stock')
    <a href="{{ $item['url'] }}"
       x-data="{ hover: false }"
       @mouseenter="hover = true"
       @mouseleave="hover = false"
       class="flex flex-col bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 transition-all duration-300 relative opacity-70 grayscale-[30%] hover:grayscale-0"
       :class="hover ? 'z-[100] shadow-xl border-gray-400 dark:border-gray-500' : 'z-10'">

        <div class="absolute inset-x-0 top-1/3 flex justify-center z-[60] transition-opacity duration-300"
             :class="hover ? 'opacity-0' : 'opacity-100'">
            <span class="bg-red-600 text-white text-[10px] font-black uppercase px-3 py-1 rounded-full shadow-lg border border-red-800">Esgotado</span>
        </div>

        <div class="relative p-3 pb-0 overflow-visible opacity-80 transition-opacity duration-300"
             :class="hover ? 'opacity-100' : ''">

            {{-- BADGES no esgotado também --}}
            <div class="absolute top-2 left-0 z-[60] flex flex-col gap-1 transition-opacity duration-300"
                 :class="hover ? 'opacity-0' : 'opacity-100'">
                @if($isFoil)
                    <span class="bg-gradient-to-r from-amber-200 to-yellow-400 text-yellow-900 text-[9px] font-black px-2 py-0.5 rounded-r shadow-sm">FOIL</span>
                @endif
                @if($isEtched)
                    <span class="bg-gradient-to-r from-amber-500 to-orange-700 text-white text-[9px] font-black px-2 py-0.5 rounded-r shadow-sm">FOIL ETCHED</span>
                @endif
            </div>

            {{-- WRAPPER DA IMAGEM COM EFEITO FOIL --}}
            <div class="w-full h-auto aspect-[2.5/3.5] rounded-lg shadow-sm transition-all duration-500 transform relative z-50 bg-gray-100 dark:bg-gray-800 {{ $isFoil || $isEtched ? 'efeito-foil-suave border border-yellow-400/50' : '' }}"
                 :class="hover ? 'scale-[1.7] shadow-xl ring-1 ring-black/10' : ''">
                <img src="{{ $item['imagem_final'] }}"
                     alt="{{ $item['nome_localizado'] }}"
                     class="w-full h-full object-cover rounded-lg"
                     onerror="this.onerror=null; this.src='https://placehold.co/250x350/eeeeee/999999?text=Erro+na+Foto';">
            </div>
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
            <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-slate-700">
                <span class="text-[10px] font-bold opacity-50" style="color: var(--cor-texto-principal);">0 un.</span>
                <div class="flex flex-col items-end">
                    <span class="text-[9px] uppercase font-bold opacity-50" style="color: var(--cor-texto-principal);">Último preço</span>
                    <span class="text-base font-black tracking-tight leading-none" style="color: var(--cor-1);">
                        R$ {{ number_format($item['ultimo_preco'], 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </a>
@endif