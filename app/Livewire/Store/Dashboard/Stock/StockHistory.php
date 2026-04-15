<?php

namespace App\Livewire\Store\Dashboard\Stock;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Store;
use App\Models\StoreStockSnapshot;

class StockHistory extends Component
{
    use WithPagination;

    public $slug;
    public $loja;
    public $selectedGames = []; // Controla os filtros ativos na tela

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->loja = Store::where('url_slug', $slug)->firstOrFail();
        // Já entra na tela com todos os jogos mapeados e selecionados
        $this->selectedGames = $this->getAllGames();
    }

    // Busca cega no histórico para achar qualquer jogo que já tenha passado pela loja
    public function getAllGames()
    {
        $snapshots = StoreStockSnapshot::where('store_id', $this->loja->id)->get(['game_breakdown']);
        $games = [];
        
        foreach ($snapshots as $snap) {
            $breakdown = is_string($snap->game_breakdown) ? json_decode($snap->game_breakdown, true) : $snap->game_breakdown;
            if (is_array($breakdown)) {
                foreach (array_keys($breakdown) as $gameName) {
                    $games[$gameName] = true;
                }
            }
        }
        
        $gameNames = array_keys($games);
        sort($gameNames);
        return $gameNames;
    }

    // Liga/Desliga os jogos na tela (Gráfico e Tabela)
    public function toggleGame($game)
    {
        if (in_array($game, $this->selectedGames)) {
            $this->selectedGames = array_diff($this->selectedGames, [$game]);
        } else {
            $this->selectedGames[] = $game;
        }
    }

    public function render()
    {
        $historico = StoreStockSnapshot::where('store_id', $this->loja->id)
            ->orderBy('snapshot_date', 'desc')
            ->paginate(30);

        return view('livewire.store.dashboard.stock.stock-history', [
            'historico' => $historico,
            'availableGames' => $this->getAllGames()
        ])
        ->extends('layouts.dashboard') 
        ->section('content'); 
    }
}