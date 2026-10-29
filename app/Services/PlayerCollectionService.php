<?php

namespace App\Services;

use App\Models\PlayerBinder;
use App\Models\PlayerCollectionItem;
use App\Models\Catalog\CatalogPrint;
use App\Models\Set;
use App\Models\Game;
use App\Models\StockItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlayerCollectionService
{
    public function getFeaturedBinders(int $playerUserId): Collection
    {
        return PlayerBinder::where('player_user_id', $playerUserId)
            ->withCount([
                'slots as total_cards_stored' => function ($query) {
                    $query->select(DB::raw('COALESCE(SUM(quantity_in_slot), 0)'));
                }
            ])
            ->latest('updated_at')
            ->take(3)
            ->get();
    }

    public function getAvailableGamesForPlayer(int $playerUserId): Collection
    {
        $gameIds = PlayerCollectionItem::where('player_user_id', $playerUserId)
            ->join('catalog_prints', 'player_collection_items.catalog_print_id', '=', 'catalog_prints.id')
            ->join('sets', 'catalog_prints.set_id', '=', 'sets.id')
            ->distinct()
            ->pluck('sets.game_id');

        if ($gameIds->isEmpty()) {
            return collect();
        }

        return Game::whereIn('id', $gameIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    /**
     * Resumo consolidado com patrimônio real e custo para fechar (1x e 4x)
     */
    public function getCompletedSetsSummary(int $playerUserId, ?int $gameId = null): Collection
    {
        $query = PlayerCollectionItem::where('player_user_id', $playerUserId)
            ->with(['catalogPrint.set.game']);

        if ($gameId) {
            $query->whereHas('catalogPrint.set', fn($q) => $q->where('game_id', $gameId));
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            return collect();
        }

        return $items->groupBy(function ($item) {
            $lang = strtolower(trim($item->language ?: 'pt'));
            $setId = $item->catalogPrint->set_id ?? 0;
            return "{$setId}_{$lang}";
        })
        ->map(function ($setLangItems) {
            $firstItem = $setLangItems->first();
            $set = $firstItem->catalogPrint->set ?? null;
            if (!$set) return null;

            $language = strtolower(trim($firstItem->language ?: 'pt'));
            $totalInSet = (int) ($set->card_count ?? 0);

            // Carrega média de preços das cartas do set (via estoque local ou catálogo)
            $pricesCache = $this->getSetPricesAverages($set->id);

            $regularItems = $setLangItems->filter(fn($i) => !in_array('foil', array_map('strtolower', (array) $i->extras)));
            $foilItems    = $setLangItems->filter(fn($i) => in_array('foil', array_map('strtolower', (array) $i->extras)));

            return [
                'set_id'          => $set->id,
                'set_code'        => $set->code,
                'set_name'        => $set->nome_localizado,
                'game_id'         => $set->game_id,
                'game_name'       => $set->game->name ?? '',
                'language'        => strtoupper($language),
                'total_cards'     => $totalInSet,

                'regular' => $this->calculateMetrics($regularItems, $totalInSet, $pricesCache, false),
                'foil'    => $this->calculateMetrics($foilItems, $totalInSet, $pricesCache, true),
            ];
        })
        ->filter()
        ->filter(fn($s) => $s['regular']['has_cards'] || $s['foil']['has_cards'])
        ->values();
    }

    private function calculateMetrics(Collection $items, int $totalInSet, array $pricesCache, bool $isFoil): array
    {
        $hasCards = $items->isNotEmpty();
        if (!$hasCards && $totalInSet === 0) {
            return ['has_cards' => false];
        }

        $uniqueCount = $items->pluck('catalog_print_id')->unique()->count();
        $totalCopies = $items->sum('quantity');

        // Patrimônio 1x: soma de 1 unidade de cada carta única que possui
        $patrimonio1x = 0;
        foreach ($items->unique('catalog_print_id') as $item) {
            $pId = $item->catalog_print_id;
            $price = $pricesCache[$pId][$isFoil ? 'foil' : 'regular'] ?? ($item->acquisition_price ?: 5.00);
            $patrimonio1x += $price;
        }

        // Patrimônio 4x: soma de até 4 unidades de cada
        $patrimonio4x = 0;
        foreach ($items->groupBy('catalog_print_id') as $pId => $copies) {
            $price = $pricesCache[$pId][$isFoil ? 'foil' : 'regular'] ?? 5.00;
            $qty = min(4, $copies->sum('quantity'));
            $patrimonio4x += ($price * $qty);
        }

        $missing1x = max(0, $totalInSet - $uniqueCount);
        $missing4x = max(0, ($totalInSet * 4) - min($totalInSet * 4, $totalCopies));

        // Preço médio unitário das cartas do set para estimativa de fechamento
        $avgPrice = !empty($pricesCache) ? (array_sum(array_column($pricesCache, $isFoil ? 'foil' : 'regular')) / max(1, count($pricesCache))) : 7.50;

        $costToFinish1x = $missing1x * $avgPrice;
        $costToFinish4x = $missing4x * $avgPrice;

        return [
            'has_cards'          => $hasCards,
            'unique_count'       => $uniqueCount,
            'total_copies'       => $totalCopies,
            'total_target_1x'    => $totalInSet,
            'total_target_4x'    => $totalInSet * 4,
            'pct_1x'             => $totalInSet > 0 ? min(100, round(($uniqueCount / $totalInSet) * 100)) : 0,
            'pct_4x'             => $totalInSet > 0 ? min(100, round(($totalCopies / ($totalInSet * 4)) * 100)) : 0,
            'patrimonio_1x'      => $patrimonio1x,
            'patrimonio_4x'      => $patrimonio4x,
            'missing_1x'         => $missing1x,
            'missing_4x'         => $missing4x,
            'cost_to_finish_1x'  => $costToFinish1x,
            'cost_to_finish_4x'  => $costToFinish4x,
        ];
    }

    private function getSetPricesAverages(int $setId): array
    {
        // 1. Preços praticados no estoque
        $stockPrices = StockItem::withoutGlobalScopes()
            ->whereHas('catalogPrint', fn($q) => $q->where('set_id', $setId))
            ->select(
                'catalog_print_id',
                DB::raw('AVG(price) as avg_price'),
                'extras'
            )
            ->groupBy('catalog_print_id', 'extras')
            ->get();

        $cache = [];
        foreach ($stockPrices as $sp) {
            $isFoil = in_array('foil', array_map('strtolower', (array)$sp->extras));
            $type = $isFoil ? 'foil' : 'regular';
            $cache[$sp->catalog_print_id][$type] = (float) $sp->avg_price;
        }

        return $cache;
    }
}
