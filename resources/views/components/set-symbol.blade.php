@props([
    'path' => null,
    'code' => null, 
    'rarity' => 'common', 
    'size' => 'w-6 h-6'   
])

@php
    $svgContent = null;
    $rarityLower = strtolower($rarity);

    // 1. Busca o arquivo e LIMPA o XML
    if ($code) {
        $safeCode = strtolower($code);
        $localPath = public_path("card_images/magic/{$safeCode}/{$safeCode}.svg");
        
        if (file_exists($localPath)) {
            $rawContent = file_get_contents($localPath);

            // Remove cabeçalhos XML e DOCTYPE que quebram o Livewire
            $cleanContent = preg_replace('/<\?xml.*?\?>/s', '', $rawContent);
            $cleanContent = preg_replace('/<!DOCTYPE.*?>/s', '', $cleanContent);
            
            // Remove IDs duplicados para evitar conflito de JS
            $svgContent = preg_replace('/id=["\'].*?["\']/', '', $cleanContent);
        }
    }

    // 2. Define a classe CSS
    $cssClass = match($rarityLower) {
        'uncommon'             => 'rarity-uncommon',
        'rare'                 => 'rarity-rare',
        'mythic', 'mythic rare'=> 'rarity-mythic',
        'special', 'bonus'     => 'rarity-mythic',
        default                => 'rarity-common',
    };
@endphp

@once
    <style>
        /* BASE */
        .set-symbol-base svg { 
            overflow: visible; 
            width: 100%; 
            height: 100%; 
        }
        
        .set-symbol-base svg path { 
            stroke-linejoin: round; 
            stroke-linecap: round; 
            paint-order: stroke fill;
            
            /* Mantém a borda fina independente do zoom */
            vector-effect: non-scaling-stroke; 
        }
        
        /* --- COMUM --- */
        .rarity-common svg path { 
            fill: #000000 !important; 
            stroke: #ffffff !important; 
            stroke-width: 1.5px; 
        }
        
        /* --- ESPECIAIS --- */
        
        /* INCOMUM */
        .rarity-uncommon svg path { 
            fill: #708090 !important; 
            stroke: #000000 !important; 
            stroke-width: 2.5px; 
        }
        
        /* RARO */
        .rarity-rare svg path { 
            fill: #d4af37 !important; 
            stroke: #000000 !important; 
            stroke-width: 2.5px;
        }
        
        /* MÍTICO */
        .rarity-mythic svg path { 
            fill: #ff4500 !important; 
            stroke: #000000 !important; 
            stroke-width: 2.5px; 
        }
    </style>
@endonce

@if($svgContent)
    <div {{ $attributes->merge(['class' => "set-symbol-base $size $cssClass inline-flex items-center justify-center"]) }} 
         title="{{ ucfirst($rarityLower) }}">
        {!! $svgContent !!}
    </div>
@else
    {{-- Fallback --}}
    <span class="text-[9px] font-bold border border-gray-400 px-1 rounded uppercase opacity-60 {{ $size }} flex items-center justify-center text-gray-500">
        {{ $code ? strtoupper(substr($code, 0, 3)) : '?' }}
    </span>
@endif