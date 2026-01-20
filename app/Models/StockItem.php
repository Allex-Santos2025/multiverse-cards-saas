<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Catalog\CatalogPrint; // Importa o novo Model

class StockItem extends Model
{
    use HasFactory;

    /**
     * A "lista de permissões" para o Laravel, com os nomes corretos das colunas.
     * CORREÇÃO: Renomeado 'card_id' para 'catalog_print_id'.
     */
    protected $fillable = [
        'store_id',
        'catalog_print_id', // <--- NOVO CAMPO DE RELACIONAMENTO
        'condition',
        'language',
        'is_foil',
        'quantity',
        'price',
    ];

    /**
     * Define a ponte: Um item de estoque PERTENCE A uma Loja.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Define a ponte: Um item de estoque PERTENCE A um CatalogPrint (impressão).
     * CORREÇÃO: Relacionamento alterado para CatalogPrint.
     */
    public function catalogPrint(): BelongsTo
    {
        return $this->belongsTo(CatalogPrint::class, 'catalog_print_id');
    }

    // --- RELAÇÃO INDIRETA COM O CONCEITO ---

    /**
     * Relação indireta para buscar o Conceito Pai através do Print.
     * Necessário para a busca e filtro no Resource.
     */
    public function concept()
    {
        return $this->hasOneThrough(
            \App\Models\Catalog\CatalogConcept::class,
            CatalogPrint::class,
            'id', // Chave local na tabela CatalogPrint
            'id', // Chave local na tabela CatalogConcept
            'catalog_print_id', // Chave estrangeira no StockItem
            'concept_id' // Chave estrangeira no CatalogPrint (se existir a relação no CatalogPrint)
        );
    }
}