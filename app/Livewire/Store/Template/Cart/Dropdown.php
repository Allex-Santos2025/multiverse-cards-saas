<?php

namespace App\Livewire\Store\Template\Cart;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\CartItem;
use App\Models\Store;
use Illuminate\Support\Facades\Session;

class Dropdown extends Component
{
    public $loja; 
    public $cartTrigger = 0; // O gatilho anti-F5!

    // Ajuste 1: Tipagem '?Store' e '= null' para permitir que o carrinho rode no Marketplace sem quebrar
    public function mount($loja = null)
    {
        $this->loja = $loja;
    }

    // Ouve o botão e puxa o gatilho
    #[On('cart-updated')]
    public function updateCart()
    {
        $this->cartTrigger++; 
    }

    public function render()
    {
        $sessionId = Session::getId();
        
        // Ajuste 2: Começamos a query sem finalizar o 'get()' ainda
        $query = CartItem::with(['stockItem.catalogPrint.concept', 'stockItem.catalogPrint.set']) 
            ->where('session_id', $sessionId);

        // Se uma loja foi passada (estamos na loja do lojista), filtramos os itens pelo dono do estoque
        if ($this->loja && $this->loja->id) {
            $query->whereHas('stockItem', function ($q) {
                $q->where('store_id', $this->loja->id);
            });
        }

        // Agora sim buscamos os resultados
        $items = $query->get();

        // A inteligência exata que funcionava antes
        $items->transform(function ($item) {
            $print = $item->stockItem->catalogPrint ?? null;
            
            if ($print) {
                $nome = $print->printed_name ?? $print->concept->name ?? 'Carta Desconhecida';
                
                if (str_contains($print->type_line ?? '', 'Basic Land')) {
                    $nome .= ' (#' . ($print->collector_number ?? '') . ')';
                }

                $item->nome_localizado = $nome;

                $caminhoImagem = $print->image_url ?? $print->image_path ?? $print->concept->image_url ?? $print->concept->image_path ?? 'https://placehold.co/100x140/eeeeee/999999?text=Sem+Imagem';
                $item->imagem_final = filter_var($caminhoImagem, FILTER_VALIDATE_URL) ? $caminhoImagem : asset($caminhoImagem);
            } else {
                $item->nome_localizado = 'Item Indisponível';
                $item->imagem_final = 'https://placehold.co/100x140/eeeeee/999999?text=Erro';
            }

            return $item;
        });

        $totalQuantity = $items->sum('quantity');
        $subtotal = $items->sum(function($item) {
            return $item->quantity * $item->price;
        });

        // Entregamos tudo pronto para a Blade
        return view('livewire.store.template.cart.dropdown', [
            'cartItems' => $items,
            'totalQuantity' => $totalQuantity,
            'subtotal' => $subtotal
        ]);
    }
}