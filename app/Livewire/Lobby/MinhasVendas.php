<?php

namespace App\Livewire\Lobby;

use Livewire\Component;

class MinhasVendas extends Component
{
    public $filtroAtivo = 'todos';

    public $vendas = [
        [
            'id' => 1,
            'loja' => "Olho de Leão",
            'loja_cor' => 'bg-orange-600',
            'loja_sigla' => 'OLHO',
            'codigo' => '#BUY-9921',
            'data' => '22/04/2026',
            'status_texto' => 'Em Avaliação',
            'status_cor' => 'text-orange-500',
            'barra_cor' => 'bg-orange-400',
            'progresso' => '50%',
            'info_extra_1' => 'Aguardando conferência física',
            'info_extra_2' => 'Chegada: 21/04',
            'total' => 450.00,
            'qtd_itens' => 12,
            'itens' => [
                ['qtd' => 1, 'nome' => 'Sheoldred, the Apocalypse', 'edicao' => 'Dominaria United', 'condicao' => 'NM', 'preco' => 380.00],
                ['qtd' => 11, 'nome' => 'Bulk Rares', 'edicao' => 'Várias', 'condicao' => 'LP/NM', 'preco' => 70.00]
            ]
        ]
    ];

    public function render()
    {
        return view('livewire.lobby.minhas-vendas');
    }
}