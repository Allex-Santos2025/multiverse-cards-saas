<?php

namespace App\Livewire\Lobby;

use Livewire\Component;

class MeusPedidos extends Component
{
    public $filtroAtivo = 'todos';

    // Dados fictícios baseados no seu design
    public $pedidos = [
        [
            'id' => 1,
            'loja' => "Dragon's Den",
            'loja_cor' => 'bg-slate-900',
            'loja_sigla' => 'DRAGON',
            'codigo' => '#8892-A',
            'data' => '09/12/2025',
            'status_texto' => 'Em Trânsito',
            'status_cor' => 'text-blue-600',
            'barra_cor' => 'bg-blue-500',
            'progresso' => '70%',
            'info_extra_1' => 'Previsão: 12/12',
            'info_extra_2' => 'Correios: PJ123456789BR',
            'total' => 1250.00,
            'qtd_itens' => 45,
            'itens' => [
                ['qtd' => 4, 'nome' => 'Gaea\'s Cradle', 'edicao' => 'Urza\'s Saga', 'condicao' => 'NM', 'preco' => 312.50]
            ]
        ],
        [
            'id' => 2,
            'loja' => "Mana Leak Store",
            'loja_cor' => 'bg-purple-600',
            'loja_sigla' => 'MANA',
            'codigo' => '#8892-B',
            'data' => '09/12/2025',
            'status_texto' => 'Separando Pedido',
            'status_cor' => 'text-orange-500',
            'barra_cor' => 'bg-orange-400',
            'progresso' => '30%',
            'info_extra_1' => 'Status: Aguardando Coleta',
            'info_extra_2' => '',
            'total' => 45.00,
            'qtd_itens' => 2,
            'itens' => [
                ['qtd' => 1, 'nome' => 'Sol Ring', 'edicao' => 'Commander', 'condicao' => 'SP', 'preco' => 15.00],
                ['qtd' => 1, 'nome' => 'Arcane Signet', 'edicao' => 'Throne of Eldraine', 'condicao' => 'NM', 'preco' => 30.00]
            ]
        ]
    ];

    public function setFiltro($filtro)
    {
        $this->filtroAtivo = $filtro;
        // Futuramente aqui você fará a query no banco: 
        // Order::where('status', $filtro)->get();
    }

    public function render()
    {
        return view('livewire.lobby.meus-pedidos');
    }
}