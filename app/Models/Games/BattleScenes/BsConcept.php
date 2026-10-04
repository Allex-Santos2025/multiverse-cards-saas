<?php

namespace App\Models\Games\BattleScenes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Models\Catalog\CatalogConcept;

class BsConcept extends Model
{
    protected $table = 'bs_concepts';

    protected $fillable = [
        'alter_ego',
        'type_line',
        'affiliation',
        'affiliations',
        'power',
        'toughness',
        'cost',
        'rules_text',
        'flavor_text',
        'skills',
    ];

    protected $casts = [
        'affiliations' => 'array',
        'skills' => 'array',
    ];

    public function catalogConcept(): MorphOne
    {
        return $this->morphOne(CatalogConcept::class, 'specific');
    }
}
