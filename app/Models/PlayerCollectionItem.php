<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Catalog\CatalogPrint;

class PlayerCollectionItem extends Model
{
    use HasFactory;

    protected $table = 'player_collection_items';

    protected $fillable = [
        'player_user_id',
        'catalog_print_id',
        'language',
        'condition',
        'extras',
        'quantity',
        'acquisition_price',
    ];

    protected $casts = [
        'extras'            => 'array',
        'quantity'          => 'integer',
        'acquisition_price' => 'decimal:2',
    ];

    public function playerUser(): BelongsTo
    {
        return $this->belongsTo(PlayerUser::class, 'player_user_id');
    }

    public function catalogPrint(): BelongsTo
    {
        return $this->belongsTo(CatalogPrint::class, 'catalog_print_id');
    }

    public function binderSlot(): HasOne
    {
        return $this->hasOne(PlayerBinderSlot::class, 'player_collection_item_id');
    }

    public function binderSlots(): HasMany
    {
        return $this->hasMany(PlayerBinderSlot::class, 'player_collection_item_id');
    }
}
