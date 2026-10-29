<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Catalog\CatalogPrint;

class PlayerDeckCard extends Model
{
    protected $table = 'player_deck_cards';

    protected $fillable = [
        'player_deck_id',
        'catalog_print_id',
        'player_collection_item_id',
        'quantity',
        'board_type',
        'is_virtual',
    ];

    protected $casts = [
        'is_virtual' => 'boolean',
        'quantity'   => 'integer',
    ];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(PlayerDeck::class, 'player_deck_id');
    }

    public function catalogPrint(): BelongsTo
    {
        return $this->belongsTo(CatalogPrint::class, 'catalog_print_id');
    }

    public function collectionItem(): BelongsTo
    {
        return $this->belongsTo(PlayerCollectionItem::class, 'player_collection_item_id');
    }
}
