<?php

namespace App\Models\Games\Magic;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Catalog\CatalogConcept;

class MtgConcept extends Model
{
    protected $table = 'mtg_concepts';

    // A lista completa para não perder nada do Magic
    protected $fillable = [
        'oracle_id',
        'mana_cost',
        'cmc',
        'type_line',
        'oracle_text',
        'power',
        'toughness',
        'loyalty',
        'produced_mana',
        'color_indicator',
        'edhrec_rank',
        'penny_rank',
        'max_copies',
        'colors',
        'color_identity',
        'keywords',
        'legalities',
    ];

    protected $casts = [
        'colors' => 'array',
        'color_identity' => 'array',
        'keywords' => 'array',
        'legalities' => 'array',
        'produced_mana' => 'array',
        'color_indicator' => 'array',
    ];

    // Link reverso para o pai
    public function catalogConcept(): MorphOne
    {
        return $this->morphOne(CatalogConcept::class, 'specific');
    }
}