<?php

namespace App\Http\Controllers;

use App\Models\CardFunctionality;
use App\Models\Game; // Usaremos isto para o seletor de jogos
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    /**
     * Exibe a listagem principal de jogos/categorias (Marketplace Home/Landing).
     */
    public function index()
    {
        // A LÓGICA CORRETA: Filtrar APENAS por jogos marcados como ativos.
        $games = Game::where('is_active', true)
            ->get();

        return view('marketplace.home', [
            'games' => $games,
        ]);
    }
    
    /**
     * Exibe a listagem de cartas para um jogo específico (Lista de Cartas/Catálogo).
     */
    public function showCatalog(string $game_slug) 
    {
        // Busca o Model manualmente pelo slug
        $game = Game::where('url_slug', $game_slug)->firstOrFail();
               
        // 1. Buscamos todas as funcionalidades de carta (o nome canônico)
        // 2. Usamos eager loading para buscar os preços de estoque (StockItem) de cada uma
        $cards = CardFunctionality::where('game_id', $game->id)
                                  ->with(['cards', 'stockItems']) 
                                  ->paginate(50); // Paginação padrão para não travar

        return view('marketplace.catalog', [
            'game' => $game,
            'cards' => $cards,
        ]);
    }
}