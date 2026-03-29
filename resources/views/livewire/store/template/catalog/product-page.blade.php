<div class="min-h-screen bg-gray-50 text-gray-800 flex flex-col">
    
    {{-- Barra de Navegação (Breadcrumb) --}}
    <div class="py-2" style="background-color: var(--cor-secundaria); color: var(--cor-texto-secundaria);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="text-xs font-bold flex gap-2 items-center">
                <a href="{{ route('store.view', ['slug' => $loja->url_slug]) }}" class="hover:underline opacity-90">Home</a>
                <span class="opacity-50">></span>
                <a href="#" class="hover:underline opacity-90">{{ $concept->game->name ?? 'Cards' }}</a>
                <span class="opacity-50">></span>
                <span>{{ $concept->name }}</span>
            </nav>
        </div>
    </div>

    {{-- Container Principal do Conteúdo --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
            
            {{-- Coluna Esquerda: O "CORPO" DA CARTA (Tornada mais compacta) --}}
            <div class="lg:col-span-4 flex flex-col gap-4 sticky top-20 self-start z-10">
                
                {{-- 1. Imagem Base da Carta --}}
                <div class="bg-white border border-gray-200 rounded-xl p-3 flex justify-center items-center shadow-sm relative">
                    <img id="main-card-image" src="{{ $activeImage }}" class="w-full h-auto rounded-xl shadow-2xl">
                </div>

                {{-- 2. CAIXA DE PREÇOS (Movida para cá e ajustada para caber) --}}
                <div class="flex justify-between items-center bg-white p-4 rounded-xl border border-gray-200 shadow-sm w-full">
                    {{-- Menor Preço (Loja) --}}
                    <div class="flex flex-col items-center text-center">
                        <span class="text-[9px] uppercase font-bold text-gray-500 mb-0.5">Menor (Loja)</span>
                        <span class="font-bold text-base text-emerald-600 tracking-tight">
                            {{ $priceStats['min'] > 0 ? 'R$ ' . number_format($priceStats['min'], 2, ',', '.') : '--' }}
                        </span>
                    </div>

                    <div class="w-px h-8 bg-gray-200"></div>

                    {{-- Preço Médio (Mercado) --}}
                    <div class="flex flex-col items-center text-center">
                        <span class="text-[9px] uppercase font-bold text-gray-500 mb-0.5">Médio (Mkt)</span>
                        <span class="font-bold text-base text-gray-800 tracking-tight">
                            {{ $priceStats['avg'] > 0 ? 'R$ ' . number_format($priceStats['avg'], 2, ',', '.') : '--' }}
                        </span>
                    </div>

                    <div class="w-px h-8 bg-gray-200"></div>

                    {{-- Maior Preço (Loja) --}}
                    <div class="flex flex-col items-center text-center">
                        <span class="text-[9px] uppercase font-bold text-gray-500 mb-0.5">Maior (Loja)</span>
                        <span class="font-bold text-base text-rose-600 tracking-tight">
                            {{ $priceStats['max'] > 0 ? 'R$ ' . number_format($priceStats['max'], 2, ',', '.') : '--' }}
                        </span>
                    </div>
                </div>

                {{-- 3. Bloco de Regras / Texto --}}
            @if($gameDetails)
                <div class="bg-white border-l-4 border-gray-300 rounded-r-lg p-3 text-xs leading-relaxed shadow-sm">
                    @php
                        // Pega a tradução (printed_text). Se não tiver, usa a regra em inglês (oracle_text)
                        $textoFinal = $gameDetails->printed_text ?? $concept->specific->oracle_text ?? '';

                        $oracleHtml = preg_replace_callback('/\{([^}]+)\}/', function($matches) {
                            $val = strtolower(str_replace('/', '', $matches[1]));
                            return '<i class="ms ms-' . $val . ' ms-cost text-xs" style="filter: drop-shadow(-1px 1px 0px rgba(0,0,0,0.6));"></i>';
                        }, $textoFinal);
                    @endphp
                    
                    <p class="mb-2 text-gray-800 font-medium">{!! nl2br($oracleHtml) !!}</p>
                    
                    {{-- Flavor Text (Texto ilustrativo) --}}
                    @if(!empty($gameDetails->flavor_text))
                        <div class="border-t border-gray-100 mt-2 pt-2">
                            <p class="text-gray-500 italic text-[10px] leading-snug">
                                "{{ $gameDetails->flavor_text }}"
                            </p>
                        </div>
                    @endif
                </div>
            @endif

                {{-- 4. Bloco de Atributos Técnicos (Encolhido) --}}
                <div class="bg-white border border-gray-200 rounded-lg p-3 text-xs shadow-sm">
                    <div class="grid grid-cols-2 gap-y-3 gap-x-3">
                        @if($gameDetails)
                            
                            {{-- Custo de Mana (Mecânica = Concept) --}}
                            @if(isset($concept->specific->mana_cost))
                            <div>
                                <span class="block text-[10px] font-bold uppercase mb-0.5 text-gray-500">Custo de Mana</span> 
                                <span class="flex items-center gap-0.5 font-bold text-gray-900">
                                    @php
                                        $manaCostHtml = preg_replace_callback('/\{([^}]+)\}/', function($matches) {
                                            $val = strtolower(str_replace('/', '', $matches[1]));
                                            return '<i class="ms ms-' . $val . ' ms-cost text-base" style="filter: drop-shadow(-1px 1px 0px rgba(0,0,0,0.6));"></i>';
                                        }, $concept->specific->mana_cost);
                                    @endphp
                                    {!! $manaCostHtml !!}
                                </span>
                            </div>
                            @endif
                            
                            {{-- Tipo (Tradução = Print. Fallback = Concept) --}}
                            <div>
                                <span class="block text-[10px] font-bold uppercase mb-0.5 text-gray-500">Tipo</span> 
                                <span class="text-gray-900 font-medium">{{ $gameDetails->printed_type_line ?? $concept->specific->type_line ?? '--' }}</span>
                            </div>

                            {{-- Artista (Físico = Print) --}}
                            @if(isset($gameDetails->artist))
                            <div>
                                <span class="block text-[10px] font-bold uppercase mb-0.5 text-gray-500">Artista</span> 
                                <span class="text-gray-900 font-bold italic">{{ $gameDetails->artist }}</span>
                            </div>
                            @endif

                            {{-- Poder e Resistência (Mecânica = Concept) --}}
                            @if(isset($concept->specific->power) && isset($concept->specific->toughness))
                            <div>
                                <span class="block text-[10px] font-bold uppercase mb-0.5 text-gray-500">Poder / Resistência</span> 
                                <span class="text-gray-900 font-black text-sm">
                                    {{ $concept->specific->power }} / {{ $concept->specific->toughness }}
                                </span>
                            </div>
                            @endif

                            {{-- Lealdade (Mecânica = Concept) --}}
                            @if(isset($concept->specific->loyalty))
                            <div>
                                <span class="block text-[10px] font-bold uppercase mb-0.5 text-gray-500">Lealdade</span> 
                                <span class="text-gray-900 font-black text-sm">
                                    {{ $concept->specific->loyalty }}
                                </span>
                            </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- Coluna Direita: MERCADO E VARIAÇÕES --}}
            <div class="lg:col-span-8 flex flex-col">
                
                {{-- Título da Carta e Favoritar --}}
                <div class="mb-6 flex justify-between items-start">
                    <div class="mb-2 border-b border-gray-200/50 dark:border-slate-700/50 pb-4 w-full mr-4">
                        
                        {{-- Regra exata: PT no topo (se houver e for diferente), EN embaixo. Caso contrário, só EN --}}
                        @if($nomeLocalizado && $nomeLocalizado !== $concept->name)
                            <h1 class="text-3xl font-black italic uppercase tracking-tight" style="color: var(--cor-secundaria);">
                                {{ $nomeLocalizado }}
                            </h1>
                            <h2 class="text-xl font-bold uppercase opacity-60 mt-1" style="color: var(--cor-texto-principal);">
                                {{ $concept->name }}
                            </h2>
                        @else
                            <h1 class="text-3xl font-black italic uppercase tracking-tight" style="color: var(--cor-secundaria);">
                                {{ $concept->name }}
                            </h1>
                        @endif
                        
                    </div>
                    
                    <button class="text-gray-400 hover:text-red-500 transition border border-gray-200 rounded-lg p-2 bg-white shadow-sm hover:bg-gray-50 flex-shrink-0">
                        <i class="ph ph-heart text-xl"></i>
                    </button>
                </div>

                
                    
                {{-- Tabela de Estoque --}}
                <div class="flex-grow">                    
                    {{-- Cabeçalho da Tabela com Botões de Lojista (Personalizados) --}}
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-bold uppercase text-gray-800">Edições Disponíveis</h2>

                        {{-- Botões Administrativos Personalizados --}}
                        @if(auth()->check() && (auth()->id() == ($loja->user_id ?? $loja->id)))
                            <div class="flex gap-2">
                                {{-- Botão 1: Logo Versus (Sistema Versus) --}}
                                <button title="Sistema Versus" class="flex items-center justify-center w-11 h-11 bg-gray-100 border-0 rounded-lg shadow hover:shadow-md active:translate-y-px active:shadow-sm transition-all relative group overflow-hidden">
                                    {{-- Imagem do Logo Versus com De-Para --}}
                                    <img src="{{ asset('assets/LOGO btn.png') }}" 
                                        alt="Versus" 
                                        class="absolute inset-0 w-full h-full object-contain p-1.5 opacity-90 group-hover:opacity-100 transition-opacity" 
                                        onerror="this.src='{{ asset('assets/fallback-logo.png') }}';">                                   
                                </button>

                                {{-- Botão 2: Documento + Lápis (Cadastro Individual) --}}
                                <button title="Cadastro Individual" class="flex items-center justify-center w-11 h-11 bg-gray-100 border-0 rounded-lg shadow hover:shadow-md transition-all relative">
                                    <i class="ph ph-note-pencil text-xl text-gray-600"></i>
                                    <i class="ph ph-pencil-simple-fill absolute top-1 right-1 text-[24px] text-red-600"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                    <div class="overflow-x-auto bg-white rounded-xl shadow-sm border border-gray-200">
                        <table class="w-full text-sm text-left">
                            
                            {{-- CABEÇALHO NA COR TERCIÁRIA --}}
                            <thead class="uppercase text-[10px] font-bold tracking-wider border-b border-gray-200" style="background-color: var(--cor-terciaria); color: var(--cor-texto-terciaria);">
                                <tr>
                                    <th class="px-4 py-3 text-center w-16">Edição</th>
                                    <th class="px-2 py-3 text-center w-16">Idioma</th>
                                    <th class="px-4 py-3 text-center w-20">Qualid.</th>
                                    <th class="px-4 py-3 text-center w-24">Extras</th>
                                    <th class="px-4 py-3 text-center w-32">Preço</th>
                                    <th class="px-4 py-3 text-center w-24">Estoque</th>
                                    <th class="px-4 py-3 text-center w-28">Ação</th>
                                </tr>
                            </thead>
                            
                            <tbody class="divide-y divide-gray-100">
    @forelse($displayList as $item)
        @php 
            $print = $allPrints->where('id', $item['print_id'])->first();
            $stock = $item['stock_id'] ? $stockByPrint->get($print->id)->where('id', $item['stock_id'])->first() : null;
            
            // Mapeamento de 11 Idiomas Oficiais
            $lang = strtolower($print->language_code ?? 'en');
            $langNames = [
                'en'  => 'Inglês', 'pt'  => 'Português', 'ja'  => 'Japonês',
                'zhs' => 'Chinês Simplificado', 'zht' => 'Chinês Tradicional',
                'fr'  => 'Francês', 'de'  => 'Alemão', 'it'  => 'Italiano',
                'es'  => 'Espanhol', 'ru'  => 'Russo', 'ko'  => 'Coreano'
            ];
            $fullLang = $langNames[$lang] ?? strtoupper($lang);

            // Estados de Conservação (Com o seu 'M' incluso)
            $condition = strtoupper($stock->condition ?? 'NM');
            $conditionNames = [
                'M'  => 'Mint (Nova)',
                'NM' => 'Near Mint (Quase Nova)',
                'SP' => 'Slightly Played (Ligeiramente Jogada)',
                'MP' => 'Moderately Played (Jogada)',
                'HP' => 'Heavily Played (Muito Jogada)',
                'D'  => 'Damaged (Danificada)'
            ];
            $fullCondition = $conditionNames[$condition] ?? $condition;
        @endphp

        @if($stock && $stock->quantity > 0)
            {{-- ========================================================== --}}
            {{-- ESTADO 1: DISPONÍVEL --}}
            <tr class="transition cursor-pointer {{ $activePrintId == $print->id ? 'bg-blue-50' : 'hover:bg-gray-50' }}"
                wire:mouseenter="updateStats({{ $print->id }})">
                
                {{-- 1. Símbolo (Com Tooltip Corrigido) --}}
                <td class="px-4 py-4 text-center">
                    <div class="relative group flex justify-center cursor-help">
                        <x-set-symbol :set="$print->set" rarity="{{ $print->rarity }}" size="w-7 h-7" />
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-white text-gray-800 text-[10px] font-bold px-2 py-1.5 rounded shadow-md border border-gray-200 z-[100] w-max max-w-[130px] text-center leading-tight">
                            {{ $print->set->name }}
                        </div>
                    </div>
                </td>

                {{-- 2. Idioma (Bandeira) --}}
                <td class="px-2 py-4 text-center">
                    <div class="relative group flex justify-center cursor-help">
                        <img src="{{ asset('assets/flags/' . $lang . '.svg') }}" 
                            class="w-6 h-auto mx-auto shadow-sm rounded-sm border border-gray-100" 
                            onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                        
                        {{-- Tooltip Padronizado Branco --}}
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-white text-gray-800 text-[10px] font-bold px-2 py-1.5 rounded shadow-md border border-gray-200 z-[100] w-max text-center leading-tight">
                            {{ $fullLang }}
                        </div>
                    </div>
                </td>

                {{-- 3. Qualidade --}}
                <td class="px-4 py-4 text-center">
                    <div class="relative group flex justify-center cursor-help">
                        <span class="text-xs font-bold text-gray-700 uppercase">
                            {{ $condition }}
                        </span>
                        
                        {{-- Tooltip Padronizado Branco --}}
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-white text-gray-800 text-[10px] font-bold px-2 py-1.5 rounded shadow-md border border-gray-200 z-[100] w-max text-center leading-tight">
                            {{ $fullCondition }}
                        </div>
                    </div>
                </td>

                {{-- 4. Extras --}}
                <td class="px-4 py-4 text-center">
                    <div class="text-[12px] font-bold leading-tight flex flex-wrap justify-center items-center gap-1">
                        @php
                            $tags = [];
                            
                            if(is_array($stock->extras) && !empty($stock->extras)) {
                                foreach($stock->extras as $extra) {
                                    if(empty($extra)) continue;
                                    
                                    // 1. Força TUDO para minúsculo primeiro para matar qualquer caixa alta do banco
                                    $lowerExtra = strtolower($extra);
                                    
                                    if (str_contains($lowerExtra, 'etched')) {
                                        $tags[] = '<span class="text-orange-500 font-black">Foil Etched</span>';
                                    } elseif (str_contains($lowerExtra, 'foil')) {
                                        $tags[] = '<span class="text-red-600 font-black">Foil</span>';
                                    } else {
                                        // 2. Limpa os underlines usando a versão que já está 100% minúscula
                                        $cleanName = str_replace('_', ' ', $lowerExtra);
                                        // 3. ucwords agora funciona perfeitamente, subindo só a 1ª letra (ex: Assinada)
                                        $tags[] = '<span class="text-gray-600">' . ucwords($cleanName) . '</span>';
                                    }
                                }
                            }
                        @endphp

                        @if(!empty($tags))
                            {!! implode('<span class="text-gray-400">, </span>', $tags) !!}
                        @else
                            <span class="text-gray-300 font-bold">--</span>
                        @endif
                    </div>
                </td>

                {{-- 5. Preço --}}
                <td class="px-2 py-4 text-center">
                    <div class="text-sm font-black tracking-tight" style="color: var(--cor-1);">
                        R$ {{ number_format($stock->final_price, 2, ',', '.') }}
                    </div>
                </td>

                {{-- 6. Estoque --}}
                <td class="px-2 py-4 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <input type="number" value="1" min="1" max="{{ $stock->quantity }}" 
                        class="w-12 h-8 text-center font-bold text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 outline-none">
                        <span class="text-[10px] font-bold text-gray-500">
                            de {{ $stock->quantity }} un.
                        </span>
                    </div>
                </td>                

                {{-- 7. Ação --}}
                <td class="px-2 py-4 text-center">
                    <button class="px-4 py-2 w-full rounded-lg font-bold text-[10px] tracking-wider uppercase text-white shadow-sm hover:opacity-90 transition-opacity" style="background-color: var(--cor-cta);">
                        Comprar
                    </button>
                </td>
            </tr>
        @elseif($stock)
            {{-- ========================================================== --}}
            {{-- ESTADO 2: ESGOTADO --}}
            <tr class="hover:bg-gray-50 transition duration-150 cursor-pointer opacity-70 grayscale-[30%] {{ $activePrintId == $print->id ? 'bg-blue-50' : '' }}"
                wire:mouseenter="updateStats({{ $print->id }})">
                
                {{-- 1. Símbolo (Com Tooltip) --}}
                <td class="px-4 py-4 text-center">
                    <div class="relative group flex justify-center cursor-help">
                        <x-set-symbol :set="$print->set" rarity="{{ $print->rarity }}" size="w-7 h-7" />
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-white text-gray-800 text-[10px] font-bold px-2 py-1.5 rounded shadow-md border border-gray-200 z-[100] w-max max-w-[130px] text-center leading-tight">
                            {{ $print->set->name }}
                        </div>
                    </div>
                </td>

                {{-- 2. Idioma --}}
                <td class="px-2 py-4 text-center opacity-70">
                    @php $lang = strtolower($stock->language ?? 'en'); @endphp
                    <div class="relative group flex justify-center cursor-help">
                        {{-- Imagem da Bandeira --}}
                        <img src="{{ asset('assets/flags/' . strtolower($lang) . '.svg') }}" 
                            alt="{{ strtoupper($lang) }}" 
                            class="w-6 h-auto mx-auto shadow-sm rounded-sm border border-gray-100 grayscale" 
                            onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                        
                        <span class="text-[10px] font-bold uppercase hidden text-gray-400">{{ $lang }}</span>

                        {{-- Tooltip do Idioma --}}
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-white text-gray-800 text-[10px] font-bold px-2 py-1.5 rounded shadow-md border border-gray-200 z-[100] w-max text-center leading-tight">
                            {{ $fullLang }}
                        </div>
                    </div>
                </td>

                {{-- 3. Qualidade --}}
                <td class="px-4 py-4 text-center">
                    <div class="relative group flex justify-center cursor-help">
                        <span class="text-xs font-bold text-gray-500 uppercase">
                            {{ $stock->condition ?? 'NM' }}
                        </span>

                        {{-- Tooltip da Qualidade --}}
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-white text-gray-800 text-[10px] font-bold px-2 py-1.5 rounded shadow-md border border-gray-200 z-[100] w-max text-center leading-tight">
                            {{ $fullCondition }}
                        </div>
                    </div>
                </td>

                {{-- 4. Extras --}}
                <td class="px-4 py-4 text-center">
                    <div class="text-[10px] font-bold leading-tight flex flex-wrap justify-center items-center gap-1">
                        @php
                            $tags = [];
                            
                            if(is_array($stock->extras) && !empty($stock->extras)) {
                                foreach($stock->extras as $extra) {
                                    if(empty($extra)) continue;
                                    
                                    // 1. Força TUDO para minúsculo primeiro para matar qualquer caixa alta do banco
                                    $lowerExtra = strtolower($extra);
                                    
                                    if (str_contains($lowerExtra, 'etched')) {
                                        $tags[] = '<span class="text-orange-500 font-black">Foil Etched</span>';
                                    } elseif (str_contains($lowerExtra, 'foil')) {
                                        $tags[] = '<span class="text-red-600 font-black">Foil</span>';
                                    } else {
                                        // 2. Limpa os underlines usando a versão que já está 100% minúscula
                                        $cleanName = str_replace('_', ' ', $lowerExtra);
                                        // 3. ucwords agora funciona perfeitamente, subindo só a 1ª letra (ex: Assinada)
                                        $tags[] = '<span class="text-gray-600">' . ucwords($cleanName) . '</span>';
                                    }
                                }
                            }
                        @endphp

                        @if(!empty($tags))
                            {!! implode('<span class="text-gray-400">, </span>', $tags) !!}
                        @else
                            <span class="text-gray-300 font-bold">--</span>
                        @endif
                    </div>
                </td>

                {{-- 5. Preço --}}
                <td class="px-4 py-4 text-center">
                    <div class="text-base font-black tracking-tight text-gray-500">
                        R$ {{ number_format($stock->final_price, 2, ',', '.') }}
                    </div>
                </td>

                {{-- 6. Estoque --}}
                <td class="px-4 py-4 text-center font-medium text-gray-400">
                    0 un.
                </td>                

                {{-- 7. Ação --}}
                <td class="px-4 py-4 text-center">
                    <span class="block w-full text-center py-1.5 rounded text-[10px] font-bold uppercase bg-gray-200 text-gray-500 border border-gray-300">
                        Esgotado
                    </span>
                </td>
            </tr>
        @else
            {{-- ======================================================== --}}
            {{-- ESTADO 3: NUNCA CADASTRADA --}}
            <tr class="hover:bg-gray-50 transition duration-150 cursor-pointer opacity-50 grayscale-[80%] {{ $activePrintId == $print->id ? 'bg-blue-50' : '' }}"
                wire:mouseenter="updateStats({{ $print->id }})">
                
                {{-- 1. Símbolo (Com Tooltip) --}}
                <td class="px-4 py-4 text-center">
                    <div class="relative group flex justify-center cursor-help">
                        <x-set-symbol :set="$print->set" rarity="{{ $print->rarity }}" size="w-7 h-7" />
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-white text-gray-800 text-[10px] font-bold px-2 py-1.5 rounded shadow-md border border-gray-200 z-[100] w-max max-w-[130px] text-center leading-tight">
                            {{ $print->set->name }}
                        </div>
                    </div>
                </td>

                {{-- 2. Idioma (Vazio) --}}
                <td class="px-2 py-4 text-center"></td>

                {{-- 3. Qualidade --}}
                <td class="px-4 py-4 text-center text-gray-400 font-bold">--</td>
                
                {{-- 4. Extras --}}
                <td class="px-4 py-4 text-center text-gray-400 font-bold">--</td>

                {{-- 5. Preço (Sem Estoque) --}}
                <td class="px-4 py-4 text-center">
                    <div class="text-base font-bold tracking-tight text-gray-400">
                        --
                    </div>
                </td>
                
                {{-- 6. Estoque --}}
                <td class="px-4 py-4 text-center font-medium text-gray-400">0 un.</td>
                              
                {{-- 7. Ação --}}
                <td class="px-4 py-4 text-center">
                    <a href="#" class="inline-block text-[10px] font-bold uppercase text-blue-500 hover:text-blue-700 hover:underline transition-colors" style="color: var(--cor-1);">
                        Avise-me
                    </a>
                </td>
            </tr>
        @endif
    @empty
        <tr>
            <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                Nenhuma edição encontrada no banco de dados.
            </td>
        </tr>
    @endforelse
</tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>