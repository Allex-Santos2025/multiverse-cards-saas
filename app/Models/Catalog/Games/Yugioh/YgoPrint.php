<?php

namespace App\Models\Catalog\Games\Yugioh;

use Illuminate\Database\Eloquent\Model;
use App\Models\Catalog\CatalogPrint;

class YgoPrint extends Model
{
    protected $table = 'ygo_prints';

    protected $fillable = [
        'api_id',
        'collector_number',
        'language_code',
        'rarity',
        'rarity_code',
        'printed_name',
        'frame_type',
        'printed_text',
        'printed_type_line',
        'flavor_text',
        'prices',
        'cardmarket_id',
        'tcgplayer_id',
    ];

    protected $casts = [
        'prices' => 'array',
    ];

    public function print()
    {
        return $this->morphOne(CatalogPrint::class, 'specific');
    }
}
