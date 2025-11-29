<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\Auth\RegisterController;

// --- ROTAS DE CADASTRO CUSTOMIZADAS ---

// 1. Rota de Escolha (A Bifurcação)
Route::get('/register', [RegisterController::class, 'showRegistrationTypeForm'])->name('register');

// 2. Rota de Registro do Player (Pessoa Física / Cliente)
Route::get('/register/player', [RegisterController::class, 'showPlayerRegistrationForm'])->name('register.player');
Route::post('/register/player', [RegisterController::class, 'registerPlayer']);

// 3. Rota de Registro do Lojista (Requer criação da Loja)
Route::get('/register/store', [RegisterController::class, 'showStoreRegistrationForm'])->name('register.store');
Route::post('/register/store', [RegisterController::class, 'registerStore']);

// Rota 1: Home (Seletor de Jogos)
// Certifique-se de que a rota da home (/) está correta:
Route::get('/', [MarketplaceController::class, 'index'])->name('marketplace.home');

// Rota do Catálogo: Usa 'game' para o Model Binding, mas 'url_slug' para a URL
Route::get('/{game:url_slug}/cards', [MarketplaceController::class, 'showCatalog'])->name('marketplace.catalog');

