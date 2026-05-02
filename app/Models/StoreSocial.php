<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSocial extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'platform',
        'url',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}