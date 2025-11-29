@php
    // Decodifica o JSON de legalidades
    $legalityData = is_string($legalities) ? json_decode($legalities, true) : (is_array($legalities) ? $legalities : []);

    // Mapeia os formatos para nomes amigáveis (ordem comum)
    $formatNames = [
        'standard' => 'Standard',
        'pioneer' => 'Pioneer',
        'modern' => 'Modern',
        'legacy' => 'Legacy',
        'vintage' => 'Vintage',
        'pauper' => 'Pauper',
        'commander' => 'Commander',
        'brawl' => 'Brawl',
        'historic' => 'Historic',
        'alchemy' => 'Alchemy',
        'penny' => 'Penny',
        'oathbreaker' => 'Oathbreaker',
    ];

    // Mapeia os status para cores do Filament
    $badgeColors = [
        'legal' => 'success',
        'banned' => 'danger',
        'restricted' => 'warning',
        'not_legal' => 'gray',
    ];
    
    // Mapeia os status para texto
    $badgeText = [
        'legal' => 'Legal',
        'banned' => 'Banido',
        'restricted' => 'Restrito',
        'not_legal' => 'Não Legal',
    ];
@endphp

{{-- Cria um grid de 2 colunas para as legalidades --}}
<div class="grid grid-cols-2 gap-x-4 gap-y-2">
    @foreach($formatNames as $key => $name)
        @php
            // Pega o status do JSON; se não existir, assume 'not_legal'
            $status = $legalityData[$key] ?? 'not_legal';
        @endphp

        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600 dark:text-gray-300">{{ $name }}</span>
            <x-filament::badge :color="$badgeColors[$status] ?? 'gray'">
                {{ $badgeText[$status] ?? 'Não Legal' }}
            </x-filament::badge>
        </div>
    @endforeach
</div>