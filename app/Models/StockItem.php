<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <--- Importante para a lixeira
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Catalog\CatalogPrint;

class StockItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stock_items';

    protected $fillable = [
        'store_id',
        'catalog_print_id',
        'catalog_product_id',
        'price',
        'quantity',
        'condition',    // NM, SP...
        'language',     // en, pt...
        'extras',       // JSON: { "foil": true }
        'comments',     // Texto livre
        'real_photos',  // JSON: ["path/to/img1.jpg"]

        'discount_percent',
        'discount_start',
        'discount_end',
        'is_promotion',
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
    
    // Relacionamento com o Produto Global (Selados ou Acessórios)
    public function catalogProduct()
    {
        return $this->belongsTo(CatalogProduct::class);
    }
    protected function finalPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Se a flag de promoção estiver ativa E o desconto for maior que 0
                if ($this->is_promotion && $this->discount_percent > 0) {
                    $desconto = $this->price * ($this->discount_percent / 100);
                    return round($this->price - $desconto, 2);
                }
                
                // Se não, retorna o preço cheio intacto
                return $this->price;
            }
        );
    }
    public function getConceptAttribute()
    {
        return $this->catalogPrint->concept ?? null;
    }
}