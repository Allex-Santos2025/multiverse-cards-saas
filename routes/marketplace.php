<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StoreUser;
use App\Models\PlayerUser;
use App\Http\Controllers\Auth\SocialController;

// Componentes
use App\Livewire\Marketplace\Magic\MagicHomeMarketplace;
use App\Livewire\Marketplace\Plans;
use App\Livewire\Marketplace\Events;
use App\Livewire\Store\Register; 
use App\Livewire\Store\PlayerRegisterWizard;

/*
|--------------------------------------------------------------------------
| MARKETPLACE & GERAL
|--------------------------------------------------------------------------
*/

Route::get('/', function () { return view('home'); })->name('home');
Route::get('/planos', Plans::class)->name('plans');
Route::get('/registro/jogador', PlayerRegisterWizard::class)->name('registro.jogador');
Route::get('/registro/{plan?}', Register::class)->name('register.store');
Route::get('/eventos', Events::class)->name('events.index');

// REMOVIDO: A rota /carrinho solta foi deletada daqui para evitar acesso sem contexto.

// GRUPO DE MARKETPLACE ESPECÍFICO
Route::prefix('marketplace/{game_slug}')->group(function () {
    // Home do Jogo
    Route::get('/', function($game_slug) {
        if($game_slug === 'magic') return app()->make(MagicHomeMarketplace::class)->__invoke();
        abort(404);
    })->name('marketplace.game.home');

    // ROTA DO CARRINHO CONTEXTUALIZADA
    // Agora o carrinho só abre se houver um jogo na URL (ex: /marketplace/magic/carrinho)
    Route::get('/carrinho', \App\Livewire\Lobby\Cart\Index::class)->name('game.cart.index');
});

// Retrocompatibilidade para a rota fixa do Magic
Route::get('marketplace/magic', MagicHomeMarketplace::class)->name('marketplace.magic.home');


// LOGIN SOCIAL
Route::get('auth/{provider}', [SocialController::class, 'redirectToProvider'])->name('social.login');
Route::get('auth/{provider}/callback', [SocialController::class, 'handleProviderCallback']);

// AGUARDE JOGADOR
Route::get('/jogador/{nickname}/aguarde', function ($nickname) {
    $user = DB::table('player_users')->where('nickname', $nickname)->first();
    return $user ? view('livewire.placeholder-player', ['user' => $user]) : redirect('/');
})->name('player.wait');

// VERIFICAÇÃO DE E-MAIL
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = PlayerUser::find($id);
    if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        $user = StoreUser::find($id);
    }
    if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return (!PlayerUser::find($id) && !StoreUser::find($id)) ? abort(404) : abort(403);
    }
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }
    if ($user instanceof PlayerUser) {
        return redirect()->route('player.wait', ['nickname' => $user->nickname]);
    }
    $store = DB::table('stores')->where('owner_user_id', $user->id)->first();
    return redirect('/loja/' . ($store->url_slug ?? 'erro') . '/login');
})->name('verification.verify');