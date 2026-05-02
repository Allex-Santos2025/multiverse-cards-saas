<?php

namespace App\Livewire\Lobby;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Carteira extends Component
{
    public $balance = 0.00;
    public $pix_key;

    // Protótipo do Extrato
    public $movimentacoes = [
        [
            'id' => 101,
            'data' => '23/04/2026',
            'descricao' => "Crédito Buylist (Venda #BUY-9921)",
            'loja' => 'Olho de Leão',
            'valor' => 450.00,
            'tipo' => 'entrada', // entrada ou saida
            'status' => 'Concluído'
        ],
        [
            'id' => 102,
            'data' => '22/04/2026',
            'descricao' => "Compra de Cartas (Pedido #8892-A)",
            'loja' => "Dragon's Den",
            'valor' => 1250.00,
            'tipo' => 'saida',
            'status' => 'Concluído'
        ],
        [
            'id' => 103,
            'data' => '20/04/2026',
            'descricao' => "Bônus Indicação (Amigo: Bruno Silva)",
            'loja' => "Versus Global",
            'valor' => 15.00,
            'tipo' => 'entrada',
            'status' => 'Concluído'
        ],
        [
            'id' => 104,
            'data' => '18/04/2026',
            'descricao' => "Saque Solicitado via PIX",
            'loja' => "Carteira",
            'valor' => 345.00,
            'tipo' => 'saida',
            'status' => 'Processando'
        ]
    ];

    public function mount()
    {
        $player = Auth::guard('player')->user();
        if ($player) {
            $this->balance = $player->balance ?? 150.00;
            $this->pix_key = $player->pix_key ?? '';
        }
    }

    public function render()
    {
        return view('livewire.lobby.carteira');
    }
}