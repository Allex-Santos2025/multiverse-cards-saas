<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Modelos
use App\Models\StoreUser;
use App\Models\PlayerUser;

// Componentes Livewire
use App\Livewire\Marketplace\Plans;
use App\Livewire\Marketplace\Events;
use App\Livewire\Store\Register; 
use App\Livewire\Store\PlayerRegisterWizard;

// Controllers
use App\Http\Controllers\StoreController;
use App\Http\Controllers\Auth\SocialController;

/*
|--------------------------------------------------------------------------
| 1. ROTAS DE REGISTRO E PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::get('/', function () { return view('home'); })->name('home');
Route::get('/planos', Plans::class)->name('plans');
Route::get('/registro/jogador', PlayerRegisterWizard::class)->name('registro.jogador');
Route::get('/registro/{plan?}', Register::class)->name('register.store');
// Fallback de Eventos
Route::get('/eventos', Events::class)->name('events.index');

/*
|--------------------------------------------------------------------------
| 2. VERIFICAÇÃO DE E-MAIL (O FIX DO ERRO)
|--------------------------------------------------------------------------
| Usamos Request comum para não conflitar entre Lojista e Jogador.
*/

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    // Busca o usuário. Como os IDs podem se repetir entre tabelas, 
    // o Laravel valida pelo Hash abaixo para garantir que é o cara certo.
    $user = PlayerUser::find($id) ?: StoreUser::find($id);

    if (!$user) {
        abort(404, 'Usuário não encontrado.');
    }

    // Validação manual do Hash (O que o Laravel faz por baixo dos panos)
    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Link de verificação inválido.');
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    // Redirecionamento baseado no tipo de usuário
    if ($user instanceof PlayerUser) {
        return redirect()->route('player.wait', ['nickname' => $user->nickname]);
    }

    $store = DB::table('stores')->where('owner_user_id', $user->id)->first();
    return redirect('/loja/' . ($store->url_slug ?? 'erro') . '/aguarde');
})->name('verification.verify');

/*
|--------------------------------------------------------------------------
| 3. PÁGINAS DE AGUARDE (FALLBACKS)
|--------------------------------------------------------------------------
*/

Route::get('/loja/{url_slug}/aguarde', function ($url_slug) {
    $store = DB::table('stores')->where('url_slug', $url_slug)->first();
    return $store ? view('livewire.store.placeholder', ['store' => $store]) : redirect('/');
})->name('store.wait');

Route::get('/jogador/{nickname}/aguarde', function ($nickname) {
    $user = DB::table('player_users')->where('nickname', $nickname)->first();
    return $user ? view('livewire.placeholder-player', ['user' => $user]) : redirect('/');
})->name('player.wait');

/*
|--------------------------------------------------------------------------
| 4. LOGIN SOCIAL E OUTROS
|--------------------------------------------------------------------------
*/

Route::get('auth/{provider}', [SocialController::class, 'redirectToProvider'])->name('social.login');
Route::get('auth/{provider}/callback', [SocialController::class, 'handleProviderCallback']);

/*
|--------------------------------------------------------------------------
| 5. ROTAS DINÂMICAS DE LOJA (SEMPRE POR ÚLTIMO)
|--------------------------------------------------------------------------
*/

Route::middleware(['web'])
    ->prefix('/{storeSlug}')
    ->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('store.home');
    });