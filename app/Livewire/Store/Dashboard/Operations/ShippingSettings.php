<?php

namespace App\Livewire\Store\Dashboard\Operations;

use Livewire\Component;
use App\Models\StoreShippingSetting;

class ShippingSettings extends Component
{
    // ==========================================
    // INTEGRAÇÕES GLOBAIS (Chaves Mestras)
    // ==========================================
    public $is_active_melhor_envio = false;
    public $melhor_envio_token = '';
    
    public $is_active_frenet = false;
    public $frenet_token = '';

    public $is_active_correios = false;
    public $correios_codigo_adm = '';
    public $correios_cartao_postagem = '';
    public $correios_senha = '';

    // ==========================================
    // REGRAS GLOBAIS
    // ==========================================
    public $taxa_seguro_percentual = '2.00'; 
    public $prazo_manuseio_dias = 1;

    // ==========================================
    // SERVIÇOS DOS CORREIOS (API/CONTRATO)
    // ==========================================
    public $correios_pac = false;
    public $correios_pac_nome_exibicao = 'Correios PAC';
    public $correios_pac_descricao = 'Entrega econômica padrão.';

    public $correios_sedex = false;
    public $correios_sedex_nome_exibicao = 'Correios Sedex';
    public $correios_sedex_descricao = '';

    public $correios_sedex10 = false;
    public $correios_sedex10_nome_exibicao = 'Correios Sedex 10';
    public $correios_sedex10_descricao = '';

    public $correios_mini_envios = false;
    public $correios_mini_envios_nome_exibicao = 'Correios Mini Envios';
    public $correios_mini_envios_descricao = '';

    public $correios_impresso_modico = false;
    public $correios_impresso_modico_nome_exibicao = 'Impresso Módico';
    public $correios_impresso_modico_descricao = '';

    // ==========================================
    // SERVIÇOS MANUAIS / FIXOS
    // ==========================================
    
    // Carta Registrada
    public $is_active_carta_registrada = false;
    public $cr_nome_exibicao = 'Impresso Nacional';
    public $cr_descricao = 'Envio econômico exclusivo para cartas avulsas.';
    public $cr_valor_fixo = '15.00';
    public $cr_taxa_percentual = '0.00'; 
    public $cr_limite_cartas = 80;
    public $cr_prazo_dias = 7;
    public $cr_apenas_singles = true;

    // Retirada
    public $is_active_retirada = false;
    public $retirada_nome_exibicao = 'Entrega no Metrô (Agendar)'; 
    public $retirada_instrucoes = 'Entrega na estação de metrô. Favor entrar em contato pelo WhatsApp.';
    public $retirada_apenas_local = true;

    // Motoboy
    public $is_active_motoboy = false;
    public $motoboy_nome_exibicao = 'Entrega via Motoboy';
    public $motoboy_descricao = 'Entrega rápida no mesmo dia útil.';
    public $motoboy_valor_fixo = '15.00';
    public $motoboy_taxa_percentual = '0.00'; 
    public $motoboy_apenas_local = true;

    // Uber Flash
    public $is_active_uber_flash = false;
    public $uber_flash_nome_exibicao = 'Uber Flash / 99 Entrega';
    public $uber_flash_instrucoes = 'Solicite o motorista pelo seu app.';
    public $uber_flash_apenas_local = true;

