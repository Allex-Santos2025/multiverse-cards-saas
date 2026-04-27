<?php

namespace App\Livewire\Lobby\Cart;

use Livewire\Component;
use App\Models\Store;
use App\Models\CartItem;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    // Apenas propriedades simples que precisam persistir
    public $cep = '';
    public $selectedShipping = [];
    public $descontoGeral = 0;
    
    // Contexto
    public $loja = null;
    public $isMarketplace = false;

    public function mount($slug = null)
    {
        if ($slug) {
            $this->loja = Store::with('visual')->where('url_slug', $slug)->first();
            if (!$this->loja) abort(404);
            $this->isMarketplace = false;
        } else {
            $this->isMarketplace = true;
        }
    }

    public function incrementQuantity($itemId)
    {
        $cartItem = CartItem::with('stockItem')->where('session_id', Session::getId())->find($itemId);
        if ($cartItem) {
            $estoqueMaximo = $cartItem->stockItem->quantity ?? 1;
            if ($cartItem->quantity < $estoqueMaximo) {
                $cartItem->increment('quantity');
                $this->dispatch('cart-updated'); // Atualiza a bolinha do header
            }
        }
    }

    public function decrementQuantity($itemId)
    {
        $cartItem = CartItem::where('session_id', Session::getId())->find($itemId);
        if ($cartItem && $cartItem->quantity > 1) {
            $cartItem->decrement('quantity');
            $this->dispatch('cart-updated');
        }
    }

    public function removeItem($itemId)
    {
        $cartItem = CartItem::where('session_id', Session::getId())->find($itemId);
        if ($cartItem) {
            $cartItem->delete();
            $this->dispatch('cart-updated');
        }
    }

    public function render()
    {
        $sessionId = Session::getId();
        $items = CartItem::with(['stockItem.catalogPrint.concept', 'stockItem.catalogPrint.set', 'stockItem.store']) 
            ->where('session_id', $sessionId)
            ->get();

        // A MÁGICA PARA RESOLVER O VAZAMENTO:
        // Se estivermos dentro de uma loja específica, descartamos os itens das outras lojas da coleção.
        if ($this->loja) {
            $items = $items->filter(function ($item) {
                return ($item->stockItem->store->id ?? null) === $this->loja->id;
            });
        }

        $cartByStore = [];
        $totalItems = 0;
        $subtotalGeral = 0;
        $fretesGeral = 0;

        // Processamento dos itens (Lógica do Dropdown integrada)
        $items->each(function ($item) use (&$cartByStore, &$totalItems, &$subtotalGeral) {
            $stock = $item->stockItem;
            $print = $stock->catalogPrint ?? null;
            
            if ($print) {
                $nome = $print->printed_name ?? $print->concept->name ?? 'Carta Desconhecida';
                if (str_contains($print->type_line ?? '', 'Basic Land')) {
                    $nome .= ' (#' . ($print->collector_number ?? '') . ')';
                }
                $item->nome_localizado = $nome;

                $caminhoImagem = $print->image_url ?? $print->image_path ?? $print->concept->image_url ?? $print->concept->image_path ?? 'https://placehold.co/100x140';
                $item->imagem_final = filter_var($caminhoImagem, FILTER_VALIDATE_URL) ? $caminhoImagem : asset($caminhoImagem);
                
                $item->condicao = strtoupper($stock->condition ?? 'NM');
                $item->idioma = strtoupper($stock->language ?? $print->language_code ?? 'PT');
                $item->edicao = strtoupper($print->set->code ?? 'N/A');
                $item->estoque_maximo = $stock->quantity ?? 1;
            }

            $store = $stock->store ?? null;
            $storeId = $store ? $store->id : 0;
            
            if (!isset($cartByStore[$storeId])) {
                $cartByStore[$storeId] = ['store' => $store, 'items' => collect(), 'total' => 0];
            }

            $cartByStore[$storeId]['items']->push($item);
            $cartByStore[$storeId]['total'] += ($item->price * $item->quantity);
            $totalItems += $item->quantity;
            $subtotalGeral += ($item->price * $item->quantity);
        });

        // Cálculo de fretes
        foreach ($this->selectedShipping as $storeId => $tipo) {
            if (isset($cartByStore[$storeId])) {
                $valorFrete = ($tipo === 'sedex') ? 24.90 : (($tipo === 'pac') ? 12.50 : 0);
                $fretesGeral += $valorFrete;
                $cartByStore[$storeId]['total'] += $valorFrete;
            }
        }

        $totalGeral = $subtotalGeral + $fretesGeral - $this->descontoGeral;

        $layout = $this->loja ? 'layouts.template' : 'layouts.app';
        
        return view('livewire.lobby.cart.index', [
            'cartByStore' => $cartByStore,
            'totalItems' => $totalItems,
            'subtotalGeral' => $subtotalGeral,
            'fretesGeral' => $fretesGeral,
            'totalGeral' => $totalGeral,
        ])->layout($layout, ['loja' => $this->loja, 'isMarketplace' => $this->isMarketplace]);
    }
}