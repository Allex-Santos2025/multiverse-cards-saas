<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreGameMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'game_id', // <--- Mudamos de 'game' para 'game_id' para bater com o banco
        'name_singles',
        'name_sealed',
        'name_accessories',
        'name_latest',
        'name_all_sets',
        'show_singles',
        'show_sealed',
        'show_accessories',
        'show_latest',
        'show_all_sets',
        'is_active',
        'position',
        'name_updates', // O nome que o lojista vai dar (ex: Últimas Atualizações)
        'show_updates', // Boolean para ligar/desligar o botão
    ];

    protected $casts = [
        'show_singles' => 'boolean',
        'show_sealed' => 'boolean',
        'show_accessories' => 'boolean',
        'show_latest' => 'boolean',
        'show_all_sets' => 'boolean',
        'is_active' => 'boolean',
        'position' => 'integer',
        'game_id' => 'integer', // <--- Cast para garantir que o ID venha como número
        'show_updates' => 'boolean',
    ];

    /**
     * Relacionamento com o Jogo (O que estava faltando!)
     * Agora o Laravel sabe que game_id aponta para a tabela games
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }
    /**
     * Busca os 6 últimos sets com estoque real usando Atributo.
     * No Blade, isso é chamado como $menu->recent_sets
     */
    public function getRecentSetsAttribute()
    {
        return \App\Models\Set::where('game_id', $this->game_id)
            ->whereHas('stockItems', function ($query) {
                $query->where('store_id', $this->store_id);
            })
            // AGORA SIM: o nome exato da coluna no seu banco de dados
            ->orderBy('released_at', 'desc') 
            ->take(6)
            ->get();
    }

    // Relacionamento com a Loja
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}