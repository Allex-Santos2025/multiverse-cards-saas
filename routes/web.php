<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Livewire\Marketplace\Plans;
use App\Livewire\Store\Register; // Componente de registro de lojista
use App\Http\Controllers\StoreController; // Controller para páginas de loja
use App\Models\StoreUser;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// 1. HOME (Landing Page)
Route::get('/', function () {
    return view('home');
})->name('home');

// 2. PLANOS (Página pública – Livewire)
// Usuário escolhe entre os planos Básico, Pro ou Premium
Route::get('/planos', Plans::class)->name('plans');

// 3. REGISTRO DE LOJA (Wizard – Livewire)
// Cria a conta e a loja simultaneamente.
// O nome da rota **store.register** é usado no link da página de planos.
// AGORA ACEITA O PARÂMETRO 'plan'
Route::get('/registro/{plan?}', Register::class)->name('register.store');

// ========================================================================
// ROTAS DINÂMICAS DE LOJA (FUTURO)
// ========================================================================
// Capturam acessos como "versustcg.com/magic-planet"
// Estas rotas são o núcleo do sistema, mas só funcionarão quando
// as lojas forem criadas.
Route::middleware(['web']) // Adicionar StoreResolver quando estiver pronto
    ->prefix('/{storeSlug}')
    ->group(function () {
        // Se o StoreController ainda não existir ou estiver com erro,
        // comente a linha abaixo para evitar que o site quebre.
        Route::get('/', [StoreController::class, 'index'])->name('store.home');
    });

// Rota exigida pelo Laravel para gerar o link do e-mail
// Rota de Verificação SEM exigência de estar logado
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    // Usamos o $id direto da URL, sem precisar do método ->route()
    $storeUser = StoreUser::findOrFail($id);

    // Validação do Hash
    if (! hash_equals((string) $hash, sha1($storeUser->getEmailForVerification()))) {
        abort(403, 'Link de verificação inválido.');
    }

    if (!$storeUser->hasVerifiedEmail()) {
        $storeUser->markEmailAsVerified();
    }

    // Busca a loja vinculada
    $store = DB::table('stores')->where('owner_user_id', $storeUser->id)->first();

    return redirect('/loja/' . $store->url_slug . '/aguarde')->with('status', 'E-mail verificado com sucesso!');
})->name('verification.verify');

Route::get('/loja/{url_slug}/aguarde', function ($url_slug) {
    // Busca a loja no banco para garantir que ela existe e pegar os dados
    $store = DB::table('stores')->where('url_slug', $url_slug)->first();

    if (!$store) {
        return redirect('/');
    }

    // Carrega o arquivo que está em: resources/views/livewire/store/placeholder.blade.php
    return view('livewire.store.placeholder', ['store' => $store]);
});

// OBS: As rotas do painel administrativo (/admin ou /app) são gerenciadas
// automaticamente pelo Filament e não precisam ser declaradas aqui.
