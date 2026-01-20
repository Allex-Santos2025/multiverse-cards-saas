<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escolha o Tipo de Conta - Multiverse Cards</title>
    <style>
        body { font-family: sans-serif; background-color: #f0f0f0; color: #333; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); max-width: 600px; width: 90%; text-align: center; }
        .choice-grid { display: flex; gap: 30px; margin-top: 30px; justify-content: center; }
        .choice-card { flex: 1; padding: 25px; border: 2px solid #ccc; border-radius: 8px; text-decoration: none; color: #333; transition: all 0.3s ease; cursor: pointer; }
        .choice-card:hover { border-color: #ff9900; transform: translateY(-3px); box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15); }
        .choice-card h3 { margin-top: 10px; color: #ff9900; }
        .choice-card p { font-size: 0.9em; color: #666; }
        .icon { font-size: 2.5em; color: #007bff; }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="font-size: 1.8em; margin-bottom: 10px;">Crie sua Conta Multiverse Cards</h1>
        <p style="margin-bottom: 30px; color: #666;">Para continuar, escolha seu papel no Marketplace:</p>

        <div class="choice-grid">
            <a href="{{ route('register.player') }}" class="choice-card">
                <span class="icon">👤</span> 
                <h3>Sou um JOGADOR</h3>
                <p>Compre, colecione e gerencie seus decks e lista de desejos.</p>
            </a>

            <a href="{{ route('register.store') }}" class="choice-card">
                <span class="icon">🛍️</span>
                <h3>Sou um LOJISTA</h3>
                <p>Crie sua loja, gerencie estoque e venda para toda a comunidade.</p>
            </a>
        </div>
    </div>
</body>
</html>