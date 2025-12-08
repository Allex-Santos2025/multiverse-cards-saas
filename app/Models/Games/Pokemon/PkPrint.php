<?php

namespace App\Models\Games\Pokemon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Catalog\CatalogPrint;

class PkPrint extends Model
{
    protected $table = 'pk_prints';

    protected $fillable = [
        'rarity',
        'artist',
        'number',
        'flavor_text',
        'language_code',
        
        // Campos Full Data (Confirmados no banco)
        'level', 
        'images',      // URLs originais da API
        'tcgplayer',   // Preços
        'cardmarket',  // Preços
    ];

    protected $casts = [
        'images' => 'array',
        'tcgplayer' => 'array',
        'cardmarket' => 'array',
    ];

    public function catalogPrint(): MorphOne
    {
        return $this->morphOne(CatalogPrint::class, 'specific');
    }
}