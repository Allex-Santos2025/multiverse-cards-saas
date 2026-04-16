<?php

namespace App\Models\Catalog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;
use App\Models\Set;

class CatalogPrint extends Model
{
    use Searchable;

    protected $table = 'catalog_prints';

    protected $fillable = [
        'concept_id', 'set_id', 'image_path', 'printed_name', 
        'language_code', 'collector_number', 
        'rarity', 'type_line', 'cmc', 'mana_cost',
        'specific_type', 'specific_id',
    ];

    // ========================================================
    // A BARREIRA DE ENTRADA DO MEILISEARCH
    // ========================================================
    public function shouldBeSearchable()
    {
        // Barra cartas que começam com A-, H-, 1-, 2-, 3- ou 4-
        return !preg_match('/^(A|H|1|2|3|4)-/', $this->printed_name);
    }

    // ========================================================
    // O SEGREDO DO MEILISEARCH: Ensinando o que ele deve indexar
    // ========================================================
    public function toSearchableArray(): array
    {
        // 1. Pega as colunas nativas
        $array = $this->toArray();

        // 2. Carrega a relação específica (ex: MtgPrint, PkmPrint)
        $specific = $this->specific;

        // 3. Injeta as flags de Foil e Etched no índice do Meilisearch
        $array['is_foil']   = $specific->is_foil ?? $specific->has_foil ?? false;
        $array['is_etched'] = $specific->is_etched ?? $specific->has_etched ?? false;

        // 4. Injeta o nome da edição para facilitar a busca e a renderização do card
        $array['set_name']  = $this->set->name ?? null;

        return $array;
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