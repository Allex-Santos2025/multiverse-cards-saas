<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreShippingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'is_active_melhor_envio', 'melhor_envio_token',
        'is_active_frenet', 'frenet_token',
        'is_active_correios', 'correios_codigo_adm', 'correios_cartao_postagem', 'correios_senha',
        
        // Regras Globais e PAC
        'taxa_seguro_percentual', 'prazo_manuseio_dias',
        'correios_pac', 'correios_pac_nome_exibicao', 'correios_pac_descricao',
        
        // Serviços de Contrato (Campos Novos da Migration)
        'correios_sedex', 'correios_sedex_nome_exibicao', 'correios_sedex_descricao',
        'correios_sedex10', 'correios_sedex10_nome_exibicao', 'correios_sedex10_descricao',
        'correios_mini_envios', 'correios_mini_envios_nome_exibicao', 'correios_mini_envios_descricao',
        'correios_impresso_modico', 'correios_impresso_modico_nome_exibicao', 'correios_impresso_modico_descricao',
        
        // Carta Registrada
        'is_active_carta_registrada', 'cr_nome_exibicao', 'cr_descricao', 'cr_valor_fixo', 
        'cr_taxa_percentual', 'cr_limite_cartas', 'cr_prazo_dias', 'cr_apenas_singles',
        
        // Retirada e Apps
        'is_active_retirada', 'retirada_nome_exibicao', 'retirada_instrucoes', 'retirada_apenas_local',
        'is_active_motoboy', 'motoboy_nome_exibicao', 'motoboy_descricao', 'motoboy_valor_fixo', 'motoboy_taxa_percentual', 'motoboy_apenas_local',
        'is_active_uber_flash', 'uber_flash_nome_exibicao', 'uber_flash_instrucoes', 'uber_flash_apenas_local'
    ];

    protected $casts = [
        'is_active_melhor_envio' => 'boolean',
        'is_active_frenet' => 'boolean',
        'is_active_correios' => 'boolean',
        'correios_pac' => 'boolean',
        'correios_sedex' => 'boolean',
        'correios_sedex10' => 'boolean',
        'correios_mini_envios' => 'boolean',
        'correios_impresso_modico' => 'boolean',
        'is_active_carta_registrada' => 'boolean',
        'cr_apenas_singles' => 'boolean',
        'is_active_retirada' => 'boolean',
        'retirada_apenas_local' => 'boolean',
        'is_active_motoboy' => 'boolean',
        'motoboy_apenas_local' => 'boolean',
        'is_active_uber_flash' => 'boolean',
        'uber_flash_apenas_local' => 'boolean',
        'taxa_seguro_percentual' => 'decimal:2',
        'cr_valor_fixo' => 'decimal:2',
        'cr_taxa_percentual' => 'decimal:2',
        'motoboy_valor_fixo' => 'decimal:2',
        'motoboy_taxa_percentual' => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}