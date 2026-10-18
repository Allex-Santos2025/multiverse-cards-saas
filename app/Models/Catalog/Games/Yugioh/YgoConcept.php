<?php

namespace App\Models\Catalog\Games\Yugioh;

use Illuminate\Database\Eloquent\Model;
use App\Models\Catalog\CatalogConcept;

class YgoConcept extends Model
{
    protected $table = 'ygo_concepts';

    protected $fillable = [
        'attribute',
        'race',
        'type_line',
        'level',
        'scale',
        'atk',
        'def',
        'rules_text',
        'pendulum_text',
    ];

    public function concept()
    {
        return $this->morphOne(CatalogConcept::class, 'specific');
    }
}
