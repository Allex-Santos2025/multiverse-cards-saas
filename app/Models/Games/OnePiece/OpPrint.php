<?php

namespace App\Models\Games\OnePiece;

use App\Models\Catalog\CatalogPrint;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class OpPrint extends Model
{
    use HasFactory;

    protected $table = 'op_prints';

    protected $fillable = [
        'api_id',
        'concept_id',
        'collector_number',
        'rarity',
        'artist',
        'language_code',
        'flavor_text',
        'full_art',
        'has_foil',
        'textured',
        'promo',
        'reprint',
        'variation',
        'variation_of',
        'printed_name',
        'printed_type_line',
        'printed_text',
        'printed_trigger_text',
        'image_status',
        'released_at',
    ];

    protected $casts = [
        'full_art' => 'boolean',
        'has_foil' => 'boolean',
        'textured' => 'boolean',
        'promo' => 'boolean',
        'reprint' => 'boolean',
        'released_at' => 'date',
    ];

    public function concept(): BelongsTo
    {
        return $this->belongsTo(OpConcept::class, 'concept_id');
    }

    public function catalogPrint(): MorphOne
    {
        return $this->morphOne(CatalogPrint::class, 'specific');
    }
}
