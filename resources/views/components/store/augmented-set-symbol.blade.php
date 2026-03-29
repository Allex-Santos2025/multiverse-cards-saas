@props(['set'])

@php
    $code = strtoupper($set->code);
    $safeCode = strtolower($code);
    $rarityLower = strtolower($set->rarity ?? 'common');

    // 1. Lógica de Badges (TK, P)
    $hasBadge = false;
    $badgeText = '';
    $badgeColors = 'bg-white text-gray-900 border-gray-100';

    if ($set->game_id == 1) {
        if ($set->set_type == 'token' || str_starts_with($code, 'T')) { 
            $hasBadge = true; $badgeText = 'TK'; 
            if (str_starts_with($code, 'TT')) $code = substr($code, 1);
        }
        elseif ($set->set_type == 'promo' || str_starts_with($code, 'P')) { 
            $hasBadge = true; $badgeText = 'P'; $badgeColors = 'bg-white text-purple-900 border-gray-100'; 
        }
    }

    // 2. A Tartaruga é Intocável (Whitelist)
    $isTMT = in_array($code, ['TMT', 'TTMT']);

    // 3. Engine de SVG Nativos (Baseado no seu código)
    $svgContent = null;
    if (!$isTMT) {
        $localPath = public_path("card_images/magic/{$safeCode}/{$safeCode}.svg");
        
        if (file_exists($localPath)) {
            $rawContent = file_get_contents($localPath);
            // Limpeza XML para não quebrar Livewire
            $cleanContent = preg_replace('/<\?xml.*?\?>/s', '', $rawContent);
            $cleanContent = preg_replace('/<!DOCTYPE.*?>/s', '', $cleanContent);
            $svgContent = preg_replace('/id=["\'].*?["\']/', '', $cleanContent);
        }
    }

    // 4. Whitelist de Inversão (Onde o Comum não é Preto, é Branco com linha Preta)
    $invertedSets = ['DOM', 'ALL', 'AL', 'ISD'];
    $isInverted = in_array($code, $invertedSets);

    // 5. Paleta de Cores Refinada (Oficial Keyrune)
    $rarityColor = match($rarityLower) {
        'uncommon' => '#6C848C',
        'rare'     => '#C5B38A',
        'mythic', 'special', 'bonus' => '#BF4427',
        default    => '#1A1718', // Usado para a TMT
    };

    $fillColor = match($rarityLower) {
        'uncommon' => '#6C848C',
        'rare'     => '#C5B38A',
        'mythic', 'special', 'bonus' => '#BF4427',
        default    => $isInverted ? '#FFFFFF' : '#1A1718', // Inverte o preenchimento se for Alisios/Dominária
    };

    $strokeColor = match($rarityLower) {
        'uncommon', 'rare', 'mythic', 'special', 'bonus' => '#000000',
        default    => $isInverted ? '#1A1718' : '#FFFFFF', // Inverte o contorno se for Alisios/Dominária
    };

    $strokeWidth = $rarityLower == 'common' ? '1.5px' : '2.5px';
@endphp

<div class="relative inline-flex items-center justify-center group w-9 h-9 shrink-0">
    <div class="w-full h-full flex items-center justify-center transition-transform duration-200 group-hover:scale-110">
        
        @if($isTMT)
            {{-- BLOCO 1: TARTARUGA (CÓDIGO COMPLETO, SEM ABREVIAÇÕES) --}}
            <svg viewBox="0 0 500 500" class="w-7 h-7 drop-shadow-sm" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2; stroke:black; stroke-width:5px;">
                <g transform="matrix(1.01318,0,0,1.01318,0,-0.79)">
                    <path d="M153.389,133.509C245.936,201.593 340.381,202.848 436.84,133.216C363.632,36.063 229.877,35.191 153.389,133.509Z" fill="#FFFFFF"/>
                    <path d="M111.429,275.086C143.872,292.673 180.049,300.613 220.004,298.795L205.215,316.87C266.174,279.215 326.181,279.642 385.262,317.105L370.004,298.795C407.798,299.726 444.174,293.326 478.213,274.851C516.183,345.363 482.965,406.029 383.384,413.584C402.77,402.558 416.581,386.19 420.942,360.767C369.572,455.091 219.038,456.883 170.004,360.062C171.604,382.131 182.957,400.357 205.215,414.288C114.251,408.295 68.961,348.172 111.429,275.086Z" fill="#FFFFFF"/>
                    <path d="M146.999,131.893C127.662,79.303 90.908,63.978 44.651,68.983C115.447,119.937 48.534,163.58 111.429,204.898C95.607,201.524 82.061,194.357 75.637,175.321C40.958,181.293 16.526,198.926 -0,225.555C28.366,255.209 62.53,267.193 104.041,256.784C104.904,237.334 109.515,219.966 121.647,206.776C124.062,176.987 128.89,149.128 146.999,131.893Z" fill="{{ $rarityColor }}"/>
                    <path d="M144.88,145.833C241.156,216.195 340.857,217.295 444.175,145.246C454.308,160.158 459.375,181.978 460.901,208.627C472.603,224.055 478.248,241.907 478.213,262.03C436.472,278.889 393.879,287.23 350.572,288.439C314.383,271.029 276.237,270.833 236.136,287.852C186.115,289.275 147.365,278.213 111.429,260.857C112.69,240.849 118.384,223.059 129.035,207.746C130.431,184.389 135.659,163.714 144.88,145.833ZM194.175,201.291C183.904,213.186 179.189,227.217 178.917,242.958C201.707,255.701 225.981,259.789 251.848,254.584C236.394,231.415 217.321,213.434 194.175,201.291ZM336.46,254.488C349.824,234.391 369.152,216.516 395.593,201.291C404.2,214.733 409.389,228.289 410.304,241.988C385.546,256.722 360.961,258.73 336.46,254.488Z" fill="{{ $rarityColor }}"/>
                </g>
            </svg>

        @elseif($svgContent)
            {{-- BLOCO 2: ENGINE NATIVA DA VERSUS TCG (O Pulo do Gato para Dominária/Resto) --}}
            <style>
                .render-{{ $safeCode }}-{{ $rarityLower }} svg { 
                    overflow: visible; width: 100%; height: 100%; 
                }
                .render-{{ $safeCode }}-{{ $rarityLower }} svg path { 
                    stroke-linejoin: round; 
                    stroke-linecap: round; 
                    paint-order: stroke fill;
                    vector-effect: non-scaling-stroke; 
                    fill: {{ $fillColor }} !important; 
                    stroke: {{ $strokeColor }} !important; 
                    stroke-width: {{ $strokeWidth }}; 
                }
            </style>

            <div class="render-{{ $safeCode }}-{{ $rarityLower }} w-7 h-7 flex items-center justify-center drop-shadow-sm" title="{{ ucfirst($rarityLower) }}">
                {!! $svgContent !!}
            </div>

        @else
            {{-- BLOCO 3: FALLBACK (Se a imagem faltar na sua pasta local) --}}
            <span class="text-[9px] font-bold border border-gray-400 px-1 rounded uppercase opacity-60 flex items-center justify-center text-gray-500 w-7 h-7">
                {{ $code ? strtoupper(substr($code, 0, 3)) : '?' }}
            </span>
        @endif

    </div>
    
    {{-- Badge TK/P --}}
    @if($hasBadge)
        <span class="absolute -top-1.5 -right-1.5 flex items-center justify-center {{ $badgeColors }} text-[9px] font-black px-1 min-w-[15px] h-[15px] rounded-full shadow-md border-2 z-10 leading-none">
            {{ $badgeText }}
        </span>
    @endif
</div>