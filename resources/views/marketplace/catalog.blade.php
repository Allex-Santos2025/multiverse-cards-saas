<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $game->name }} Catálogo - Multiverse Cards</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; color: #333; }
        .container { display: flex; max-width: 1400px; margin: 20px auto; }
        .sidebar { flex-basis: 250px; background-color: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-right: 20px; }
        .content { flex-grow: 1; }
        .card-row { display: flex; align-items: center; border-bottom: 1px solid #ddd; padding: 15px 0; background-color: #fff; margin-bottom: 8px; border-radius: 4px; }
        .card-info { flex-grow: 1; padding: 0 15px; }
        .price-info { width: 150px; text-align: right; font-weight: bold; color: #10b981; }
        .mana-symbol { vertical-align: middle; margin-left: 2px; }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="sidebar">
            <h3 style="margin-bottom: 15px; border-bottom: 2px solid #ccc; padding-bottom: 10px;">Filtros: {{ $game->name }}</h3>
            <p>Vendedores ({{-- Lógica de Lojas --}})</p>
            <p>Edições ({{-- Lógica de Sets --}})</p>
            <p style="margin-top: 30px; font-weight: bold;">(Espaço para busca global)</p>
        </div>
        
        <div class="content">
            <h1 style="font-size: 2em; margin-bottom: 20px;">Catálogo: {{ $game->name }}</h1>

            @forelse ($cards as $cardFunctionality)
                <div class="card-row">
                    <div class="card-info">
                        <a href="#" style="font-size: 1.2em; font-weight: bold; text-decoration: none; color: #007bff;">
                            {{-- Usa o nome canônico limpo, como fizemos no Filament --}}
                            {{ $cardFunctionality->name }} 
                        </a>
                        <p style="font-size: 0.9em; color: #666;">
                            {{ $cardFunctionality->type_line }}
                            {{-- Aqui você pode injetar o HTML do custo de mana usando o seu helper --}}
                            {{-- Exemplo: {!! \App\Filament\Resources\CardFunctionalities\CardFunctionalityResource::convertManaSymbolsToHtml($cardFunctionality->mana_cost) !!} --}}
                        </p>
                    </div>
                    
                    <div class="price-info">
                        @if ($cardFunctionality->stockItems->isNotEmpty())
                            R$ {{ number_format($cardFunctionality->stockItems->min('price_usd') * 5, 2, ',', '.') }}
                            <p style="font-size: 0.8em; color: #999; font-weight: normal;">(Preço mais baixo)</p>
                        @else
                            <span style="color: #f00;">Esgotado</span>
                        @endif
                    </div>

                    <a href="#" style="background-color: #007bff; color: white; padding: 10px 15px; border-radius: 5px; text-decoration: none; margin-left: 10px;">
                        Ver Lojas
                    </a>
                </div>
            @empty
                <p>Nenhuma carta encontrada neste catálogo.</p>
            @endforelse
            
            <div style="margin-top: 30px; text-align: center;">
                {{ $cards->links() }}
            </div>
        </div>
    </div>
</body>
</html>