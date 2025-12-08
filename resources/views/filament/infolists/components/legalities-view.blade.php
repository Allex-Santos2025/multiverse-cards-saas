{{--
    Blade: Legalidades da Carta (Agnóstico ao Jogo)
    Objetivo: Exibir o status de legalidade para os formatos existentes no JSON da carta.

    Variável esperada:
    - $legalities: Array associativo ou JSON string com formato => status (ex: ['standard' => 'legal']).
    - $tcgName: String com o nome do jogo.
--}}

@php
    // Decodifica o JSON de legalidades
    $legalityData = is_string($legalities) ? json_decode($legalities, true) : (is_array($legalities) ? $legalities : []);
    
    // Mapeia os status para cores do Filament (Padronizado)
    $badgeColors = [
        'legal' => 'success',
        'banned' => 'danger',
        'restricted' => 'warning',
        'not_legal' => 'gray',
        'unknown' => 'gray',
    ];
    
    // Mapeia os status para texto em Português (Padronizado)
    $badgeText = [
        'legal' => 'Legal',
        'banned' => 'Banido',
        'restricted' => 'Restrito',
        'not_legal' => 'Não Legal',
        'unknown' => 'Desconhecido',
    ];

    // Ordena os formatos alfabeticamente para melhor visualização
    if (!empty($legalityData)) {
        ksort($legalityData);
    }
@endphp

{{-- Cria um grid de 2 colunas para as legalidades --}}
<div class="grid grid-cols-2 gap-x-4 gap-y-2">
    @if (!empty($legalityData))
        {{-- Itera diretamente sobre os formatos encontrados no JSON (sem lista estática) --}}
        @foreach($legalityData as $formatKey => $status)
            @php
                // Formata a chave para um nome amigável (ex: 'commander' -> 'Commander')
                $name = ucwords(str_replace('_', ' ', $formatKey));
                
                // Limpa o status para garantir que bate com o array de cores
                $cleanStatus = strtolower($status) ?? 'unknown';
            @endphp

            <div class="flex justify-between items-center text-sm">
                {{-- Nome do Formato --}}
                <span class="text-gray-600 dark:text-gray-300">{{ $name }}</span>
                
                {{-- Badge de Status --}}
                <x-filament::badge :color="$badgeColors[$cleanStatus] ?? 'gray'">
                    {{ $badgeText[$cleanStatus] ?? $status }}
                </x-filament::badge>
            </div>
        @endforeach
    @else
        <div class="text-sm text-gray-400 italic col-span-2">Nenhum dado de legalidade encontrado.</div>
    @endif
</div>