<?php

namespace App\Livewire\Store\Dashboard\Management;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class Profile extends Component
{
    // Identificação e Domínio
    public $name = '';
    public $url_slug = '';
    public $use_custom_domain = false;
    public $domain = '';

    // Fiscais
    public $document = '';
    public $corporate_name = '';
    public $is_ie_exempt = false;
    public $state_registration = '';
    
    // Contatos e Endereço
    public $phone = '';
    public $support_email = '';
    public $store_zip_code = '';
    public $street = '';
    public $number = '';
    public $complement = '';
    public $neighborhood = '';
    public $city = '';
    public $store_state_code = '';

    // Redes Sociais Dinâmicas
    public $socials = [];

    // Plataformas disponíveis para o select
    public $availablePlatforms = [
        'instagram' => 'Instagram',
        'facebook' => 'Facebook',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
        'twitter' => 'X (Twitter)',
        'discord' => 'Discord',
        'twitch' => 'Twitch',
        'linkedin' => 'LinkedIn',
    ];

    public function mount()
    {
        $store = auth('store_user')->user()->store;

        $this->name = $store->name ?? '';
        $this->url_slug = $store->url_slug ?? '';
        $this->use_custom_domain = (bool) ($store->use_custom_domain ?? false);
        $this->domain = $store->domain ?? '';
        
        $this->document = $store->document ?? '';
        $this->corporate_name = $store->corporate_name ?? '';
        $this->is_ie_exempt = (bool) ($store->is_ie_exempt ?? false);
        $this->state_registration = $store->state_registration ?? '';
        
        $this->phone = $store->phone ?? '';
        $this->support_email = $store->support_email ?? '';
        
        $this->store_zip_code = $store->store_zip_code ?? '';
        $this->street = $store->street ?? '';
        $this->number = $store->number ?? '';
        $this->complement = $store->complement ?? '';
        $this->neighborhood = $store->neighborhood ?? '';
        $this->city = $store->city ?? '';
        $this->store_state_code = $store->store_state_code ?? '';

        // Carrega as redes sociais existentes do banco
        if ($store && $store->socials) {
            $this->socials = $store->socials->map(function ($social) {
                return ['platform' => $social->platform, 'url' => $social->url];
            })->toArray();
        }

        // Se a loja já tem CEP salvo, mas não tem rua preenchida, busca automaticamente
        if (!empty($this->store_zip_code) && empty($this->street)) {
            $this->buscarCep($this->store_zip_code);
        }
    }

    // --- MÉTODOS DE REDES SOCIAIS ---
    public function addSocial()
    {
        $this->socials[] = ['platform' => 'instagram', 'url' => ''];
    }

    public function removeSocial($index)
    {
        unset($this->socials[$index]);
        $this->socials = array_values($this->socials); // Reindexa o array
    }
    // --------------------------------

    public function updatedStoreZipCode($value)
    {
        $this->buscarCep($value);
    }

    private function buscarCep($cep)
    {
        $cepLimpo = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cepLimpo) === 8) {
            try {
                $response = Http::withoutVerifying()
                    ->timeout(5)
                    ->get("https://brasilapi.com.br/api/cep/v1/{$cepLimpo}");
                
                if ($response->successful()) {
                    $data = $response->json();
                    
                    $this->street = $data['street'] ?? '';
                    $this->neighborhood = $data['neighborhood'] ?? '';
                    $this->city = $data['city'] ?? '';
                    $this->store_state_code = $data['state'] ?? '';
                }
            } catch (\Exception $e) {
                session()->flash('error', 'Erro ao buscar CEP: ' . $e->getMessage());
            }
        }
    }

    public function updatedIsIeExempt($value)
    {
        if ($value) {
            $this->state_registration = 'Isento';
        } else {
            $this->state_registration = '';
        }
    }

    public function salvarPerfil()
    {
        $store = auth('store_user')->user()->store;

        if ($this->use_custom_domain && empty(trim($this->domain))) {
            session()->flash('error', 'Por favor, informe o seu domínio próprio.');
            return;
        }

        $store->update([
            'name' => $this->name,
            'url_slug' => $this->url_slug,
            'use_custom_domain' => $this->use_custom_domain,
            'domain' => $this->domain,
            'document' => $this->document,
            'corporate_name' => $this->corporate_name,
            'is_ie_exempt' => $this->is_ie_exempt,
            'state_registration' => $this->state_registration,
            'phone' => $this->phone,
            'support_email' => $this->support_email,
            'store_zip_code' => $this->store_zip_code,
            'street' => $this->street,
            'number' => $this->number,
            'complement' => $this->complement,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'store_state_code' => $this->store_state_code,
        ]);

        // --- SALVANDO AS REDES SOCIAIS ---
        // Apaga as antigas e insere as novas limpas (evita links em branco)
        $store->socials()->delete();
        
        $validSocials = array_filter($this->socials, function ($social) {
            return !empty(trim($social['url']));
        });

        if (!empty($validSocials)) {
            $store->socials()->createMany($validSocials);
        }
        // ---------------------------------

        session()->flash('message', 'Dados da loja atualizados com sucesso!');
    }

    public function render()
    {
        return view('livewire.store.dashboard.management.profile')
            ->extends('layouts.dashboard')
            ->section('content');
    }
}