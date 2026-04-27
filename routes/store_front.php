<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Livewire\Store\LoginLojista;
use App\Livewire\Store\Template\Home;
use App\Livewire\Store\Template\Catalog\SetList; 
use App\Livewire\Store\Template\Catalog\SetPage;
use App\Livewire\Store\Template\Catalog\SinglePage;
use App\Livewire\Store\Template\Catalog\ProductPage;
use App\Livewire\Lobby\Cart\Index as CartIndex;

/*
|--------------------------------------------------------------------------
| STORE FRONT
| O prefixo '/loja/{slug}' JÁ VEM do web.php
|--------------------------------------------------------------------------
*/

// 1. ROTAS ESTÁTICAS E SISTEMA
Route::get('/login', LoginLojista::class)->name('loja.login');

Route::get('/nova-senha/{token}', LoginLojista::class)->name('loja.reset-password');

Route::post('/logout', function () {
    $user = auth('store_user')->user();
    $slug = ($user && $user->store) ? $user->store->url_slug : 'admin';
    
    if($slug === 'admin') {
        $slug = collect(explode('/', request()->header('referer')))->after('loja')->first() ?? 'admin';
    }

    auth('store_user')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('loja.login', ['slug' => $slug]);
})->name('logout');

Route::get('/aguarde', function ($slug) {
    $store = DB::table('stores')->where('url_slug', $slug)->first();
    return $store ? view('livewire.store.placeholder', ['store' => $store]) : redirect('/');
})->name('store.wait');

// ROTA DO CARRINHO DA LOJA (Mantida aqui conforme solicitado)
Route::get('/carrinho', CartIndex::class)->name('store.cart');

// 2. ROTA RAIZ DA LOJA
Route::get('/', Home::class)->name('store.view');

// 3. ROTAS DINÂMICAS COM CURINGA {gameSlug}
Route::get('/{gameSlug}/sets', SetList::class)->name('store.catalog.sets');
Route::get('/{gameSlug}/sets/{setCode}', SetPage::class)->name('store.catalog.set');
Route::get('/{gameSlug}/card/{conceptSlug}', ProductPage::class)->name('store.catalog.product');
Route::get('/{gameSlug}/busca', \App\Livewire\Store\Template\Catalog\SearchResults::class)->name('store.catalog.search');
Route::get('/{gameSlug}/singles', SinglePage::class)->name('store.catalog.singles');