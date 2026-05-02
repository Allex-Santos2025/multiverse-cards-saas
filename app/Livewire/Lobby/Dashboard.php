<?php

namespace App\Livewire\Lobby;

use Livewire\Component;

class Dashboard extends Component
{
    public $loja;

    public function mount($loja = null)
    {
        // Recebe a loja do LobbyIndex. 
        // Se for null, sabemos que o player está no escopo global (Marketplace)
        $this->loja = $loja;
    }

    public function render()
    {
        return view('livewire.lobby.dashboard');
    }
}