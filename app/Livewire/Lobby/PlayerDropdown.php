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
        Auth::guard('player')->logout();
        
        $currentUrl = url()->previous();

        // Verifica se o usuário estava em uma área protegida (como /lobby, /perfil, /painel)
        if (str_contains($currentUrl, '/lobby')) {
            // Se estiver no contexto de um Lojista, expulsa para a vitrine daquela loja específica
            if ($this->loja) {
                return redirect()->to('/' . $this->loja->url_slug); 
            }
            // Se estiver no Marketplace Global, expulsa para a home principal
            return redirect()->to('/');
        }
        
        // Se for qualquer outra página pública, apenas recarrega onde ele está
        return redirect()->to($currentUrl);
    }

    public function render()
    {
        return view('livewire.lobby.player-dropdown');
    }
}