    public function mount()
    {
        $store = auth('store_user')->user()->store;
        $settings = StoreShippingSetting::where('store_id', $store->id)->first();

        if ($settings) {
            $this->is_active_melhor_envio = $settings->is_active_melhor_envio;
            $this->melhor_envio_token = $settings->melhor_envio_token;
            $this->is_active_frenet = $settings->is_active_frenet;
            $this->frenet_token = $settings->frenet_token;
            $this->is_active_correios = $settings->is_active_correios;
            $this->correios_codigo_adm = $settings->correios_codigo_adm;
            $this->correios_cartao_postagem = $settings->correios_cartao_postagem;
            $this->correios_senha = $settings->correios_senha;

            // Regras Globais
            $this->taxa_seguro_percentual = $settings->taxa_seguro_percentual;
            $this->prazo_manuseio_dias = $settings->prazo_manuseio_dias;

            // PAC
            $this->correios_pac = $settings->correios_pac;
            $this->correios_pac_nome_exibicao = $settings->correios_pac_nome_exibicao;
            $this->correios_pac_descricao = $settings->correios_pac_descricao;

            // Sedex
            $this->correios_sedex = $settings->correios_sedex;
            $this->correios_sedex_nome_exibicao = $settings->correios_sedex_nome_exibicao ?? 'Correios Sedex';
            $this->correios_sedex_descricao = $settings->correios_sedex_descricao;

            // Sedex 10
            $this->correios_sedex10 = $settings->correios_sedex10;
            $this->correios_sedex10_nome_exibicao = $settings->correios_sedex10_nome_exibicao ?? 'Correios Sedex 10';
            $this->correios_sedex10_descricao = $settings->correios_sedex10_descricao;

            // Mini Envios
            $this->correios_mini_envios = $settings->correios_mini_envios;
            $this->correios_mini_envios_nome_exibicao = $settings->correios_mini_envios_nome_exibicao ?? 'Correios Mini Envios';
            $this->correios_mini_envios_descricao = $settings->correios_mini_envios_descricao;

            // Impresso Módico
            $this->correios_impresso_modico = $settings->correios_impresso_modico;
            $this->correios_impresso_modico_nome_exibicao = $settings->correios_impresso_modico_nome_exibicao ?? 'Impresso Módico';
            $this->correios_impresso_modico_descricao = $settings->correios_impresso_modico_descricao;

            // Manuais
            $this->is_active_carta_registrada = $settings->is_active_carta_registrada;
            $this->cr_nome_exibicao = $settings->cr_nome_exibicao;
            $this->cr_descricao = $settings->cr_descricao;
            $this->cr_valor_fixo = $settings->cr_valor_fixo;
            $this->cr_taxa_percentual = $settings->cr_taxa_percentual;
            $this->cr_limite_cartas = $settings->cr_limite_cartas;
            $this->cr_prazo_dias = $settings->cr_prazo_dias;
            $this->cr_apenas_singles = $settings->cr_apenas_singles;

            $this->is_active_retirada = $settings->is_active_retirada;
            $this->retirada_nome_exibicao = $settings->retirada_nome_exibicao;
            $this->retirada_instrucoes = $settings->retirada_instrucoes;
            $this->retirada_apenas_local = $settings->retirada_apenas_local;

            $this->is_active_motoboy = $settings->is_active_motoboy;
            $this->motoboy_nome_exibicao = $settings->motoboy_nome_exibicao;
            $this->motoboy_descricao = $settings->motoboy_descricao;
            $this->motoboy_valor_fixo = $settings->motoboy_valor_fixo;
            $this->motoboy_taxa_percentual = $settings->motoboy_taxa_percentual;
            $this->motoboy_apenas_local = $settings->motoboy_apenas_local;

            $this->is_active_uber_flash = $settings->is_active_uber_flash;
            $this->uber_flash_nome_exibicao = $settings->uber_flash_nome_exibicao;
            $this->uber_flash_instrucoes = $settings->uber_flash_instrucoes;
            $this->uber_flash_apenas_local = $settings->uber_flash_apenas_local;
        }
    }

