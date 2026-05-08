<?php

namespace App\Livewire\Lobby\Orders;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    public function render()
    {
        // Busca os pedidos do jogador logado, trazendo as cartas, fretes e as lojas junto
        $orders = Order::with(['items.store', 'shippings.store'])
            ->where('player_user_id', Auth::guard('player')->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.lobby.orders.index', [
            'orders' => $orders
        ])->layout('layouts.app'); // Ajuste se o seu layout principal tiver outro nome
    }
}