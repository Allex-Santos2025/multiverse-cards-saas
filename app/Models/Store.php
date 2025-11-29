<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    /**
     * Os atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'name',
        'url_slug',
        'slogan',
        'user_id', // Chave de posse
        'purchase_margin_cash',
        'purchase_margin_credit',
        'max_loyalty_discount',
        'pix_discount_rate',
        'store_zip_code',
        'store_state_code',
        'is_active',
        'is_template', // Chave de modelo
    ];
    
    public function stockItems(): HasMany
    {
        return $this->hasMany(StockItem::class);
    }

    // You might also have a relationship for users here
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}


