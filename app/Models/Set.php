<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Catalog\CatalogPrint;

class Set extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',      
        'is_fanmade',
        'digital', 
        'foil_only', 
        'api_id',
        'code',
        'name', 
        'name_pt',
        'translations',
        'released_at',
        'set_type',
        'card_count',
        'icon_svg_uri',
    ];

    protected $casts = [
        'translations' => 'array',
        'is_fanmade'   => 'boolean',
        'digital'      => 'boolean',
        'foil_only'    => 'boolean',
        'released_at'  => 'date', 
        'card_count'   => 'integer',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    public function catalogPrints(): HasMany
    {
        return $this->hasMany(CatalogPrint::class, 'set_id');
    }

    public function stockItems()
    {
        return $this->hasManyThrough(
            \App\Models\StockItem::class,
            \App\Models\Catalog\CatalogPrint::class,
            'set_id',
            'catalog_print_id',
            'id',
            'id'
        );
    }

    public function collectionItems()
    {
        return $this->hasManyThrough(
            \App\Models\PlayerCollectionItem::class,
            \App\Models\Catalog\CatalogPrint::class,
            'set_id',
            'catalog_print_id',
            'id',
            'id'
        );
    }
    
    public function getNomeLocalizadoAttribute()
    {
        return !empty($this->name_pt) ? $this->name_pt : $this->name;
    }
}
