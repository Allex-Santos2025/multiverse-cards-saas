@php
    // Defina se quer mostrar o BRL (true/false)
    $showBrl = true;
    // Defina a taxa de conversão
    $usdToBrlRate = 5.00; 

    // Mapeia raridade para classe de cor da Keyrune
    $rarityClassMap = [
        'common' => 'ss-common',
        'uncommon' => 'ss-uncommon',
        'rare' => 'ss-rare',
        'mythic' => 'ss-mythic',
        'special' => 'ss-mythic', 
    ];
    // Mapeia raridade para cor de Badge do Filament
    $badgeColorMap = [
        'common' => 'gray',
        'uncommon' => 'info',    // Prata
        'rare' => 'warning', // Dourado
        'mythic' => 'danger',  // Mítico/Bronze
        'special' => 'primary',
    ];
@endphp

{{-- Cabeçalho da Tabela de Preços --}}
<div class="flex items-center text-xs text-gray-500 dark:text-gray-400 font-semibold mb-2 pr-2">
    <span class="flex-1">PRINTS</span>
    <span class="w-12 text-right">USD</span>
    <span class="w-12 text-right">EUR</span>
    <span class="w-12 text-right">TIX</span>
    @if($showBrl)
        <span class="w-12 text-right">BRL</span>
    @endif
</div>

