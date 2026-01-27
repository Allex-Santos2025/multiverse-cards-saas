<?php

namespace App\Livewire\Store;

use Livewire\Component;
use App\Models\PlayerUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class PlayerRegisterWizard extends Component
{
    public $currentStep = 1; // 1: Termos, 2: Formulário
    
    // Dados do Jogador
    public $name, $surname, $nickname, $email, $password, $password_confirmation;
    public $acceptTerms = false;
    public $isSocial = false;
    public $userCreated;

    protected $rules = [
        'nickname' => 'required|unique:player_users,nickname|min:3',
        'email' => 'required|email|unique:player_users,email',
        'password' => 'required|min:8|confirmed',
    ];
    public function render()
    {
        return view('livewire.player-register-wizard')
            ->layout('layouts.app', [
                'funnelMode' => true,
                'funnelTitle' => $this->getFunnelTitle(),
                //'backLink' => $this->getBackLink(),
            ]); 
    }

    public function mount()
    {
        // Verifica se existem dados sociais na sessão
        if (session()->has('social_registration')) {
            $data = session('social_registration');

            $this->name = $data['name'];
            $this->surname = $data['surname'];
            $this->email = $data['email'];
            
            // Sugestão de login baseada no e-mail (ex: joao.silva)
            $this->login = strstr($this->email, '@', true);
            
            // Ativamos a flag de social
            $this->isSocial = true;

            // Opcional: Avançar automaticamente para o passo 2 se o passo 1 for apenas seleção
            $this->currentStep = 2;
        }
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
        // Se for manual, criamos o usuário mas ele fica "pendente"
        $this->registerUser();        
       
        } 
    }
    public function registerUser()
{
    // 1. Usamos uma variável local temporária (como funcionava antes)
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
            $this->redirectAfterLogin();
        }
        $this->userCreated = $player;
        $this->currentStep = 3;
    }

    
}
