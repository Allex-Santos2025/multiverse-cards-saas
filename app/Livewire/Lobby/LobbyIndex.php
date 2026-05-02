<?php

namespace App\Livewire\Lobby;

use Livewire\Component;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

class LobbyIndex extends Component
{
    public $abaAtiva; 
    public $loja = null;
    public $isMarketplace = false;

    // Dados do Player para o Aside
    public $playerName;
    public $playerAvatar;
    public $playerCredit;

    public function mount(Request $request)
    {
        $slugLoja = $request->route('slug');
        $secaoUrl = $request->route('secao');

        // Define a aba inicial
        $this->abaAtiva = $secaoUrl ?? 'dashboard';

        // Busca os dados do Player logado para o Aside
        $this->carregarDadosPlayer();

        if ($slugLoja) {
            $this->loja = Store::with('visual')->where('url_slug', $slugLoja)->first();
            
            if (!$this->loja) {
                abort(404);
            }
            $this->isMarketplace = false;
        } else {
            $this->isMarketplace = true;
        }
    }

    public function switchAba($aba)
    {
        $this->abaAtiva = $aba;
    }

    #[On('perfil-atualizado')]
    public function carregarDadosPlayer()
    {
        $player = Auth::guard('player')->user();
        if ($player) {
            $this->playerName = $player->name;
            // Corrigido para buscar a coluna 'avatar' exata que salvamos no DadosPessoais
            $this->playerAvatar = $player->avatar; 
            $this->playerCredit = $player->balance ?? 0.00; // Saldo da carteira
        }
    }

    public function render()
    {
        $layout = $this->loja ? 'layouts.template' : 'layouts.app';

        return view('livewire.lobby.lobby-index')->layout($layout, [
            'loja' => $this->loja, 
            'isMarketplace' => $this->isMarketplace
        ]);
    }
}