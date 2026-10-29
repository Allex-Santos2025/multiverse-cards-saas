<?php

namespace App\Livewire\Lobby;

use Livewire\Component;
use App\Models\PlayerBinder;
use App\Models\PlayerCollectionItem;
use App\Services\GamePresenters\GamePresenterFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $loja;

    public $modalHistoryOpen = false;
    public $historyPeriod = 'ALL';

    public function mount($loja = null)
    {
        $this->loja = $loja;
    }

    public function openHistoryModal(): void
    {
        $this->modalHistoryOpen = true;
    }

    public function closeHistoryModal(): void
    {
        $this->modalHistoryOpen = false;
    }

    public function setPeriod(string $period): void
    {
        if (in_array($period, ['1M', '3M', '6M', '1Y', 'ALL'])) {
            $this->historyPeriod = $period;
        }
    }

    /**
     * Resolve o preço de mercado da estampa delegando ao GamePresenter do jogo
     */
    protected function resolveCardMarketPrice($print, array $extras, $paidPrice, array &$presenterCache): float
    {
        if (!$print) {
            return (float) ($paidPrice ?: 0.00);
        }

        $gameId = $print->set->game_id ?? 1;

        if (!isset($presenterCache[$gameId])) {
            try {
                $presenterCache[$gameId] = GamePresenterFactory::make($gameId);
            } catch (\Throwable $e) {
                $presenterCache[$gameId] = null;
            }
        }

        $presenter = $presenterCache[$gameId];

        // 1. Se o Presenter possui método dedicado para cotação de mercado da estampa
        if ($presenter && method_exists($presenter, 'getPrintMarketPrice')) {
            $val = (float) $presenter->getPrintMarketPrice($print, $extras);
            if ($val > 0) return $val;
        }

        // 2. Fallback via presenter se possuir resolveMarketPrice genérico
        if ($presenter && method_exists($presenter, 'resolveMarketPrice')) {
            $val = (float) $presenter->resolveMarketPrice($print, $extras);
            if ($val > 0) return $val;
        }

        // 3. Fallback lendo coluna prices do specific caso o presenter do jogo ainda esteja sendo expandido
        $specific = $print->specific;
        if (!$specific && class_exists($print->specific_type)) {
            $specific = ($print->specific_type)::find($print->specific_id);
        }

        if ($specific && isset($specific->prices)) {
            $prices = is_array($specific->prices) ? $specific->prices : json_decode($specific->prices, true);
            if (is_array($prices)) {
                $isFoil = in_array('foil', array_map('strtolower', $extras));
                $usd = $isFoil && !empty($prices['usd_foil']) ? (float)$prices['usd_foil'] : (!empty($prices['usd']) ? (float)$prices['usd'] : null);
                if ($usd) {
                    return round($usd * 5.60, 2);
                }
            }
        }

        return (float) ($paidPrice ?: 0.00);
    }

    public function render()
    {
        $player = Auth::guard('player')->user();
        $playerId = $player?->id;

        // 1. Fichários Reais Cadastrados (Top 3 por ocupação/relevância)
        $binders = PlayerBinder::where('player_user_id', $playerId)
            ->withCount('slots')
            ->orderBy('slots_count', 'desc')
            ->take(3)
            ->get();

        $totalBindersCount = PlayerBinder::where('player_user_id', $playerId)->count();

        // 2. Acervo Geral do Jogador
        $collectionItems = PlayerCollectionItem::where('player_user_id', $playerId)
            ->with(['catalogPrint.concept', 'catalogPrint.set', 'catalogPrint.specific'])
            ->orderBy('created_at', 'asc')
            ->get();

        $totalCardsCount = $collectionItems->sum('quantity');

        $totalInvested = 0.00;
        $totalMarketValue = 0.00;
        $presenterCache = [];

        $rankedItems = $collectionItems->map(function ($item) use (&$totalInvested, &$totalMarketValue, &$presenterCache) {
            $print = $item->catalogPrint;
            
            // Cotação obtida via GamePresenter
            $marketPrice = $this->resolveCardMarketPrice($print, (array)$item->extras, $item->acquisition_price, $presenterCache);
            $paidPrice = (float) ($item->acquisition_price ?: 0.00);
            
            $itemTotalPaid = $paidPrice * $item->quantity;
            $itemTotalMarket = $marketPrice * $item->quantity;

            $totalInvested += $itemTotalPaid;
            $totalMarketValue += $itemTotalMarket;

            $diffPct = 0;
            if ($paidPrice > 0 && $marketPrice > 0) {
                $diffPct = round((($marketPrice - $paidPrice) / $paidPrice) * 100, 1);
            }

            return [
                'id'           => $item->id,
                'name'         => $print->printed_name ?: ($print->concept->name ?? 'Carta'),
                'set_name'     => $print->set->name ?? '',
                'set_code'     => $print->set->code ?? '',
                'number'       => $print->collector_number,
                'condition'    => $item->condition,
                'extras'       => (array) $item->extras,
                'image'        => $print->image_path ?: '/images/placeholder-card.png',
                'quantity'     => $item->quantity,
                'market_price' => $marketPrice,
                'paid_price'   => $paidPrice,
                'diff_pct'     => $diffPct,
                'diff_trend'   => $diffPct > 0 ? 'up' : ($diffPct < 0 ? 'down' : 'flat'),
                'created_at'   => $item->created_at,
            ];
        });

        // 3. Métricas de Valorização Geral
        $portfolioDiffPct = 0;
        $portfolioTrend = 'flat';
        $portfolioNominalGain = $totalMarketValue - $totalInvested;

        if ($totalInvested > 0) {
            $portfolioDiffPct = round(($portfolioNominalGain / $totalInvested) * 100, 1);
            if ($portfolioDiffPct > 0) $portfolioTrend = 'up';
            elseif ($portfolioDiffPct < 0) $portfolioTrend = 'down';
        }

        // Top 5 Joias da Coroa
        $top5Cards = $rankedItems->sortByDesc('market_price')->take(5)->values();

        // 4. Quantidade de Decks
        $totalDecksCount = 0;
        if (DB::getSchemaBuilder()->hasTable('player_decks')) {
            $totalDecksCount = DB::table('player_decks')->where('player_user_id', $playerId)->count();
        }

        // 5. Linha do Tempo Financeira
        $timelineDates = [];
        $timelineInvested = [];
        $timelineMarket = [];

        $filteredHistory = $collectionItems;
        if ($this->historyPeriod !== 'ALL') {
            $threshold = match($this->historyPeriod) {
                '1M' => Carbon::now()->subMonth(),
                '3M' => Carbon::now()->subMonths(3),
                '6M' => Carbon::now()->subMonths(6),
                '1Y' => Carbon::now()->subYear(),
                default => Carbon::now()->subYears(10),
            };
            $filteredHistory = $collectionItems->filter(fn($i) => Carbon::parse($i->created_at)->gte($threshold));
        }

        $groupedByDate = $filteredHistory->groupBy(fn($i) => Carbon::parse($i->created_at)->format('Y-m-d'));

        $accumInvested = 0.00;
        $accumMarket = 0.00;

        foreach ($groupedByDate as $date => $dayItems) {
            foreach ($dayItems as $item) {
                $print = $item->catalogPrint;
                $market = $this->resolveCardMarketPrice($print, (array)$item->extras, $item->acquisition_price, $presenterCache);
                $paid = (float) ($item->acquisition_price ?: 0.00);

                $accumInvested += ($paid * $item->quantity);
                $accumMarket += ($market * $item->quantity);
            }

            $timelineDates[] = Carbon::parse($date)->format('d/m/Y');
            $timelineInvested[] = round($accumInvested, 2);
            $timelineMarket[] = round($accumMarket, 2);
        }

        if (count($timelineDates) === 1) {
            array_unshift($timelineDates, Carbon::parse($collectionItems->first()?->created_at ?? now())->subDay()->format('d/m/Y'));
            array_unshift($timelineInvested, 0);
            array_unshift($timelineMarket, 0);
        }

        return view('livewire.lobby.dashboard', compact(
            'binders',
            'totalBindersCount',
            'totalCardsCount',
            'totalDecksCount',
            'totalMarketValue',
            'totalInvested',
            'portfolioNominalGain',
            'portfolioDiffPct',
            'portfolioTrend',
            'top5Cards',
            'timelineDates',
            'timelineInvested',
            'timelineMarket'
        ));
    }
}
