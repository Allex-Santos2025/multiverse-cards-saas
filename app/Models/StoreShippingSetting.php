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
        'correios_pac', 'correios_pac_nome_exibicao', 'correios_pac_descricao', 'taxa_seguro_percentual', 'prazo_manuseio_dias',
        'correios_sedex', 'correios_sedex10', 'correios_mini_envios',
        'is_active_carta_registrada', 'cr_nome_exibicao', 'cr_descricao', 'cr_valor_fixo', 'cr_taxa_percentual', 'cr_limite_cartas', 'cr_prazo_dias', 'cr_apenas_singles',
        'is_active_retirada', 'retirada_nome_exibicao', 'retirada_instrucoes', 'retirada_apenas_local'
    ];

    // O Segredo para o Livewire marcar a chavinha corretamente ao recarregar a tela
    protected $casts = [
        'is_active_melhor_envio' => 'boolean',
        'is_active_frenet' => 'boolean',
        'is_active_correios' => 'boolean',
        'correios_pac' => 'boolean',
        'correios_sedex' => 'boolean',
        'correios_sedex10' => 'boolean',
        'correios_mini_envios' => 'boolean',
        'is_active_carta_registrada' => 'boolean',
        'cr_apenas_singles' => 'boolean',
        'is_active_retirada' => 'boolean',
        'retirada_apenas_local' => 'boolean',
        'taxa_seguro_percentual' => 'decimal:2',
        'cr_valor_fixo' => 'decimal:2',
        'cr_taxa_percentual' => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}