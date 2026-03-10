<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CatalogProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'game_id',
        'type',
        'name',
        'description',
        'barcode',
        'image_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relacionamento com o Jogo (Magic, Pokémon, etc)
    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    // Relacionamento com os itens de estoque dos lojistas
    public function stockItems()
    {
        return $this->hasMany(StockItem::class);
    }
}
