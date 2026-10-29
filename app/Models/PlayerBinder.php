<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerBinder extends Model
{
    use HasFactory;

    protected $table = 'player_binders';

    protected $fillable = [
        'player_user_id',
        'name',
        'slug',
        'description',
        'pocket_layout',
        'factory_sheets_count',
        'current_sheets_count',
        'is_locked_sheets', // Trava física estilo fichário costurado
        'cover_material',
        'cover_image_path',
        'strap_color',
        'is_public',
    ];

    protected $casts = [
        'factory_sheets_count' => 'integer',
        'current_sheets_count' => 'integer',
        'is_locked_sheets'     => 'boolean',
        'is_public'            => 'boolean',
    ];

    public function playerUser(): BelongsTo
    {
        return $this->belongsTo(PlayerUser::class, 'player_user_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(PlayerBinderSlot::class, 'binder_id');
    }

    public function getPocketsPerPageAttribute(): int
    {
        return match($this->pocket_layout) {
            '2x2' => 4,
            '3x3' => 9,
            '4x4' => 16,
            default => 0,
        };
    }

    public function getFactoryCapacityAttribute(): int
    {
        if ($this->pocket_layout === 'bulk') {
            return 0;
        }

        return $this->factory_sheets_count * ($this->pockets_per_page * 2);
    }

    public function getCurrentCapacityAttribute(): int
    {
        if ($this->pocket_layout === 'bulk') {
            return 0;
        }

        return $this->current_sheets_count * ($this->pockets_per_page * 2);
    }

    public function getTotalCardsAttribute(): int
    {
        return (int) $this->slots()->sum('quantity_in_slot');
    }
}
