<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Game;

class CatalogConcept extends Model
{
    protected $table = 'catalog_concepts';

    protected $fillable = [
        'game_id',
        'name',
        'slug',
        'search_names', // JSON com apelidos/nomes para busca
        'specific_type', // Polimorfismo (Aponta para MtgConcept, BsConcept...)
        'specific_id',
    ];

    protected $casts = [
        'search_names' => 'array',
    ];

    // Relação Polimórfica: Traz os dados específicos
    public function specific(): MorphTo
    {
        return $this->morphTo();
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function prints(): HasMany
    {
        return $this->hasMany(CatalogPrint::class, 'concept_id');
    }
}