<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'store_id',
        'stock_item_id',
        'quantity',
        'price',
    ];

    // Relação com o item do estoque (para pegar nome, imagem, etc)
    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }

    // Relação com a loja dona do item
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Relação com o usuário logado (se houver)
    public function user()
    {
        return $this->belongsTo(User::class); // Ou o Model de cliente global que você usa
    }
}