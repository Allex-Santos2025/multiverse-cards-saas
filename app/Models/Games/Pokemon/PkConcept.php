<?php

namespace App\Models\Games\Pokemon;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Catalog\CatalogConcept;

class PkConcept extends Model
{
    protected $table = 'pk_concepts';

    protected $fillable = [
        'supertype',
        'hp',
        'level',
        'types',
        'subtypes',
        'attacks',
        'abilities',
        'weaknesses',
        'resistances',
        'retreat_cost',
        'evolves_from',
        'evolves_to',
        'rules_text',
        
        // Campos Full Data (Confirmados no banco)
        'national_pokedex_numbers',
        'legalities',
        'regulation_mark',
        'ancient_trait',
    ];

    protected $casts = [
        'types' => 'array',
        'subtypes' => 'array',
        'attacks' => 'array',
        'abilities' => 'array',
        'weaknesses' => 'array',
        'resistances' => 'array',
        'retreat_cost' => 'array',
        'evolves_to' => 'array',
        
        // Casts Novos
        'national_pokedex_numbers' => 'array',
        'legalities' => 'array',
        'ancient_trait' => 'array',
    ];

    public function catalogConcept(): MorphOne
    {
        return $this->morphOne(CatalogConcept::class, 'specific');
    }
}