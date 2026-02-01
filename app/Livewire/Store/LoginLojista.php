<?php

namespace App\Livewire\Store;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
// 1. Importe o Model do Log no topo
use App\Models\ActivityLog;

class LoginLojista extends Component
{
    public $slug;
    public $email;
    public $password;
    public $store;

    public function mount($slug)
    {
        $this->slug = $slug;

        $this->store = DB::table('stores')->where('url_slug', $slug)->first();

        if (!$this->store) {
            abort(404, 'Loja não encontrada.');
        }
    }

    public function autenticar()
    {
        $credentials = $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'O campo e-mail é obrigatório.',
            'email.email' => 'Insira um e-mail válido.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
        ]);

        if (Auth::guard('store_user')->attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();

            // 2. REGISTRA O LOG DE ACESSO
            // Pegamos o usuário que acabou de logar
            $user = Auth::guard('store_user')->user();

            ActivityLog::create([
                'store_id'    => $user->current_store_id, // Usando o campo correto do seu banco
                'user_id'     => $user->id,
                'user_guard'  => 'store_user',
                'action'      => 'Login Realizado',
                'module'      => 'security',
                'description' => 'Sessão iniciada via ' . request()->header('User-Agent'),
                'ip_address'  => request()->ip(),
            ]);

            return redirect()->route('store.dashboard', ['slug' => $this->slug]);
        }
        
        $this->addError('login_error', 'E-mail ou senha incorretos. Verifique seus dados.');
    }

    public function render()
    {
        return view('livewire.store.login-lojista')
            ->layout('layouts.guest');
    }
}