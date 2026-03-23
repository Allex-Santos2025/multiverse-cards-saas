<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Livewire\Store\LoginLojista;
use App\Livewire\Store\Template\Home;
// 👇 1. IMPORTAMOS A NOSSA NOVA CLASSE AQUI
use App\Livewire\Store\Template\Catalog\SetList; 

/*
|--------------------------------------------------------------------------
| STORE FRONT
| O prefixo '/loja/{slug}' JÁ VEM do web.php
|--------------------------------------------------------------------------
*/

// Rota final: /loja/{slug}/login
Route::get('/login', LoginLojista::class)->name('loja.login');

// Rota final: /loja/{slug}/logout
Route::post('/logout', function () {
    $user = auth('store_user')->user();
    $slug = ($user && $user->store) ? $user->store->url_slug : 'admin';
    
    // Tratamento de fallback para admin
    if($slug === 'admin') {
        $slug = collect(explode('/', request()->header('referer')))->after('loja')->first() ?? 'admin';
    }

    auth('store_user')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('loja.login', ['slug' => $slug]);
})->name('logout');

// Rota final: /loja/{slug}/aguarde
Route::get('/aguarde', function ($slug) {
    $store = DB::table('stores')->where('url_slug', $slug)->first();
    return $store ? view('livewire.store.placeholder', ['store' => $store]) : redirect('/');
})->name('store.wait');

// Rota final: /loja/{slug}/ (A Home da Loja)
Route::get('/', Home::class)->name('store.view');


// 👇 2. NOSSA NOVA ROTA DE CATÁLOGO DE SETS
// Rota final: /loja/{slug}/{gameSlug}/sets
Route::get('/{gameId}/sets', SetList::class)->name('store.catalog.sets');