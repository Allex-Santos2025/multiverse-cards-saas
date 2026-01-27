<?php

namespace App\Livewire\Marketplace;

use Livewire\Component;

class Events extends Component
{
    public function render()
{
    return view('livewire.marketplace.events')
        ->layout('layouts.app',[ // Indica o caminho correto do layout
        'funnelMode'  => true,
        'funnelTitle' => 'EVENTOS',
        'backLink'    => route('home') 
        ]);
}
}