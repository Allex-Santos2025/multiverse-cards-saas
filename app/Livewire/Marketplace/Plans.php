<?php

namespace App\Livewire\Marketplace;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Plans extends Component
{
    #[Layout('layouts.app', [
        'funnelMode' => true,
        'funnelTitle' => 'ESCOLHA SEU PLANO',
        'backLink' => '/' 
    ])] 
    public function render()
    {
        return view('livewire.marketplace.plans');
    }

    public function selectPlan($planType, $cycle)
    {
        // Redirecionamento temporário para teste
        dd("Plano: $planType | Ciclo: $cycle");
    }
}
