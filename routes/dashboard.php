<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Livewire\Store\Dashboard\Index;
use App\Livewire\Store\Dashboard\Stock\ManageInventory; 
use App\Livewire\Store\Dashboard\Stock\ManageSingleCard;
use App\Livewire\Store\Dashboard\Stock\StockHistory;
use App\Livewire\Store\Dashboard\Layout\VisualIdentity;
use App\Livewire\Store\Dashboard\Operations\ShippingSettings;
use App\Livewire\Store\Dashboard\Management\Profile;

/*
|--------------------------------------------------------------------------
| DASHBOARD ROUTES
|--------------------------------------------------------------------------
| Contexto: /loja/{slug}/dashboard OU /dashboard (subdomínio)
|--------------------------------------------------------------------------
*/

// 1. A ROTA PRINCIPAL (Home do Dashboard)
Route::get('/', Index::class)->name('store.dashboard'); 

// 2. ROTAS INTERNAS DO DASHBOARD
Route::name('store.dashboard.')->group(function () {

    // --- HISTÓRICO DE ESTOQUE ---
    Route::get('/historico-estoque', StockHistory::class)->name('stock.history');

    // --- GERENCIAIS ---
    Route::prefix('gerenciais')->name('management.')->group(function () {
        Route::get('/perfil', Profile::class)->name('profile');
    });

    // --- OPERAÇÕES ---
    Route::prefix('operacoes')->name('operations.')->group(function () {
        Route::get('/envios-e-retiradas', ShippingSettings::class)->name('shipping');
    });

    // --- LAYOUT E APARÊNCIA ---
    Route::prefix('layout')->name('layout.')->group(function () {
        Route::get('/identidade-visual', VisualIdentity::class)->name('visual-identity');
    });

    // --- LOGS ---
    Route::get('/logs', function ($slug = null) {
        $slug = $slug ?? request('slug') ?? optional(auth('store_user')->user()->store)->url_slug;
        return view('livewire.store.dashboard.logs', ['slug' => $slug]);
    })->name('logs');

    // --- NOVIDADES (LISTA) ---
    Route::get('/novidades', function ($slug = null) {
        $slug = $slug ?? request('slug') ?? optional(auth('store_user')->user()->store)->url_slug;
        return view('livewire.store.dashboard.novidades', compact('slug'));
    })->name('novidades');

    // --- NOVIDADES (DETALHE + MARCAR COMO LIDO) ---
    Route::get('/novidades/{changelog_slug}', function ($changelog_slug, $slug = null) {
        $slug = $slug ?? request()->route('slug') ?? request('slug') ?? optional(auth('store_user')->user()->store)->url_slug;
        
        $changelog = \App\Models\Changelog::where('slug', $changelog_slug)
            ->where('is_published', true)
            ->firstOrFail();
        
        $user = auth('store_user')->user();
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

    // --- GRUPO DINÂMICO DE JOGOS E ESTOQUE (SEMPRE NO FINAL) ---
    Route::prefix('{game_slug}')->where(['game_slug' => '[a-zA-Z0-9\-_]+'])->group(function () {
        Route::prefix('estoque')->name('stock.')->group(function () {
            Route::get('/', ManageInventory::class)->name('index');
            Route::get('/carta/{conceptSlug}', ManageSingleCard::class)->name('manage-card');
        });
    });

});