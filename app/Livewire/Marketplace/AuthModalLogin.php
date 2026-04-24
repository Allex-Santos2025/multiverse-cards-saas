<?php

namespace App\Livewire\Marketplace;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use App\Models\StoreUser;
use App\Models\Store;
use App\Models\CartItem;
use App\Notifications\RecoverStoreSlug;

class AuthModalLogin extends Component
{
    // Propriedades para as duas regras
    public $email;
    public $password;
    public $storeSlug;
    public $recoverEmail;

    // Declaração única das variáveis
    public $isMarketplace = true;
    public $loja = null;

    // O Mount define o valor inicial
    public function mount($isMarketplace = true, $loja = null)
    {
        $this->isMarketplace = $isMarketplace;
        $this->loja = $loja;
    }

    // REGRA 1: Autenticação do Jogador
    public function loginPlayer()
    {
        $this->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $coluna = filter_var($this->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'nickname';

        if (Auth::guard('player')->attempt([$coluna => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            $user = Auth::guard('player')->user();

            CartItem::where('session_id', Session::getId())
                    ->update(['user_id' => $user->id]);

            // Mantém o usuário na MESMA TELA em que ele abriu o modal (Loja ou Versus)
            // Isso faz a página recarregar no mesmo lugar e o Dropdown do Header vai aparecer naturalmente
            return redirect()->to(request()->header('Referer') ?? '/');
        }

        throw ValidationException::withMessages(['email' => 'Credenciais inválidas.']);
    }

    // REGRA 2: Redirecionamento do Lojista
    public function redirectToStore()
    {
        if (!$this->isMarketplace) {
            return;
        }

        $this->validate([
            'storeSlug' => 'required|min:2',
        ]);

        return redirect('/loja/' . $this->storeSlug . '/login');
    }

    public function render()
    {
        return view('livewire.marketplace.auth-modal-login');
    }

    // REGRA 3: Recuperação de Slug
    public function recoverStoreSlug()
    {
        if (!$this->isMarketplace) {
            return;
        }

        $this->validate([
            'recoverEmail' => 'required|email',
        ]);

        $user = StoreUser::where('email', $this->recoverEmail)->first();

        if (!$user) { 
            session()->flash('recoverError', 'Não há nenhuma conta atrelada a este e-mail.');
            return;
        }

        $store = Store::where('owner_user_id', $user->id)->first();

        if (!$store) {
            session()->flash('recoverError', 'Este e-mail não possui uma loja ativa.');
            return;
        }

        $user->notify(new RecoverStoreSlug($store->url_slug));
        
        session()->flash('recoverSuccess', 'Enviamos o link da sua loja para o seu e-mail!');
        $this->recoverEmail = ''; 
    }
}