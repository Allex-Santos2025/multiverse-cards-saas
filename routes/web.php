<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;

/*
|--------------------------------------------------------------------------
| HUB DE ROTAS (WEB.PHP)
|--------------------------------------------------------------------------
*/

// 1. MARKETPLACE & GERAL (Rotas globais e específicas de jogos)
Route::middleware(['web'])
    ->group(base_path('routes/marketplace.php'));


// 2. ÁREA DO JOGADOR (LOBBY) - MULTI-CONTEXTO
// Aqui centralizamos os 3 caminhos que levam ao mesmo arquivo de rotas do lobby

// 2.1 Lobby Global (Lobby direto da Versus)
Route::prefix('lobby')
    ->middleware(['web', 'auth:player'])
    ->name('lobby.')
    ->group(base_path('routes/lobby.php'));

// 2.2 Lobby do Jogo (Dentro de um Marketplace específico)
Route::prefix('marketplace/{game_slug}/lobby')
    ->middleware(['web', 'auth:player'])
    ->name('game.lobby.')
    ->group(base_path('routes/lobby.php'));

// 2.3 Lobby da Loja (Dentro de uma loja específica)
Route::prefix('loja/{slug}/lobby')
    ->middleware(['web', 'auth:player'])
    ->name('store.lobby.')
    ->group(base_path('routes/lobby.php'));


// 3. DASHBOARD (Área Administrativa da Loja)
Route::prefix('loja/{slug}/dashboard')
    ->middleware(['web', 'auth:store_user']) 
    ->group(base_path('routes/dashboard.php'));


// 4. LOJA FRONT (Navegação na Loja)
Route::prefix('loja/{slug}')
    ->middleware(['web', 'auth.store_optional'])
    ->group(base_path('routes/store_front.php'));


// 5. FALLBACK DA RAIZ
Route::middleware(['web'])
    ->prefix('/{storeSlug}')
    ->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('store.home');
    });