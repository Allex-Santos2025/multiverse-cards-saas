<?php

namespace App\Livewire\Store\Template;

use Livewire\Component;
use App\Models\Store;
use App\Models\StockItem; 

class Home extends Component
{
    public $loja;

    public function mount($slug)
    {
        $this->loja = Store::where('url_slug', $slug)->firstOrFail();
    }

    public function render()
    {
        // Alterado para as colunas reais do seu banco
        $ultimasAdicoes = StockItem::with(['catalogPrint.concept']) // <-- Substitua 'catalogPrint' pelo nome real da sua relação no Model se for diferente (ex: 'card', 'product')
            ->where('store_id', $this->loja->id)
            ->where('quantity', '>', 0) 
            ->whereNotNull('catalog_print_id')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('livewire.store.template.home', [
            'ultimasAdicoes' => $ultimasAdicoes
        ])->layout('layouts.template', ['loja' => $this->loja]); // <-- A MÁGICA ACONTECE AQUI 
    }
}