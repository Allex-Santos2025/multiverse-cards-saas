<?php

namespace App\Models\Games\OnePiece;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpConcept extends Model
{
    use HasFactory;

    protected $table = 'op_concepts';

    protected $fillable = [
        'card_code',
        'name',
        'name_ja',
        'card_type',
        'type_line',
        'colors',
        'attribute',
        'cost',
        'life',
        'power',
        'counter',
        'oracle_text',
        'trigger_text',
        'keywords',
        'families',
        'legalities',
    ];

    protected $casts = [
        'colors' => 'array',
        'keywords' => 'array',
        'families' => 'array',
        'legalities' => 'array',
        'cost' => 'integer',
        'life' => 'integer',
        'power' => 'integer',
        'counter' => 'integer',
    ];

    public function prints(): HasMany
    {
        return $this->hasMany(OpPrint::class, 'concept_id');
    }
}