{{-- Lista de Prints --}}
<div class="flex flex-col space-y-1">

    @if($allPrintGroups->isEmpty())
        <span class="text-sm text-gray-500">Nenhuma impressão encontrada.</span>
    @else
        @foreach($allPrintGroups as $groupKey => $printGroup)
            @php
                // Pega o primeiro card do grupo (ex: 'en') como representante
                $print = $printGroup->first(); 
                
                // ----------------------------------------------------
                // INÍCIO DA LÓGICA REATORADA (TCG-AWARE)
                // ----------------------------------------------------
                
               // $tcgName = $print->tcg_name;
                
                $set = $print->set; 
                $setCode = strtolower($set?->code ?? ''); 
                $setName = $set?->name ?? 'Set Desconhecido';
                
                // --- 1. LÓGICA DE RARIDADE (CORRIGIDA) ---
                // Puxa a raridade direto do campo do BD correto
                $rarity = match ($tcgName) {
                    'Magic: The Gathering' => $print->mtg_rarity,
                    'Battle Scenes' => $print->bs_rarity,
                    // TODO: Adicionar os campos de raridade que faltam (dos outros 6 TCGs)
                    // quando os adicionarmos ao banco de dados `cards`.
                    default => 'common', // Fallback
                };
                $rarity = strtolower($rarity); // Garante minúsculas
                $rarityClass = $rarityClassMap[$rarity] ?? 'ss-common'; 
                $badgeColor = $badgeColorMap[$rarity] ?? 'gray';

                // --- 2. LÓGICA DE NÚMERO (CORRIGIDA) ---
                // Puxa o número direto do campo do BD correto
                $collectionNumber = match ($tcgName) {
                    'Magic: The Gathering' => $print->mtg_collection_number,
                    'Pokémon TCG' => $print->pk_number,
                    'Lorcana TCG' => $print->lor_collector_number,
                    'Star Wars: Unlimited' => $print->swu_card_number,
                    'Battle Scenes' => $print->bs_collection_number,
                    'One Piece Card Game' => $print->op_card_id_name,
                    'Flesh and Blood' => $print->fab_identifier,
                    'Yu-Gi-Oh!' => $print->ygo_konami_id, // Fallback para o ID
                    default => 'N/A',
                };

                // --- 3. LÓGICA DE PREÇOS (CORRIGIDA) ---
                // Puxa os preços direto do campo JSON do BD correto
                $priceUsd = null;
                $priceEur = null;
                $priceTix = null;
                $pricesJson = null;

                switch ($tcgName) {
                    case 'Magic: The Gathering':
                        $pricesJson = $print->mtg_prices ?? [];
                        $priceUsd = $pricesJson['usd'] ?? $pricesJson['usd_foil'] ?? null;
                        $priceEur = $pricesJson['eur'] ?? $pricesJson['eur_foil'] ?? null;
                        $priceTix = $pricesJson['tix'] ?? null;
                        break;
                    case 'Pokémon TCG':
                        // O 'pk_tcgplayer_prices' tem uma estrutura diferente
                        $pricesJson = $print->pk_tcgplayer_prices ?? [];
                        // Tenta achar o preço de qualquer raridade (normal, holofoil, reverse)
                        $priceUsd = $pricesJson['normal']['market'] 
                                 ?? $pricesJson['holofoil']['market'] 
                                 ?? $pricesJson['reverseHolofoil']['market'] 
                                 ?? $pricesJson[array_key_first($pricesJson ?? [])]['market']['price'] // Tenta o primeiro que achar
                                 ?? null;
                        break;
                    case 'Yu-Gi-Oh!':
                        // YGO (ygo_card_prices) é um array
                        $pricesJson = $print->ygo_card_prices[0] ?? [];
                        $priceUsd = $pricesJson['tcgplayer_price'] ?? null;
                        $priceEur = $pricesJson['cardmarket_price'] ?? null;
                        break;
                    case 'Lorcana TCG':
                        $pricesJson = $print->lor_prices ?? [];
                        $priceUsd = $pricesJson['usd'] ?? $pricesJson['usd_foil'] ?? null;
                        break;
                }
                
                $priceBrl = $showBrl && $priceUsd ? number_format((float)$priceUsd * $usdToBrlRate, 2) : null;
                
                // ----------------------------------------------------
                // FIM DA LÓGICA REATORADA
                // ----------------------------------------------------

                $isCurrent = $printGroup->contains('id', $currentPrintId);
            @endphp
        
            {{-- O ITEM CLICÁVEL (div inteira) --}}
            <div 
                {{-- Passa o ID do card representante ('en') para o método changePrint --}}
                wire:click="changePrint({{ $print->id }})" 
                wire:loading.class="opacity-50"
                wire:target="changePrint"
                class="
                    flex items-center p-1 rounded-lg transition cursor-pointer 
                    {{ $isCurrent ? 'bg-primary-100 dark:bg-primary-800' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}
                "
                title="Selecionar {{ $setName }} #{{ $collectionNumber }}" {{-- CORRIGIDO --}}
            >
                {{-- Ícone Keyrune com Cor --}}
                <i class="ss ss-{{ $setCode }} {{ $rarityClass }} mr-2 text-lg" 
                   style="flex-shrink: 0; width: 1.25em; text-align: center;" 
                   aria-hidden="true"></i> 
                
                {{-- Nome do Set e Número/Raridade (com Badge) --}}
                <div class="flex-1 text-sm overflow-hidden">
                    <p class="font-medium truncate {{ $isCurrent ? 'text-primary-700 dark:text-primary-400' : 'text-gray-800 dark:text-gray-200' }}">
                        {{ $setName }} 
                    </p>
                    <div class="flex items-center text-xs mt-0.5"> 
                        <span class="{{ $isCurrent ? 'text-primary-600 dark:text-primary-500' : 'text-gray-500' }}">
                            #{{ $collectionNumber }} {{-- CORRIGIDO --}}
                        </span>
                        <x-filament::badge :color="$badgeColor" class="ml-1.5" size="xs">
                            {{ $rarity }} {{-- CORRIGIDO --}}
                        </x-filament::badge>
                    </div>
                </div>

                {{-- Preços Alinhados (do card representante 'en') --}}
                <div class="flex text-sm font-semibold pl-2 text-right">
                    <span class="w-12 {{ $priceUsd ? 'text-gray-700 dark:text-gray-300' : 'text-gray-500' }}">
                        {{ $priceUsd ? '$' . $priceUsd : '—' }}
                    </span>
                    <span class="w-12 {{ $priceEur ? 'text-gray-700 dark:text-gray-300' : 'text-gray-500' }}">
                        {{ $priceEur ? '€' . $priceEur : '—' }}
                    </span>
                    <span class="w-12 {{ $priceTix ? 'text-orange-500' : 'text-gray-500' }}">
                        {{ $priceTix ?? '—' }}
                    </span>
                    @if($showBrl)
                        <span class="w-12 {{ $priceBrl ? 'text-green-600' : 'text-gray-500' }}">
                            {{ $priceBrl ? 'R$' . $priceBrl : '—' }}
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
    
    <a href="#" class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-700 text-sm text-primary-600 dark:text-primary-400 hover:underline">
        View all prints →
    </a>
</div>