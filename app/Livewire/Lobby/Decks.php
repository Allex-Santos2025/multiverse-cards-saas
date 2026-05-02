<?php

namespace App\Livewire\Lobby;

use Livewire\Component;

class Decks extends Component
{
    // A prateleira de decks do player
    public $meusDecks = [
        [
            'id' => 1,
            'nome' => 'Krenko, Mob Boss',
            'jogo' => 'Magic: The Gathering',
            'jogo_cor' => 'bg-amber-600',
            'formato' => 'Commander',
            'cores' => ['bg-red-500'], // Representação de mana Red
            'vitorias' => 24,
            'derrotas' => 12,
            'cartas_atuais' => 100,
            'cartas_total' => 100,
            'status' => 'Legal',
            'status_cor' => 'text-emerald-500 bg-emerald-50'
        ],
        [
            'id' => 2,
            'nome' => 'Charizard ex Turbo',
            'jogo' => 'Pokémon TCG',
            'jogo_cor' => 'bg-blue-600',
            'formato' => 'Standard',
            'cores' => ['bg-orange-500', 'bg-slate-800'], // Representação de tipos (Fire/Dark)
            'vitorias' => 15,
            'derrotas' => 8,
            'cartas_atuais' => 57,
            'cartas_total' => 60,
            'status' => 'Faltam 3 Cartas',
            'status_cor' => 'text-red-500 bg-red-50'
        ],
        [
            'id' => 3,
            'nome' => 'Azorius Control',
            'jogo' => 'Magic: The Gathering',
            'jogo_cor' => 'bg-amber-600',
            'formato' => 'Modern',
            'cores' => ['bg-yellow-100', 'bg-blue-500'], // White/Blue
            'vitorias' => 8,
            'derrotas' => 15,
            'cartas_atuais' => 60,
            'cartas_total' => 60,
            'status' => 'Legal',
            'status_cor' => 'text-emerald-500 bg-emerald-50'
        ]
    ];

    public function render()
    {
        return view('livewire.lobby.decks');
    }
}