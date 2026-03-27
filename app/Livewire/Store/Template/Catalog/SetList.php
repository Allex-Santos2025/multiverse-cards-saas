<?php

namespace App\Livewire\Store\Template\Catalog;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Store;
use App\Models\Game;
use App\Models\Set;

class SetList extends Component
{
    use WithPagination;

    // Variáveis da Rota
    public $slug;
    public $gameSlug;

    // Variáveis pré-carregadas no mount
    public $loja;
    public $game;
    public $gameId;

    // Variáveis de Filtro e Ordenação
    public $sortOrder = 'release_desc';
    public $activeLetter = null;

    public function mount($slug, $gameSlug)
    {
        // 1. Guarda a loja (com o visual pro Header não quebrar)
        $this->slug = $slug;
        $this->loja = Store::with('visual')->where('url_slug', $slug)->firstOrFail();

        // 2. Guarda o Jogo pela URL (slug) e já salva o objeto e o ID
        $this->gameSlug = $gameSlug;
        $this->game = Game::where('url_slug', $gameSlug)->firstOrFail();
        $this->gameId = $this->game->id;
    }

    public function render()
    {
        // Removemos as consultas redundantes. 
        // Usamos $this->loja e $this->gameId que já foram carregados no mount().

        $query = Set::where('game_id', $this->gameId)
            ->whereHas('stockItems', function ($q) {
                $q->where('store_id', $this->loja->id);
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

        // Retornando com todas as variáveis que a sua Blade original precisa
        return view('livewire.store.template.catalog.set-list', [
            'loja' => $this->loja,
            'game' => $this->game,
            'groupedSets' => $groupedSets,
            'viewMode' => $viewMode,
            'alphabet' => range('A', 'Z')
        ])->layout('layouts.template', ['loja' => $this->loja]); 
    }
}