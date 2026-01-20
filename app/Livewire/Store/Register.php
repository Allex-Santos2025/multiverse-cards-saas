<?php

namespace App\Livewire\Store;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\Plan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Register extends Component
{
    public $currentStep = 1; 

    // --- DADOS DO PLANO ---
    public $plan_slug, $plan_id, $plan_name;

    // --- DADOS DO LOJISTA ---
    public $name, $surname, $login, $email, $document_number, $password, $password_confirmation;

    // --- DADOS DA LOJA ---
    public $store_name, $url_slug, $store_slogan, $store_zip_code, $store_state_code;

    // Propriedade para o e-mail mascarado no Passo 4
    public $userCreated;
    public $acceptTerms = false;

    #[Layout('layouts.app')]
    #[Title('Cadastro de Lojista')]
    public function render()
    {
        return view('livewire.store.register')
            ->layout('layouts.app', [
                'funnelMode' => true,
                'funnelTitle' => $this->getFunnelTitle(),
                'backLink' => $this->getBackLink(),
            ]);
    }

    public function mount($plan = null)
    {
        if (!$plan) {
            return redirect()->route('plans');
        }

        $selectedPlan = Plan::where('slug', $plan)->first();

        if (!$selectedPlan) {
            return redirect()->route('plans');
        }

        $this->plan_slug = $selectedPlan->slug;
        $this->plan_id = $selectedPlan->id;
        $this->plan_name = $selectedPlan->name;
    }

    public function getFunnelTitle() 
    {
        return match ($this->currentStep) {
            1 => 'ACEITE OS TERMOS',
            2 => 'SEUS DADOS PESSOAIS',
            3 => 'INFORMAÇÕES DA LOJA',
            4 => 'CADASTRO CONCLUÍDO',
            default => 'CADASTRO DE LOJISTA',
        };
    }

    public function getBackLink() 
    {
        return ($this->currentStep === 1) ? route('plans') : '#';
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
                'name' => 'required', 
                'surname' => 'required',
                'login' => 'required|unique:store_users,login',
                'document_number' => 'required|unique:store_users,document_number',
                'email' => 'required|email|unique:store_users,email',
                'password' => 'required|min:8|confirmed',
            ]);
        } elseif ($this->currentStep === 3) {
            $this->validate([
                'store_name' => 'required',
                'url_slug' => 'required|unique:stores,url_slug|alpha_dash',
                'store_zip_code' => 'required',
                'store_state_code' => 'required',
            ]);
        }
    }

    public function finishRegistration() 
    {        
        $this->validateCurrentStep(); 

        try {
            DB::transaction(function () {
                // 1. Cria o Lojista
                $this->userCreated = StoreUser::create([
                    'name' => $this->name, 
                    'surname' => $this->surname,
                    'login' => $this->login, 
                    'email' => $this->email,
                    'document_number' => $this->document_number,
                    'password' => Hash::make($this->password),
                    'is_active' => true,
                ]);

                // 2. Cria a Loja vinculada
                $store = Store::create([
                    'name' => $this->store_name,
                    'url_slug' => Str::slug($this->url_slug),
                    'slogan' => $this->store_slogan,
                    'zip_code' => $this->store_zip_code,
                    'state' => $this->store_state_code,
                    'owner_user_id' => $this->userCreated->id,
                    'plan_id' => $this->plan_id,
                    'is_active' => false, 
                ]);

                // 3. Atualiza o lojista com o ID da loja criada
                $this->userCreated->update(['current_store_id' => $store->id]);
            });

            // Envia o e-mail de verificação (O lojista NÃO é logado aqui)
            if ($this->userCreated) {
                $this->userCreated->sendEmailVerificationNotification();
            }

            // Move para a tela final de sucesso
            $this->currentStep = 4;

        } catch (\Exception $e) { 
            \Log::error("Erro Registro Versus TCG: " . $e->getMessage());
            session()->flash('error', "Erro ao salvar os dados. Por favor, tente novamente."); 
        }
    }
}