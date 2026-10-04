<?php

namespace App\Models\Games\BattleScenes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Catalog\CatalogPrint;

class BsPrint extends Model
{
    protected $table = 'bs_prints';

    protected $fillable = [
        'rarity',
        'artist',
        'number',
        'flavor_text',
        'language_code',
        'images',
        'market_prices',
    ];

    protected $casts = [
        'images' => 'array',
        'market_prices' => 'array',
    ];

    public function catalogPrint(): MorphOne
    {
        return $this->morphOne(CatalogPrint::class, 'specific');
    }
}
