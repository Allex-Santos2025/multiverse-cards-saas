<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;

/*
|--------------------------------------------------------------------------
| HUB DE ROTAS (WEB.PHP) - ARQUITETURA LIGA MAGIC
|--------------------------------------------------------------------------
*/

$host = request()->getHost();
$mainDomain = env('APP_URL_DOMAIN', 'versustcg.com.br');

// Verifica se estamos acessando por um domínio customizado
if ($host !== $mainDomain && $host !== 'www.' . $mainDomain) {

    // ==========================================================================
    // 1. MODO DOMÍNIO CUSTOMIZADO (Ex: olhodeleao.mooo.com)
    // ==========================================================================
    // Aqui nós removemos o prefixo 'loja/{slug}'. 
    // Como as suas Blades enviam o ['slug' => $slug], o Laravel automaticamente 
    // vai jogar isso para o final da URL como o parâmetro invisível (?slug=...)
    Route::middleware(['web', \App\Http\Middleware\DomainMiddleware::class])->group(function () {
        
        // Home da Loja (Raiz limpa)
        Route::get('/', [StoreController::class, 'index'])->name('store.home');

        // Vitrine (Ex: olhodeleao.mooo.com/carrinho?slug=olhodeleao)
        Route::middleware(['auth.store_optional'])->group(base_path('routes/store_front.php'));

        // Dashboard (Ex: olhodeleao.mooo.com/dashboard?slug=olhodeleao)
        Route::prefix('dashboard')->middleware(['auth:store_user'])->group(base_path('routes/dashboard.php'));

        // Lobby da Loja
        Route::prefix('lobby')->middleware(['auth:player'])->name('store.lobby.')->group(base_path('routes/lobby.php'));
    });

} else {

    // ==========================================================================
    // 2. MODO VERSUS TCG (Marketplace Principal e Lojas com /loja/slug)
    // ==========================================================================
    Route::domain($mainDomain)->group(function () {
        
        // Marketplace Global
        Route::middleware(['web'])->group(base_path('routes/marketplace.php'));

        // Lobbies Globais
        Route::prefix('lobby')->middleware(['web', 'auth:player'])->name('lobby.')->group(base_path('routes/lobby.php'));
        Route::prefix('marketplace/{game_slug}/lobby')->middleware(['web', 'auth:player'])->name('game.lobby.')->group(base_path('routes/lobby.php'));

        // Rotas das Lojas na Versus (Aqui o {slug} CONTINUA na URL)
        Route::prefix('loja/{slug}')->middleware(['web', \App\Http\Middleware\DomainMiddleware::class])->group(function () {
            Route::get('/', [StoreController::class, 'index'])->name('store.home');
            
            Route::middleware(['auth.store_optional'])->group(base_path('routes/store_front.php'));
            Route::prefix('dashboard')->middleware(['auth:store_user'])->group(base_path('routes/dashboard.php'));
            Route::prefix('lobby')->middleware(['auth:player'])->name('store.lobby.')->group(base_path('routes/lobby.php'));
        });

    });
}