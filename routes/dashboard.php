<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Livewire\Store\Dashboard\Index; // O componente novo do dashboard
use App\Livewire\Store\Dashboard\Stock\ManageInventory; 
use App\Livewire\Store\Dashboard\Stock\ManageSingleCard;
use App\Livewire\Store\Dashboard\Stock\StockHistory; // O componente do histórico
use App\Livewire\Store\Dashboard\Layout\VisualIdentity;
use App\Livewire\Store\Dashboard\Operations\ShippingSettings; // Importação da nova rota de frete
use App\Livewire\Store\Dashboard\Management\Profile; // Importação da nova rota de perfil

/*
|--------------------------------------------------------------------------
| DASHBOARD ROUTES
|--------------------------------------------------------------------------
| Contexto: /loja/{slug}/dashboard
|--------------------------------------------------------------------------
*/

// 1. A ROTA PRINCIPAL (Home do Dashboard - Agora via Componente Livewire)
Route::get('/', Index::class)->name('store.dashboard'); 

// 2. O RESTO DAS ROTAS
Route::name('store.dashboard.')->group(function () {

    // --- NOVA ROTA: Histórico de Estoque (Global, sem depender do jogo) ---
    Route::get('/historico-estoque', StockHistory::class)->name('stock.history');

    // --- GERENCIAIS ---
    Route::prefix('gerenciais')->name('management.')->group(function () {
        Route::get('/perfil', Profile::class)->name('profile');
    });

    // --- GRUPO DE JOGOS E ESTOQUE ---
    Route::prefix('{game_slug}')->group(function () {
        Route::prefix('estoque')->name('stock.')->group(function () {
            
            Route::get('/', ManageInventory::class)->name('index');
            
            // ROTA DE GERENCIAMENTO INDIVIDUAL DA CARTA
            Route::get('/carta/{conceptSlug}', ManageSingleCard::class)->name('manage-card');
            
        });
    });

    // --- OPERAÇÕES ---
    Route::prefix('operacoes')->name('operations.')->group(function () {
        // Rota final: /loja/{slug}/dashboard/operacoes/envios-e-retiradas
        // Nome final: store.dashboard.operations.shipping
        Route::get('/envios-e-retiradas', ShippingSettings::class)->name('shipping');
    });

    // --- LAYOUT E APARÊNCIA ---
    Route::prefix('layout')->name('layout.')->group(function () {
        Route::get('/identidade-visual', VisualIdentity::class)->name('visual-identity');
    });

    // --- LOGS ---
    Route::get('/logs', function ($slug) {
        return view('livewire.store.dashboard.logs', ['slug' => $slug]);
    })->name('logs');

    // --- NOVIDADES (LISTA) ---
    Route::get('/novidades', function($slug) {
        return view('livewire.store.dashboard.novidades', compact('slug'));
    })->name('novidades');

    // --- NOVIDADES (DETALHE + MARCAR COMO LIDO) ---
    Route::get('/novidades/{changelog_slug}', function($slug, $changelog_slug) {
        
        $changelog = \App\Models\Changelog::where('slug', $changelog_slug)
            ->where('is_published', true)->firstOrFail();
        
        $user = auth('store_user')->user();
        
        // Lógica para registrar que o usuário leu
        if ($user) {
            \App\Models\ChangelogUserRead::updateOrInsert(
                ['store_user_id' => $user->id, 'changelog_id' => $changelog->id],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
        
        return view('livewire.store.dashboard.changelog-detail', compact('changelog', 'slug'));
    })->name('novidades.show');

    // --- CATEGORIAS ---
    Route::get('/categorias', \App\Livewire\Store\Dashboard\DashboardStoreMenus::class)->name('categorias');

});