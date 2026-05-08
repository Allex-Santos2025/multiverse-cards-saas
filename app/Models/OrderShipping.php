<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderShipping extends Model
{
    protected $fillable = [
        'order_id',
        'store_id',
        'shipping_method_key',
        'shipping_method_name',
        'shipping_cost',
        'destination_zip_code',
        'tracking_code',
        'shipping_status'
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}