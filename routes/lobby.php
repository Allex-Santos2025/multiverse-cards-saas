<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROTAS DO LOBBY (PLAYER_USER)
|--------------------------------------------------------------------------
| Este arquivo é um template. Os prefixos e nomes de rota (lobby., 
| game.lobby., store.lobby.) são injetados pelo web.php.
*/

Route::get('/{secao?}', \App\Livewire\Lobby\LobbyIndex::class)->name('index');