    public function salvarConfiguracoes()
    {
        $store = auth('store_user')->user()->store;

        try {
            StoreShippingSetting::updateOrCreate(
                ['store_id' => $store->id],
                [
                    'is_active_melhor_envio' => (bool) $this->is_active_melhor_envio,
                    'melhor_envio_token' => $this->melhor_envio_token,
                    'is_active_frenet' => (bool) $this->is_active_frenet,
                    'frenet_token' => $this->frenet_token,
                    'is_active_correios' => (bool) $this->is_active_correios,
                    'correios_codigo_adm' => $this->correios_codigo_adm,
                    'correios_cartao_postagem' => $this->correios_cartao_postagem,
                    'correios_senha' => $this->correios_senha,

                    // Regras Globais
                    'taxa_seguro_percentual' => (float) str_replace(',', '.', $this->taxa_seguro_percentual ?: 0),
                    'prazo_manuseio_dias' => (int) ($this->prazo_manuseio_dias ?: 1),

                    // PAC
                    'correios_pac' => (bool) $this->correios_pac,
                    'correios_pac_nome_exibicao' => $this->correios_pac_nome_exibicao ?: 'Correios PAC',
                    'correios_pac_descricao' => $this->correios_pac_descricao,

                    // Sedex
                    'correios_sedex' => (bool) $this->correios_sedex,
                    'correios_sedex_nome_exibicao' => $this->correios_sedex_nome_exibicao ?: 'Correios Sedex',
                    'correios_sedex_descricao' => $this->correios_sedex_descricao,

                    // Sedex 10
                    'correios_sedex10' => (bool) $this->correios_sedex10,
                    'correios_sedex10_nome_exibicao' => $this->correios_sedex10_nome_exibicao ?: 'Correios Sedex 10',
                    'correios_sedex10_descricao' => $this->correios_sedex10_descricao,

                    // Mini Envios
                    'correios_mini_envios' => (bool) $this->correios_mini_envios,
                    'correios_mini_envios_nome_exibicao' => $this->correios_mini_envios_nome_exibicao ?: 'Correios Mini Envios',
                    'correios_mini_envios_descricao' => $this->correios_mini_envios_descricao,

                    // Impresso Módico
                    'correios_impresso_modico' => (bool) $this->correios_impresso_modico,
                    'correios_impresso_modico_nome_exibicao' => $this->correios_impresso_modico_nome_exibicao ?: 'Impresso Módico',
                    'correios_impresso_modico_descricao' => $this->correios_impresso_modico_descricao,

                    // Carta Registrada
                    'is_active_carta_registrada' => (bool) $this->is_active_carta_registrada,
                    'cr_nome_exibicao' => $this->cr_nome_exibicao ?: 'Impresso Nacional',
                    'cr_descricao' => $this->cr_descricao,
                    'cr_valor_fixo' => (float) str_replace(',', '.', $this->cr_valor_fixo ?: 0),
                    'cr_taxa_percentual' => (float) str_replace(',', '.', $this->cr_taxa_percentual ?: 0),
                    'cr_limite_cartas' => (int) ($this->cr_limite_cartas ?: 80),
                    'cr_prazo_dias' => (int) ($this->cr_prazo_dias ?: 7),
                    'cr_apenas_singles' => (bool) $this->cr_apenas_singles,

                    // Retirada
                    'is_active_retirada' => (bool) $this->is_active_retirada,
                    'retirada_nome_exibicao' => $this->retirada_nome_exibicao ?: 'Entrega no Metrô',
                    'retirada_instrucoes' => $this->retirada_instrucoes,
                    'retirada_apenas_local' => (bool) $this->retirada_apenas_local,

                    // Motoboy
                    'is_active_motoboy' => (bool) $this->is_active_motoboy,
                    'motoboy_nome_exibicao' => $this->motoboy_nome_exibicao ?: 'Entrega via Motoboy',
                    'motoboy_descricao' => $this->motoboy_descricao,
                    'motoboy_valor_fixo' => (float) str_replace(',', '.', $this->motoboy_valor_fixo ?: 0),
                    'motoboy_taxa_percentual' => (float) str_replace(',', '.', $this->motoboy_taxa_percentual ?: 0),
                    'motoboy_apenas_local' => (bool) $this->motoboy_apenas_local,

                    // Uber Flash
                    'is_active_uber_flash' => (bool) $this->is_active_uber_flash,
                    'uber_flash_nome_exibicao' => $this->uber_flash_nome_exibicao ?: 'Uber Flash / 99 Entrega',
                    'uber_flash_instrucoes' => $this->uber_flash_instrucoes,
                    'uber_flash_apenas_local' => (bool) $this->uber_flash_apenas_local,
                ]
            );

            session()->flash('message', 'Configurações de frete salvas com sucesso!');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Ocorreu um erro ao salvar no banco de dados.');
        }
    }

    public function render()
    {
        return view('livewire.store.dashboard.operations.shipping-settings')
            ->extends('layouts.dashboard')
            ->section('content');
    }
}