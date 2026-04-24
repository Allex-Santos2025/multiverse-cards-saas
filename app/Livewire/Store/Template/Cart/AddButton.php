<?php

namespace App\Livewire\Store\Template\Cart;

use Livewire\Component;
use App\Models\CartItem;
use App\Models\StockItem;
use Illuminate\Support\Facades\Session;

class AddButton extends Component
{
    public $stockItemId;
    public $storeId;
    public $price;
    public $maxQuantity;

    public function mount(StockItem $stockItem)
    {
        $this->stockItemId = $stockItem->id;
        $this->storeId     = $stockItem->store_id;
        $this->price       = $stockItem->price; // Ou final_price, dependendo da sua regra
        $this->maxQuantity = $stockItem->quantity;
    }

    // Agora o PHP está com os ouvidos abertos para receber o número do Alpine/JS!
    public function addToCart($requestedQuantity = 1)
    {
        $quantity = (int) $requestedQuantity;

        if ($quantity > $this->maxQuantity) {
            $quantity = $this->maxQuantity;
        } elseif ($quantity < 1) {
            $quantity = 1;
        }

        $sessionId = Session::getId();
        if (!$sessionId) {
            Session::start();
            $sessionId = Session::getId();
        }

        $cartItem = CartItem::where('session_id', $sessionId)
            ->where('stock_item_id', $this->stockItemId)
            ->first();

        if ($cartItem) {
            $newQuantity = min($cartItem->quantity + $quantity, $this->maxQuantity);
            $cartItem->update([
                'quantity' => $newQuantity,
                'price'    => $this->price
            ]);
        } else {
            CartItem::create([
                'session_id'    => $sessionId,
                'user_id'       => auth()->id(),
                'store_id'      => $this->storeId,
                'stock_item_id' => $this->stockItemId,
                'quantity'      => $quantity,
                'price'         => $this->price,
            ]);
        }

        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.store.template.cart.add-button');
    }
}