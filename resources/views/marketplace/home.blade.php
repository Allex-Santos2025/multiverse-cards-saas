<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiverse Cards - Escolha o Jogo</title>
    <style>
        body { font-family: sans-serif; background-color: #121212; color: #f0f0f0; }
        .game-card { transition: transform 0.2s; }
        .game-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.5); }
    </style>
</head>
<body>
    <div style="max-width: 1200px; margin: 50px auto; padding: 20px;">
        <h1 style="text-align: center; margin-bottom: 40px; font-size: 2.5em;">Bem-vindo ao Multiverse Cards Marketplace</h1>
        <p style="text-align: center; margin-bottom: 60px; font-size: 1.2em;">O Marketplace Único para colecionadores e lojistas. Escolha o jogo para começar:</p>
        <div style="background-color: #007bff; color: white; padding: 40px; border-radius: 12px; margin-bottom: 50px; text-align: center;">
            <h2 style="font-size: 2em; margin-bottom: 10px;">Venda e Colecione no Multiverse Cards!</h2>
            <p style="font-size: 1.1em; margin-bottom: 25px;">
                Junte-se à maior comunidade de card games. Cadastre-se como Jogador ou Lojista.
            </p>
            
            <a href="{{ route('register') }}" 
            style="background-color: #ff9900; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 1.1em; transition: background-color 0.3s;">
                CADASTRE-SE AGORA
            </a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
            @forelse ($games as $game)
 
                   <a href="{{ route('marketplace.catalog', ['game' => $game->id]) }}"
                   class="game-card" 
                   style="display: block; text-decoration: none; background-color: #1e1e1e; padding: 20px; border-radius: 10px; text-align: center;">
                    
                    <h2 style="font-size: 1.5em; margin-bottom: 10px;">{{ $game->name }}</h2>
                    <p style="color: #bbb;">{{ $game->description ?? 'Catálogo de cards' }}</p>
                    
                    <div style="height: 150px; background-color: #333; margin-top: 15px; border-radius: 5px; line-height: 150px; font-size: 1.2em;">
                        LOGO
                    </div>

                </a>
            @empty
                <p style="grid-column: 1 / -1; text-align: center; color: #ff6b6b;">Nenhum jogo encontrado. O catálogo está sendo atualizado!</p>
            @endforelse
        </div>
    </div>
</body>
</html>