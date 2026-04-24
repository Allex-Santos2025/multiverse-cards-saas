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

    public function mount(Store $loja)
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
        
        $items = CartItem::with(['stockItem.catalogPrint.concept', 'stockItem.catalogPrint.set']) 
            ->where('session_id', $sessionId)
            ->get();

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