<?php

namespace App\Livewire\Lobby;

use Livewire\Component;
use App\Models\Store;
use Illuminate\Http\Request;

class LobbyIndex extends Component
{
    public $abaAtiva; 
    public $loja = null;
    public $isMarketplace = false;

    public function mount(Request $request)
    {
        // O Laravel busca o 'slug' independente de qual arquivo de rota chamou o componente
        $slugLoja = $request->route('slug');
        $secaoUrl = $request->route('secao');

        $this->abaAtiva = $secaoUrl ?? 'dados';

        if ($slugLoja) {
            // Se tem slug, estamos no contexto de LOJA
            $this->loja = Store::with('visual')->where('url_slug', $slugLoja)->first();
            
            if (!$this->loja) {
                abort(404);
            }
            $this->isMarketplace = false;
        } else {
            // Se não tem slug, estamos no MARKETPLACE
            $this->isMarketplace = true;
        }
    }

    public function switchAba($aba)
    {
        $this->abaAtiva = $aba;
        
        // Opcional: Atualiza a URL sem dar refresh para manter o histórico do navegador
        $prefix = $this->loja ? "/loja/{$this->loja->url_slug}/lobby" : "/lobby";
        return $this->redirect("{$prefix}/{$aba}", navigate: true);
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