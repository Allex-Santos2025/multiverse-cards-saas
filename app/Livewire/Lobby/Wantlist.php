<?php

namespace App\Livewire\Lobby;

use Livewire\Component;

class Wantlist extends Component
{
    // Dados fictícios baseados na regra de CONCEITO de carta
    public $itensWantlist = [
        [
            'id' => 1,
            'nome' => 'Crescimento Desenfreado (Giant Growth)',
            'preco_minimo' => 0.50,
            'loja_nome' => 'Olho de Leão',
            'loja_sigla' => 'OLHO',
            'loja_cor' => 'bg-red-700',
            'url_produto' => '#', 
        ],
        [
            'id' => 2,
            'nome' => 'Crescimento Desenfreado (Giant Growth)',
            'preco_minimo' => 0.45,
            'loja_nome' => "Dragon's Den",
            'loja_sigla' => 'DRAGON',
            'loja_cor' => 'bg-slate-900',
            'url_produto' => '#',
        ],
        [
            'id' => 3,
            'nome' => 'The One Ring',
            'preco_minimo' => 450.00,
            'loja_nome' => 'Olho de Leão',
            'loja_sigla' => 'OLHO',
            'loja_cor' => 'bg-red-700',
            'url_produto' => '#',
        ]
    ];

    public function removerDaLista($id)
    {
        // Lógica futura: Deleta o registro de favorito desta carta nesta loja
    }

    public function render()
    {
        return view('livewire.lobby.wantlist');
    }
}