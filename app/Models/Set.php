<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Set extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'game_id',      
        // 'tcg_name', << REMOVIDO: Coluna removida para centralizar a unicidade em game_id
        'is_fanmade',
        'digital', 
        'foil_only', 
        // CORREÇÃO ESSENCIAL: Usar os nomes de coluna com prefixo mtg_
        'mtg_scryfall_id', // CORRIGIDO: Deve ser prefixado
        'mtg_code',        // CORRIGIDO: Deve ser prefixado
        'mtg_icon_svg_uri',// CORRIGIDO: Deve ser prefixado
        'name',
        'code', 
        'released_at',
        'set_type',
        'card_count',
        'icon_svg_uri', // Mantemos este aqui, mas ele não deve ser preenchido pela ScryfallApi
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_fanmade' => 'boolean',
        'digital' => 'boolean',
        'foil_only' => 'boolean',
        'released_at' => 'date', // Garante que é tratado como um objeto de data
        'card_count' => 'integer',
    ];

    /**
     * Define o relacionamento: Um Set (Coleção) PERTENCE A um Game (Jogo).
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Define o relacionamento: Um Set (Coleção) TEM MUITOS Cards (Cartas).
     */
    public function cards(): HasMany
    {
        // NOTA: Assumindo que o Card Model existe
        return $this->hasMany(Card::class);
    }
}