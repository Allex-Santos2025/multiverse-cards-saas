<?php

namespace App\Livewire\Store\Template\Catalog;

use Livewire\Component;
use App\Models\Set;
use App\Models\Game;
use App\Models\Store;

class SetList extends Component
{
    public $gameId;
    public $lojaSlug; // Atualizado para o padrão
    public $sortOrder = 'release_desc'; 
    public $activeLetter = null; 
    public $ptBrEnabled = false; 

    public function mount($slug, $gameId)
    {
        $this->lojaSlug = $slug;
        $this->gameId = $gameId;
    }

    public function filterByLetter($letter)
    {
        if ($this->activeLetter === $letter) {
            $this->activeLetter = null;
        } else {
            $this->activeLetter = $letter;
        }
    }

    public function render()
    {
        // Padrão: tudo usando $loja
        $loja = Store::where('url_slug', $this->lojaSlug)->firstOrFail();
        $game = Game::findOrFail($this->gameId);

        $query = Set::where('game_id', $game->id)
            ->whereHas('stockItems', function ($q) use ($loja) {
                $q->where('store_id', $loja->id);
            });

        switch ($this->sortOrder) {
            case 'release_asc':
                $query->orderBy('released_at', 'asc');
                break;
            case 'az':
                $query->orderBy('name', 'asc');
                break;
            case 'za':
                $query->orderBy('name', 'desc');
                break;
            case 'release_desc':
            default:
                $query->orderBy('released_at', 'desc');
                break;
        }

        $rawSets = $query->get();

        $groupedSets = collect();
        $viewMode = 'timeline';

        if (in_array($this->sortOrder, ['az', 'za'])) {
            $viewMode = 'alphabetical';
            
            if ($this->activeLetter) {
                $rawSets = $rawSets->filter(function ($set) {
                    return strtoupper(substr($set->name, 0, 1)) === $this->activeLetter;
                });
            }

            $groupedSets = $rawSets->groupBy(function ($set) {
                return strtoupper(substr($set->name, 0, 1));
            });

        } else {
            $viewMode = 'timeline';
            
            $groupedSets = $rawSets->groupBy(function ($set) {
                return \Carbon\Carbon::parse($set->released_at)->format('Y');
            });
        }

        // Retornando apenas o padrão do sistema
        return view('livewire.store.template.catalog.set-list', [
            'loja' => $loja,
            'game' => $game,
            'groupedSets' => $groupedSets,
            'viewMode' => $viewMode,
            'alphabet' => range('A', 'Z')
        ])->layout('layouts.template', ['loja' => $loja]); 
    }
}