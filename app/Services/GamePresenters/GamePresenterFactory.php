<?php

namespace App\Services\GamePresenters;

use App\Models\Catalog\CatalogPrint;

class GamePresenterFactory
{
    public static function make(
        int $gameId,
        ?object $concept = null,
        ?CatalogPrint $activePrint = null,
        ?string $localizedName = null
    ): GamePresenterInterface {
        return match ($gameId) {
            1 => new MagicPresenter($concept, $activePrint, $localizedName),
            2 => new PokemonPresenter($concept, $activePrint, $localizedName),
            4 => new BattleScenesPresenter($concept, $activePrint, $localizedName),
            default => new MagicPresenter($concept, $activePrint, $localizedName),
        };
    }
}
