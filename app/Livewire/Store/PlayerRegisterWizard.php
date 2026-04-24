<?php

namespace App\Livewire\Store;

use Livewire\Component;
use App\Models\PlayerUser;
use App\Models\Store; // <-- Necessário para buscar a loja
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PlayerRegisterWizard extends Component
{
    public $currentStep = 1; 
    
    // Dados do Jogador
    public $name, $surname, $nickname, $email, $password, $password_confirmation;
    public $acceptTerms = false;
    public $isSocial = false;
    public $userCreated;

    // --- VARIÁVEIS WHITE LABEL ---
    public $isMarketplace = true;
    public $loja = null;

    protected $rules = [
        'nickname' => 'required|unique:player_users,nickname|min:3',
        'email' => 'required|email|unique:player_users,email',
        'password' => 'required|min:8|confirmed',
    ];

    public function mount()
    {
        // 1. LÓGICA WHITE LABEL: Captura a URL ou a Sessão
        if (request()->has('loja')) {
            // Se o botão mandou o slug da loja na URL, a gente pega e força a sessão
            $contexto = request()->query('loja');
            session(['contexto_loja' => $contexto]); 
        } else {
            // Se não (ex: veio pelo menu principal), lê a sessão normalmente
            $contexto = session('contexto_loja', 'versus');
        }

        if ($contexto !== 'versus') {
            $this->loja = Store::where('url_slug', $contexto)->first();
            $this->isMarketplace = false;
        }

        // 2. Verifica se existem dados sociais na sessão
        if (session()->has('social_registration')) {
            $data = session('social_registration');

            $this->name = $data['name'];
            $this->surname = $data['surname'];
            $this->email = $data['email'];
            
            // Sugestão de login baseada no e-mail
            $this->nickname = strstr($this->email, '@', true);
            
            // Ativamos a flag de social
            $this->isSocial = true;

            // Avançar automaticamente para o passo 2
            $this->currentStep = 2;
        }
    }

    public function render()
    {
        // Define qual layout abraça a página dependendo de onde o usuário está
        $layout = $this->isMarketplace ? 'layouts.app' : 'layouts.template';

        return view('livewire.player-register-wizard')
            ->layout($layout, [
                'funnelMode' => true,
                'funnelTitle' => $this->getFunnelTitle(),
                'loja' => $this->loja // Passa a loja para o layout, caso o layout precise
            ]); 
    }

    public function getFunnelTitle() 
    {
        return match ($this->currentStep) {
            1 => 'ACEITE OS TERMOS',
            2 => 'SEUS DADOS PESSOAIS',
            3 => 'CADASTRO CONCLUÍDO',
            default => 'CADASTRO DE JOGADOR',
        };
    }

    public function nextStep() 
    {
        $this->validateCurrentStep();
        $this->currentStep++;
    }

    public function previousStep() 
    { 
        $this->currentStep--; 
    }

    public function validateCurrentStep() 
    {
        if ($this->currentStep === 1) {
            $this->validate(['acceptTerms' => 'required|accepted']);
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'name' => 'required|min:3',
                'surname' => 'required|min:3',
                'nickname' => 'required|unique:player_users,nickname',
                'email' => 'required|email|unique:player_users,email',
                'password' => 'required|min:8|confirmed',
            ]);
            
            $this->registerUser();        
        } 
    }

    public function registerUser()
    {
        $player = PlayerUser::create([
            'name'               => $this->name,
            'surname'            => $this->surname,
            'nickname'           => $this->nickname,
            'email'              => $this->email,
            'password'           => Hash::make($this->password),
            'email_verified_at'  => $this->isSocial ? now() : null,
            'preferred_language' => 'pt_BR',
        ]);
        
        if (!$this->isSocial) {
            $player->sendEmailVerificationNotification();
        } else {
            Auth::login($player);
            // $this->redirectAfterLogin(); // Descomente se tiver essa função
        }

        $this->userCreated = $player;
        $this->currentStep = 3;
    }
}