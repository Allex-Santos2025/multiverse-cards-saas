<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Set;

class CatalogPrint extends Model
{
    protected $table = 'catalog_prints';

    protected $fillable = [
        'concept_id',
        'set_id',
        'image_path', // Caminho universal da imagem
        'specific_type', // Polimorfismo (Aponta para MtgPrint, BsPrint...)
        'specific_id',
    ];

    // Relação Polimórfica: Traz os dados físicos específicos
    public function specific(): MorphTo
    {
        return $this->morphTo();
    }

    public function concept(): BelongsTo
    {
        return $this->belongsTo(CatalogConcept::class, 'concept_id');
    }

    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }
}