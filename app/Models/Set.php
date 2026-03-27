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
        'is_fanmade',
        'digital', 
        'foil_only', 
        'api_id',
        'code',
        'name', 
        'name_pt',       // Adicionado: Nome em Português
        'translations',  // Adicionado: JSON com todos os idiomas
        'released_at',
        'set_type',
        'card_count',
        'icon_svg_uri',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'translations' => 'array',
        'is_fanmade' => 'boolean',
        'digital' => 'boolean',
        'foil_only' => 'boolean',
        'released_at' => 'date', 
        'card_count' => 'integer',
        'translations' => 'array', // Adicionado: Cast automático do JSON para Array
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
    /**
     * Relacionamento com o Estoque "Através" dos Prints
     * O Laravel vai pular a ponte: Set -> CatalogPrint -> StockItem
     */
    public function stockItems()
    {
        return $this->hasManyThrough(
            \App\Models\StockItem::class,
            \App\Models\Catalog\CatalogPrint::class,
            'set_id',            // Coluna na tabela catalog_prints que aponta para o set
            'catalog_print_id',  // Coluna na tabela stock_items que aponta para o print
            'id',                // ID local da tabela sets
            'id'                 // ID local da tabela catalog_prints
        );
    }
    
    public function getNomeLocalizadoAttribute()
    {
        // Se name_pt não for nulo e não for uma string vazia, ele usa o pt.
        // Se for vazio, ele devolve o name original.
        return !empty($this->name_pt) ? $this->name_pt : $this->name;
    }
}