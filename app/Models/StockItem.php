<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <--- Importante para a lixeira
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Catalog\CatalogPrint;

class StockItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_items';

    protected $fillable = [
        'store_id',
        'catalog_print_id',
        'price',
        'quantity',
        'condition',    // NM, SP...
        'language',     // en, pt...
        'extras',       // JSON: { "foil": true }
        'comments',     // Texto livre
        'real_photos',  // JSON: ["path/to/img1.jpg"]
    ];

    /**
     * Casts: Transforma dados brutos do banco em tipos PHP utilizáveis.
     * Fundamental para os campos JSON funcionarem como Arrays.
     */
    protected $casts = [
        'price'       => 'decimal:2',
        'quantity'    => 'integer',
        'extras'      => 'array', // O Laravel faz o json_decode/encode sozinho
        'real_photos' => 'array', // O Laravel faz o json_decode/encode sozinho
    ];

    /**
     * Relacionamento: A loja dona deste item.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relacionamento: A "identidade" da carta (Imagem, Nome, Edição).
     */
    public function catalogPrint(): BelongsTo
    {
        return $this->belongsTo(CatalogPrint::class, 'catalog_print_id');
    }

    /**
     * Acessor: Atalho para pegar o Conceito (Nome Abstrato) através do Print.
     * Uso: $stockItem->concept->name
     */
    public function getConceptAttribute()
    {
        return $this->catalogPrint->concept ?? null;
    }
}