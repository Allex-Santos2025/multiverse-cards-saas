<?php

namespace App\Livewire\Store\Dashboard;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url; // Importante para a URL mudar sozinha

class Index extends Component
{
    // 1. Variável que o HTML está pedindo e dando erro
    public $slug;

    // 2. Variável que controla qual jogo está ativo na URL
    #[Url(as: 'game_slug', keep: true)]
    public $gameSlug = 'magic';

    public function mount($slug)
    {
        // O Laravel pega o {slug} da rota e joga aqui
        $this->slug = $slug;
    }

    #[Layout('layouts.dashboard')] // Define o layout correto
    public function render()
    {
        return view('livewire.store.dashboard.index', [
            // Passamos uma lista vazia só pro foreach do HTML não quebrar
            'items' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
            'inactiveSlug' => ($this->gameSlug === 'magic') ? 'pokemon' : 'magic',
        ]);
    }
}