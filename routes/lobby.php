<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROTAS DO LOBBY (PLAYER_USER)
|--------------------------------------------------------------------------
| Os prefixos e nomes (lobby., game.lobby., store.lobby.) são injetados pelo web.php.
*/

Route::get('/{secao?}', \App\Livewire\Lobby\LobbyIndex::class)->name('index');
