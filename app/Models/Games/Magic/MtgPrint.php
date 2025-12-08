<?php

namespace App\Models\Games\Magic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Catalog\CatalogPrint;

class MtgPrint extends Model
{
    protected $table = 'mtg_prints';

    protected $fillable = [
        'scryfall_id',
        'rarity',
        'artist',
        'collector_number',
        'language_code',
        'flavor_text',
        
        // Dados Técnicos de Impressão
        'frame',
        'border_color',
        'full_art',
        'textless',
        'promo',
        'reprint',
        'variation',
        'illustration_id',
        'has_foil',
        'nonfoil',
        'etched',
        'oversized',
        'digital',
        'security_stamp',
        'watermark',
        'card_back_id',
        'highres_image',
        'image_status',
        'released_at',
        
        // JSONs complexos
        'finishes',
        'prices',
        'related_uris',
        'purchase_uris',
        'multiverse_ids',
        
        // IDs Externos
        'mtgo_id',
        'mtgo_foil_id',
        'arena_id',
        'tcgplayer_id',
        'tcgplayer_etched_id',
        'cardmarket_id',
    ];

    protected $casts = [
        'finishes' => 'array',
        'prices' => 'array',
        'related_uris' => 'array',
        'purchase_uris' => 'array',
        'multiverse_ids' => 'array',
        'full_art' => 'boolean',
        'textless' => 'boolean',
        'promo' => 'boolean',
        'reprint' => 'boolean',
        'variation' => 'boolean',
        'has_foil' => 'boolean',
        'nonfoil' => 'boolean',
        'etched' => 'boolean',
        'oversized' => 'boolean',
        'digital' => 'boolean',
        'highres_image' => 'boolean',
    ];

    public function catalogPrint(): MorphOne
    {
        return $this->morphOne(CatalogPrint::class, 'specific');
    }
}