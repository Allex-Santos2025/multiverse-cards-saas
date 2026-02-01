<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Modelos
use App\Models\StoreUser;
use App\Models\PlayerUser;

// Componentes Livewire
use App\Livewire\Marketplace\Magic\MagicHomeMarketplace;
use App\Livewire\Marketplace\Plans;
use App\Livewire\Marketplace\Events;
use App\Livewire\Store\Register; 
use App\Livewire\Store\PlayerRegisterWizard;
use App\Livewire\Store\LoginLojista;
use App\Http\Livewire\Store\Template\Comingsong;


// Controllers
use App\Http\Controllers\StoreController;
use App\Http\Controllers\Auth\SocialController;

Route::get('/', function () { return view('home'); })->name('home');
Route::get('/planos', Plans::class)->name('plans');
Route::get('/registro/jogador', PlayerRegisterWizard::class)->name('registro.jogador');
Route::get('/registro/{plan?}', Register::class)->name('register.store');
Route::get('/eventos', Events::class)->name('events.index');

Route::prefix('marketplace')->group(function () {
    Route::get('/magic', MagicHomeMarketplace::class)->name('marketplace.magic.home');
});

Route::get('/loja/{slug}', function () {
    return view('livewire.store.template.coming-soon');
})->name('store.view');
// LOGS DO SISTEMA (Dentro do Dashboard)
Route::get('/loja/{slug}/dashboard/logs', function ($slug) {
    return view('livewire.store.dashboard.logs', ['slug' => $slug]);
})->name('store.dashboard.logs')->middleware('auth:store_user');

// Rota da Listagem (HUB)
Route::get('/loja/{slug}/dashboard/novidades', function($slug) {
    return view('livewire.store.dashboard.novidades', compact('slug'));
})->name('store.dashboard.novidades')->middleware('auth:store_user'); // MUDADO PARA O NOME QUE O MENU PEDE

// Rota do Detalhe (Markdown + Lógica de Leitura)
Route::get('/loja/{slug}/dashboard/novidades/{changelog_slug}', function($slug, $changelog_slug) {
    // 1. Busca a novidade
    $changelog = \App\Models\Changelog::where('slug', $changelog_slug)
        ->where('is_published', true)
        ->firstOrFail();

    // 2. DEDUÇÃO: Registra que o usuário logado leu esta novidade
    $user = auth('store_user')->user();
    if ($user) {
        \App\Models\ChangelogUserRead::updateOrInsert(
            ['store_user_id' => $user->id, 'changelog_id' => $changelog->id],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    // 3. Abre a tela de leitura (que vamos criar abaixo)
    return view('livewire.store.dashboard.changelog-detail', compact('changelog', 'slug'));
})->name('store.dashboard.novidades.show')->middleware('auth:store_user');


/*
|/*
|--------------------------------------------------------------------------
| VERIFICAÇÃO DE E-MAIL (CORRIGIDA PARA COLISÃO DE IDs)
|--------------------------------------------------------------------------
*/
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    // Tentamos encontrar e validar primeiro como Player
    $user = PlayerUser::find($id);
    
    // Se não for o player ou o hash não bater, tentamos como Lojista
    if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        $user = StoreUser::find($id);
    }

    // Se não achou em nenhuma tabela ou o hash continua não batendo, aí sim é erro
    if (!$user || !hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        // Se o registro nem existe em nenhuma tabela, 404. Se existe mas o hash é torto, 403.
        return (!PlayerUser::find($id) && !StoreUser::find($id)) ? abort(404) : abort(403);
    }

    // Daqui para baixo a validação foi um sucesso
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    if ($user instanceof PlayerUser) {
        return redirect()->route('player.wait', ['nickname' => $user->nickname]);
    }

    $store = DB::table('stores')->where('owner_user_id', $user->id)->first();
    return redirect('/loja/' . ($store->url_slug ?? 'erro') . '/login');
})->name('verification.verify');
/*
|--------------------------------------------------------------------------
| ÁREA DO LOJISTA
|--------------------------------------------------------------------------
*/
// Login (Público para quem tem o link da loja)
Route::get('/loja/{slug}/login', LoginLojista::class)->name('loja.login');

// DASHBOARD REAL (Protegido pelo Guard de Lojista)
Route::get('/loja/{slug}/dashboard', function ($slug) {
    // Validamos se a loja existe pelo slug
    $store = DB::table('stores')->where('url_slug', $slug)->first();
    
    if (!$store) {
        return redirect()->route('home');
    }

    return view('livewire.store.dashboard.index'); 
})->name('store.dashboard')->middleware('auth:store_user');

// Rota de Espera (Ainda existe caso você precise dela em algum fluxo)
Route::get('/loja/{slug}/aguarde', function ($slug) {
    $store = DB::table('stores')->where('url_slug', $slug)->first();
    return $store ? view('livewire.store.placeholder', ['store' => $store]) : redirect('/');
})->name('store.wait');

// Ajuste na rota de Logout
Route::post('/logout', function () {
    // 1. Identificamos o usuário pelo guard do lojista
    $user = auth('store_user')->user();

    // 2. Buscamos o slug real da loja dele
    // Usamos o seu relacionamento 'store' (no singular)
    $slug = ($user && $user->store) ? $user->store->url_slug : 'admin';

    // 3. Se por algum motivo o slug falhou mas ele veio de uma URL com slug, 
    // podemos tentar pegar pela URL como plano C
    if($slug === 'admin') {
        $slug = collect(explode('/', request()->header('referer')))->after('loja')->first() ?? 'admin';
    }

    // 4. Executamos o logout no guard correto
    auth('store_user')->logout();

    // 5. Destruímos a sessão e limpamos os tokens
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    // 6. Redireciona para o login da loja específica
    return redirect()->route('loja.login', ['slug' => $slug]);
})->name('logout');

/*
|--------------------------------------------------------------------------
| JOGADOR E SOCIAL
|--------------------------------------------------------------------------
*/
Route::get('/jogador/{nickname}/aguarde', function ($nickname) {
    $user = DB::table('player_users')->where('nickname', $nickname)->first();
    return $user ? view('livewire.placeholder-player', ['user' => $user]) : redirect('/');
})->name('player.wait');

Route::get('auth/{provider}', [SocialController::class, 'redirectToProvider'])->name('social.login');
Route::get('auth/{provider}/callback', [SocialController::class, 'handleProviderCallback']);

/*
|--------------------------------------------------------------------------
| ROTAS DINÂMICAS DE LOJA
|--------------------------------------------------------------------------
*/
Route::middleware(['web'])
    ->prefix('/{storeSlug}')
    ->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('store.home');
    });