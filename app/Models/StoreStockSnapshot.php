<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreStockSnapshot extends Model
{
    protected $fillable = [
        'store_id',
        'snapshot_date',
        'total_items',
        'total_value',
        'game_breakdown',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'game_breakdown' => 'array',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}