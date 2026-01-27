<?php

namespace App\Livewire\Marketplace;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthModalLogin extends Component
{
    // Propriedades para as duas regras
    public $email;      // Usado para Nick ou E-mail do Player
    public $password;   // Usada para a senha do Player
    public $storeSlug;  // Usado para o localizador da Loja

    // REGRA 1: Autenticação do Jogador
    public function loginPlayer()
    {
        $this->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $coluna = filter_var($this->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'nickname';

        // Usa o guard 'player' do seu config/auth.php
        if (Auth::guard('player')->attempt([$coluna => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            $user = Auth::guard('player')->user();
            return redirect()->route('player.wait', ['nickname' => $user->nickname]);
        }

        throw ValidationException::withMessages(['email' => 'Credenciais inválidas na arena.']);
    }

    // REGRA 2: Redirecionamento do Lojista (Slug)
    public function redirectToStore()
    {
        $this->validate([
            'storeSlug' => 'required|min:2',
        ]);

        // Redireciona para a estrutura de URL que você já utiliza
        return redirect('/loja/' . $this->storeSlug . '/aguarde');
    }

    public function render()
    {
        return view('livewire.marketplace.auth-modal-login');
    }
}