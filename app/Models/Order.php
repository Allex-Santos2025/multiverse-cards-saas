<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'player_user_id',
        'total_amount',
        'payment_method',
        'payment_status',
        'gateway_transaction_id',
        'gateway_payment_url'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippings()
    {
        return $this->hasMany(OrderShipping::class);
    }
}
