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
    'concept_id', 'set_id', 'image_path', 'printed_name', 
    'language_code', 'collector_number', 
    'rarity', 'type_line', 'cmc', 'mana_cost', // <--- Adicione estes
    'specific_type', 'specific_id',
];
    // Adicione essa relação para facilitar a busca do estoque
    public function stockItems()
    {
        return $this->hasMany(\App\Models\StockItem::class, 'catalog_print_id');
    }

    // Helper para pegar o estoque APENAS da loja atual (muito útil na view)
    public function myStock()
    {
        return $this->hasOne(\App\Models\StockItem::class, 'catalog_print_id')
            ->where('store_id', auth('store_user')->user()->store_id ?? 0);
    }

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