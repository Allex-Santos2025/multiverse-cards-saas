<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROTAS DO LOBBY (PLAYER_USER)
|--------------------------------------------------------------------------
| Aqui ficam todas as rotas exclusivas da área do jogador.
| O prefixo '/lobby' e o middleware 'auth:player_user' já são 
| aplicados automaticamente pelo web.php.
*/

// Rota principal do Lobby. 
// Passamos a {secao?} como opcional para que o mesmo componente Livewire 
// gerencie a troca de telas (dados, compras, colecoes, decks) sem recarregar a página.
//Route::get('/{secao?}', \App\Livewire\Lobby\LobbyIndex::class)->name('lobby.index');