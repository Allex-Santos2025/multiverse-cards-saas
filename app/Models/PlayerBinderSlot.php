<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerBinderSlot extends Model
{
    use HasFactory;

    protected $table = 'player_binder_slots';

    protected $fillable = [
        'binder_id',
        'player_collection_item_id',
        'page_number',
        'slot_position',
        'quantity_in_slot',
    ];

    protected $casts = [
        'page_number'      => 'integer',
        'slot_position'    => 'integer',
        'quantity_in_slot' => 'integer',
    ];

    public function binder(): BelongsTo
    {
        return $this->belongsTo(PlayerBinder::class, 'binder_id');
    }

    public function collectionItem(): BelongsTo
    {
        return $this->belongsTo(PlayerCollectionItem::class, 'player_collection_item_id');
    }
}
