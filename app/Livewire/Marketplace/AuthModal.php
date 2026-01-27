<?php

namespace App\Livewire\Marketplace;

use Livewire\Component;

class AuthModal extends Component
{
    public $isOpen = false;

    // Escuta eventos do navegador para abrir o modal
    protected $listeners = ['open-auth-modal' => 'open'];

    public function open()
    {
        $this->isOpen = true;
    }

    public function close()
    {
        $this->isOpen = false;
    }

    public function selectRole($role)
    {
        if ($role === 'store') {
            // Lojista: Segue o fluxo da Fase 1 (Planos -> Registro)
            return redirect()->to('/planos');
        }

        // Jogador: Inicia o novo fluxo da Fase 2 (Wizard do Player)
        return redirect()->to('/registro/jogador');
    }

    public function render()
    {
        return view('livewire.marketplace.auth-modal');
    }
}