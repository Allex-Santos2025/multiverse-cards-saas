<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Catalog\CatalogPrint;

class PlayerDeck extends Model
{
    protected $table = 'player_decks';

    protected $fillable = [
        'player_user_id',
        'game_id',
        'name',
        'slug',
        'format',
        'description',
        'cover_image_path',
        'cover_catalog_print_id',
        'is_public',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function player(): BelongsTo
    {
        return $this->belongsTo(PlayerUser::class, 'player_user_id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id');
    }

    public function coverPrint(): BelongsTo
    {
        return $this->belongsTo(CatalogPrint::class, 'cover_catalog_print_id');
    }

    /**
     * Todas as cartas do deck (físicas e virtuais de todas as zonas)
     */
    public function cards(): HasMany
    {
        return $this->hasMany(PlayerDeckCard::class, 'player_deck_id');
    }

    /**
     * Retorna cartas de uma zona específica definida pelo Presenter do jogo
     * (ex: 'main', 'side', 'leader', 'extra', 'don', 'commander')
     */
    public function cardsInBoard(string $boardType): HasMany
    {
        return $this->cards()->where('board_type', $boardType);
    }

    /**
     * Retorna a taxa de conclusão física do deck (0% a 100%)
     */
    public function getPhysicalCompletionRateAttribute(): int
    {
        $total = $this->cards()->sum('quantity');
        if ($total === 0) return 0;

        $physical = $this->cards()->where('is_virtual', false)->sum('quantity');
        return (int) round(($physical / $total) * 100);
    }
}
