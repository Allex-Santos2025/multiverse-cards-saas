<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StoreController;

/*
|--------------------------------------------------------------------------
| HUB DE ROTAS (WEB.PHP)
|--------------------------------------------------------------------------
*/

// 1. MARKETPLACE & GERAL (Rotas globais)
Route::group([], base_path('routes/marketplace.php'));


// 2. DASHBOARD (Área Administrativa)
// Prefixo: /loja/{slug}/dashboard
// Middleware: auth
Route::prefix('loja/{slug}/dashboard')
    //->name('store.dashboard.')
    ->middleware(['web', 'auth:store_user']) 
    ->group(base_path('routes/dashboard.php'));


// 3. LOJA FRONT (Login e Visual)
// Prefixo: /loja/{slug}
// AQUI ESTAVA O PERIGO: O prefixo é definido aqui. O arquivo filho só completa.
Route::prefix('loja/{slug}')
    ->middleware(['web'])
    ->group(base_path('routes/store_front.php'));


// 4. FALLBACK DA RAIZ (Domínio Próprio ou Wildcard Final)
// Deixamos por último para não engolir as rotas acima
Route::middleware(['web'])
    ->prefix('/{storeSlug}')
    ->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('store.home');
    });