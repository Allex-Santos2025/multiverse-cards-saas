<?php

namespace App\Livewire\Store;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Models\ActivityLog;
use App\Models\StoreUser;

class LoginLojista extends Component
{
    public $slug;
    public $loja;
    
    // Login
    public $email;
    public $password;

    // Recuperação
    public $recoverEmail;
    public $token;
    public $new_password;
    public $new_password_confirmation;
    
    // O estado inicial da tela
    public $mode = 'login'; // 'login', 'forgot' ou 'reset'

    public function mount($slug, $token = null)
    {
        $this->slug = $slug;
        $this->loja = \App\Models\Store::with('visual')->where('url_slug', $slug)->first();

        if (!$this->loja) { abort(404); }

        // Se existir um token na URL, mudamos automaticamente para o modo reset
        if ($token) {
            $this->token = $token;
            $this->email = request()->query('email');
            $this->mode = 'reset';
        }
    }

    public function autenticar()
    {
        $this->validate(['email' => 'required|email', 'password' => 'required|min:6']);

        if (Auth::guard('store_user')->attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            return redirect()->route('store.dashboard', ['slug' => $this->slug]);
        }
        
        $this->addError('login_error', 'E-mail ou senha incorretos.');
    }

    public function enviarRecuperacao()
    {
        $this->validate(['recoverEmail' => 'required|email']);
        $user = StoreUser::where('email', $this->recoverEmail)->first();

        if ($user) {
            $token = Password::createToken($user);
            $user->notify(new \App\Notifications\ResetStorePassword($token, $this->slug));
        }

        session()->flash('recoverSuccess', 'Se o e-mail existir, o link foi enviado!');
        $this->recoverEmail = '';
    }

    // Ação final de trocar a senha
    public function redefinirSenha()
    {
        $this->validate([
            'email' => 'required|email',
            'new_password' => 'required|min:8|confirmed',
        ], [
            'new_password.confirmed' => 'As senhas não coincidem.',
            'new_password.min' => 'A senha deve ter 8 caracteres.'
        ]);

        $user = StoreUser::where('email', $this->email)->first();

        if (!$user || !Password::tokenExists($user, $this->token)) {
            session()->flash('resetError', 'O link expirou ou é inválido.');
            return;
        }

        // Atualiza e limpa o token
        $user->update(['password' => Hash::make($this->new_password)]);
        Password::deleteToken($user);

        session()->flash('login_success', 'Senha alterada! Pode fazer login.');
        $this->mode = 'login';
    }

    public function render()
    {
        return view('livewire.store.login-lojista')->layout('layouts.guest');
    }
}