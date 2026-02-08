<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Livewire\Store\Dashboard\Stock\ManageInventory; 

/*
|--------------------------------------------------------------------------
| DASHBOARD ROUTES
|--------------------------------------------------------------------------
| Contexto: /loja/{slug}/dashboard
|--------------------------------------------------------------------------
*/

// 1. A ROTA PRINCIPAL (Home do Dashboard)
// Voltamos para Closure (Função) para evitar o erro "Multiple Root Elements" do Livewire
Route::get('/', function ($slug) {
    
    // A. Validação da Loja
    $store = DB::table('stores')->where('url_slug', $slug)->first();
    if (!$store) return redirect()->route('home');

    // B. Lógica dos Botões (Magic/Pokemon)
    // Se não vier na URL, assume 'magic'.
    $gameSlug = request()->query('game_slug', 'magic');
    $inactiveSlug = ($gameSlug === 'magic') ? 'pokemon' : 'magic';

    // C. Paginação Vazia (Para não quebrar o @forelse da View)
    $items = new LengthAwarePaginator([], 0, 15);

    // D. Retorna a View direto (assim o @extends funciona perfeitamente)
    return view('livewire.store.dashboard.index', [
        'slug' => $slug,
        'gameSlug' => $gameSlug,
        'inactiveSlug' => $inactiveSlug,
        'items' => $items
    ]);

})->name('store.dashboard'); 


// 2. O RESTO DAS ROTAS (Mantidas originais)
Route::name('store.dashboard.')->group(function () {

    // --- GRUPO DE JOGOS E ESTOQUE ---
    Route::prefix('{game_slug}')->group(function () {
        Route::prefix('estoque')->name('stock.')->group(function () {
            Route::get('/', ManageInventory::class)->name('index');
        });
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

});