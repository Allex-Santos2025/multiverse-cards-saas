{{--
    Blade: Lista de Impressões (Prints)
    Objetivo: Mostrar todas as edições de um Conceito com preços e permitir a troca de print na imagem principal.

    Variáveis esperadas:
    - $allPrintGroups: Collection de Prints (agrupada por Set ID)
    - $currentPrintId: ID do print atualmente selecionado
    - $tcgName: Nome do TCG (Ex: 'Magic: The Gathering')
    - $currentPage, $totalPages, $totalPrints: Dados de paginação
--}}

@php
    // --- VARIÁVEIS DE CONFIGURAÇÃO (Mantidas do Legado) ---
    $showBrl = true;
    $usdToBrlRate = 5.00; 

    // Mapeia raridade para classe de cor da Keyrune (apenas visual)
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
        'uncommon' => 'info',
        'rare' => 'warning',
        'mythic' => 'danger',
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
                // Pega o primeiro print do grupo como representante
                $print = $printGroup->first(); 
                $set = $print->set;
                $specific = $print->specific; // <--- CHAVE DA V4
                
                // ----------------------------------------------------
                // INÍCIO DA LÓGICA TCG-AWARE (V4)
                // ----------------------------------------------------
                
                $setCode = strtolower($set?->code ?? ''); 
                $setName = $set?->name ?? 'Set Desconhecido';

                // --- 1. LÓGICA DE RARIDADE ---
                // Leitura polimórfica (assumindo que 'rarity' é um campo comum ou acessado via Specific)
                $rarity = strtolower($specific?->rarity ?? 'common'); 
                $rarityClass = $rarityClassMap[$rarity] ?? 'ss-common'; 
                $badgeColor = $badgeColorMap[$rarity] ?? 'gray';

                // --- 2. LÓGICA DE NÚMERO ---
                $collectionNumber = $specific->number ?? 'N/A'; // Campo 'number' existe em PkPrint/MtgPrint
                
                // --- 3. LÓGICA DE PREÇOS (TCG-Aware no Blade) ---
                $priceUsd = null;
                $priceEur = null;
                $priceTix = null;
                $pricesJson = [];

                if ($tcgName === 'Magic: The Gathering') {
                    // MtgPrint (mtg_prices)
                    $pricesJson = $specific->mtg_prices ?? [];
                    $priceUsd = $pricesJson['usd'] ?? $pricesJson['usd_foil'] ?? null;
                    $priceEur = $pricesJson['eur'] ?? $pricesJson['eur_foil'] ?? null;
                    $priceTix = $pricesJson['tix'] ?? null;
                } elseif ($tcgName === 'Pokémon TCG') {
                    // PkPrint (tcgplayer)
                    $pricesJson = $specific->tcgplayer ?? [];
                    $priceUsd = $pricesJson['normal']['market'] 
                                ?? $pricesJson['holofoil']['market'] 
                                ?? $pricesJson['reverseHolofoil']['market'] 
                                ?? null;
                    $priceEur = $specific->cardmarket['suggestedPrice'] ?? null; // Exemplo de busca Cardmarket
                }
                
                // Conversão BRL
                $priceBrl = $showBrl && $priceUsd ? number_format((float)$priceUsd * $usdToBrlRate, 2) : null;
                
                $isCurrent = $printGroup->contains('id', $currentPrintId);
            @endphp
        
            {{-- O ITEM CLICÁVEL (div inteira) --}}
            <div 
                {{-- Passa o ID do card representante para o método changePrint no controlador --}}
                wire:click="changePrint({{ $print->id }})" 
                wire:loading.class="opacity-50"
                wire:target="changePrint"
                class="
                    flex items-center p-1 rounded-lg transition cursor-pointer 
                    {{ $isCurrent ? 'bg-primary-100 dark:bg-primary-800' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}
                "
                title="Selecionar {{ $setName }} #{{ $collectionNumber }}"
            >
                {{-- Ícone Keyrune com Cor (Funciona para Magic. Para Pokémon, será apenas o código) --}}
                <div class="flex-shrink-0 mr-2 text-lg" style="width: 1.25em; text-align: center;">
                    @if ($tcgName === 'Magic: The Gathering')
                        <i class="ss ss-{{ $setCode }} {{ $rarityClass }}" aria-hidden="true"></i>
                    @else
                        <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $set->code }}</span>
                    @endif
                </div> 
                
                {{-- Nome do Set e Número/Raridade --}}
                <div class="flex-1 text-sm overflow-hidden">
                    <p class="font-medium truncate {{ $isCurrent ? 'text-primary-700 dark:text-primary-400' : 'text-gray-800 dark:text-gray-200' }}">
                        {{ $setName }} 
                    </p>
                    <div class="flex items-center text-xs mt-0.5"> 
                        <span class="{{ $isCurrent ? 'text-primary-600 dark:text-primary-500' : 'text-gray-500' }}">
                            #{{ $collectionNumber }}
                        </span>
                        <x-filament::badge :color="$badgeColor" class="ml-1.5" size="xs">
                            {{ $rarity }}
                        </x-filament::badge>
                    </div>
                </div>

                {{-- Preços Alinhados --}}
                <div class="flex text-sm font-semibold pl-2 text-right">
                    <span class="w-12 {{ $priceUsd ? 'text-gray-700 dark:text-gray-300' : 'text-gray-500' }}">
                        {{ $priceUsd ? '$' . number_format((float)$priceUsd, 2) : '—' }}
                    </span>
                    <span class="w-12 {{ $priceEur ? 'text-gray-700 dark:text-gray-300' : 'text-gray-500' }}">
                        {{ $priceEur ? '€' . number_format((float)$priceEur, 2) : '—' }}
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
    
    {{-- Navegação de Paginação Inferior --}}
    @if($totalPages > 1)
        <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center text-sm">
            <button wire:click="previousPage" @if($currentPage <= 1) disabled @endif class="text-primary-600 dark:text-primary-400 hover:underline disabled:opacity-50">
                &laquo; Anterior
            </button>
            <span class="text-xs text-gray-500">Pág {{ $currentPage }} de {{ $totalPages }}</span>
            <button wire:click="nextPage" @if($currentPage >= $totalPages) disabled @endif class="text-primary-600 dark:text-primary-400 hover:underline disabled:opacity-50">
                Próxima &raquo;
            </button>
        </div>
    @endif
</div>