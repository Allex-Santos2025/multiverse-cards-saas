<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockItem extends Model
{
    use HasFactory;

    /**
     * A "lista de permissões" para o Laravel, com os nomes corretos das colunas.
     */
    protected $fillable = [
        'store_id',
        'card_id',
        'condition',
        'language',
        'is_foil',
        'quantity', // Corrigido de 'quantidade'
        'price',    // Corrigido de 'preço'
    ];

    /**
     * Define a ponte: Um item de estoque PERTENCE A uma Loja.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Define a ponte: Um item de estoque PERTENCE A um Card (impressão).
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class);
    }
}