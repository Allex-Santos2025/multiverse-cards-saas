<?php

namespace App\Livewire\Marketplace;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\StoreUser;
use App\Models\Store;
use App\Notifications\RecoverStoreSlug;

class AuthModalLogin extends Component
{
    // Propriedades para as duas regras
    public $email;      // Usado para Nick ou E-mail do Player
    public $password;   // Usada para a senha do Player
    public $storeSlug;  // Usado para o localizador da Loja
    public $recoverEmail;

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
        return redirect('/loja/' . $this->storeSlug . '/login');
    }

    public function render()
    {
        return view('livewire.marketplace.auth-modal-login');
    }

    // REGRA 3: Recuperação de Slug da Loja
    public function recoverStoreSlug()
    {
        $this->validate([
            'recoverEmail' => 'required|email',
        ]);

        // Busca o lojista pelo email usando o Model correto
        $user = StoreUser::where('email', $this->recoverEmail)->first();

        if (!$user) { 
            session()->flash('recoverError', 'Não há nenhuma conta atrelada a este e-mail.');
            return;
        }

        // Busca a loja atrelada a esse lojista
        $store = Store::where('owner_user_id', $user->id)->first();

        if (!$store) {
            session()->flash('recoverError', 'Este e-mail não possui uma loja ativa.');
            return;
        }

        // Dispara o e-mail usando o mesmo motor de notificações do seu sistema
        $user->notify(new RecoverStoreSlug($store->url_slug));
        
        session()->flash('recoverSuccess', 'Enviamos o link da sua loja para o seu e-mail!');
        $this->recoverEmail = ''; 
    }
}