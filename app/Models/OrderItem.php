<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'store_id',
        'stock_item_id',
        'item_name',     // <--- ESSENCIAL ESTAR AQUI
        'unit_price',    // <--- ESSENCIAL ESTAR AQUI
        'quantity'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}