<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable; // 1. IMPORTAÇÃO DO SCOUT AQUI
use App\Models\Set;

class CatalogPrint extends Model
{
    use Searchable; // 2. LIGANDO O MOTOR NO MODEL

    protected $table = 'catalog_prints';

    protected $fillable = [
        'concept_id', 'set_id', 'image_path', 'printed_name', 
        'language_code', 'collector_number', 
        'rarity', 'type_line', 'cmc', 'mana_cost', // <--- Adicione estes
        'specific_type', 'specific_id',
    ];

    // ========================================================
    // 3. A BARREIRA DE ENTRADA DO MEILISEARCH
    // Só envia para o motor de busca se retornar TRUE.
    // ========================================================
    public function shouldBeSearchable()
    {
        // Barra cartas que começam com A-, H-, 1-, 2-, 3- ou 4-
        return !preg_match('/^(A|H|1|2|3|4)-/', $this->printed_name);
    }

    // Adicione essa relação para facilitar a busca do estoque
    public function stockItems()
    {
        return $this->hasMany(\App\Models\StockItem::class, 'catalog_print_id');
    }

    // Helper para pegar o estoque APENAS da loja atual (muito útil na view)
    public function myStock()
    {
        return $this->hasMany(\App\Models\StockItem::class, 'catalog_print_id')
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