<?php

namespace App\Http\Controllers;

use App\Models\PlayerBinder;
use App\Models\Game;
use App\Services\PlayerCollectionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerCollectionController extends Controller
{
    public function __construct(
        protected PlayerCollectionService $collectionService
    ) {}

    /**
     * Exibe a visão geral da coleção e fichários.
     */
    public function index(Request $request): View
    {
        $playerId = auth('web')->id();

        // 3 Fichários em destaque
        $featuredBinders = $this->collectionService->getFeaturedBinders($playerId);
        $totalBindersCount = PlayerBinder::where('player_user_id', $playerId)->count();

        // Filtro por jogo selecionado (ou todos)
        $selectedGameId = $request->filled('game_id') ? (int) $request->game_id : null;
        $completedSets = $this->collectionService->getCompletedSetsSummary($playerId, $selectedGameId);

        $games = Game::select('id', 'name')->get();

        return view('player.collection.index', compact(
            'featuredBinders',
            'totalBindersCount',
            'completedSets',
            'games',
            'selectedGameId'
        ));
    }

    /**
     * Abre a pasta física com os bolsos interativos.
     */
    public function showBinder(string $slug): View
    {
        $playerId = auth('web')->id();

        $binder = PlayerBinder::where('player_user_id', $playerId)
            ->where('slug', $slug)
            ->with(['slots.collectionItem.catalogPrint'])
            ->firstOrFail();

        return view('player.collection.binder-viewer', compact('binder'));
    }
}
