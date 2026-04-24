<?php

namespace App\Livewire\Lobby;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;

class PlayerDropdown extends Component
{
    public $isMarketplace = true;
    public $loja = null;

    public function mount()
    {
        // Lê o contexto gravado na sessão pelo TrackStoreContext
        $contexto = session('contexto_loja', 'versus');

        if ($contexto !== 'versus') {
            // Se estiver numa loja, busca os dados para o visual White Label
            $this->loja = Store::where('url_slug', $contexto)->first();
            $this->isMarketplace = false;
        }
    }

    public function logout()
    {
        Auth::guard('player_user')->logout();
        
        // Limpa a memória de onde o jogador estava e joga para a home principal
        session()->forget('contexto_loja');
        return redirect()->to('/');
    }

    public function render()
    {
        return view('livewire.lobby.player-dropdown');
    }
}