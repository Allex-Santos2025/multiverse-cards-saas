<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'color',
        'order_position',
    ];

    public function shippings()
    {
        return $this->hasMany(OrderShipping::class, 'order_status_id');
    }
}
