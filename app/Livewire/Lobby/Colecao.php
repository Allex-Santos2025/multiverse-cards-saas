<?php

namespace App\Livewire\Lobby;

use Livewire\Component;

class Colecao extends Component
{
    // Toggle para a meta de completar a coleção (1x ou 4x)
    public $metaColecao = '1x';

    // Os Fichários Físicos Simulados
    public $ficharios = [
        [
            'id' => 1,
            'nome' => 'Raras & Míticas',
            'tipo' => '3x3 (9-Pocket)',
            'cor' => 'bg-slate-900',
            'cartas_atuais' => 145,
            'capacidade' => 360,
            'progresso' => '40%',
            'icone' => 'ph-book-bookmark'
        ],
        [
            'id' => 2,
            'nome' => 'Trades (Pasta de Troca)',
            'tipo' => '2x2 (4-Pocket)',
            'cor' => 'bg-indigo-600',
            'cartas_atuais' => 160,
            'capacidade' => 160,
            'progresso' => '100%',
            'icone' => 'ph-arrows-left-right'
        ],
        [
            'id' => 3,
            'nome' => 'Bulk (Caixa de Sapato)',
            'tipo' => 'Infinita',
            'cor' => 'bg-orange-700',
            'cartas_atuais' => 3420,
            'capacidade' => 'Ilimitada',
            'progresso' => '100%', // Infinita não tem limite visual de barra
            'icone' => 'ph-package'
        ]
    ];

    // O Progresso por Edição (Set Completion)
    public $progressoEdicoes = [
        [
            'id' => 1,
            'set_nome' => 'The Lord of the Rings: Tales of Middle-earth',
            'set_sigla' => 'LTR',
            'total_cartas_set' => 281, // Cartas únicas no set
            'tidas_1x' => 200, // Quantas únicas ele tem
            'tidas_4x' => 450, // Quantas no total ele tem para o playset
            'idiomas' => ['PT', 'EN'],
            'url_vitrine' => '#'
        ],
        [
            'id' => 2,
            'set_nome' => 'Obsidian Flames',
            'set_sigla' => 'OBF',
            'total_cartas_set' => 230,
            'tidas_1x' => 230, 
            'tidas_4x' => 800,
            'idiomas' => ['PT'],
            'url_vitrine' => '#'
        ]
    ];

    public function setMeta($meta)
    {
        $this->metaColecao = $meta;
    }

    public function render()
    {
        return view('livewire.lobby.colecao');
    }
}