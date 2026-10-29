<?php

namespace App\Livewire\Lobby;

use App\Models\Catalog\CatalogPrint;
use App\Models\PlayerCollectionItem;
use App\Models\PlayerDeck;
use App\Models\PlayerDeckCard;
use App\Services\GamePresenters\GamePresenterFactory;
use App\Services\GamePresenters\GamePresenterInterface;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeckBuilder extends Component
{
    public int $deckId;
    public ?PlayerDeck $deck = null;

    // Busca e filtros
    public string $search = '';
    public string $sourceTab = 'collection'; // 'collection' ou 'catalog'
    public string $targetBoard = 'mainboard';

    protected $listeners = ['refreshDeck' => '$refresh'];

    public function mount(int $deckId): void
    {
        $this->deckId = $deckId;
        $this->loadDeck();
    }

    public function loadDeck(): void
    {
        $playerId = Auth::guard('player')->id();

        $this->deck = PlayerDeck::where('id', $this->deckId)
            ->where('player_user_id', $playerId)
            ->with(['cards.catalogPrint.set', 'cards.catalogPrint.concept', 'cards.collectionItem'])
            ->firstOrFail();

        $zones = $this->getPresenter()->getDeckZones($this->deck->format);
        if (!array_key_exists($this->targetBoard, $zones)) {
            $this->targetBoard = array_key_first($zones) ?? 'mainboard';
        }
    }

    protected function getPresenter(): GamePresenterInterface
    {
        return GamePresenterFactory::make($this->deck->game_id);
    }

    public function setSourceTab(string $tab): void
    {
        $this->sourceTab = in_array($tab, ['collection', 'catalog']) ? $tab : 'collection';
    }

    public function setTargetBoard(string $board): void
    {
        $this->targetBoard = $board;
    }

    public function addFromCollection(int $collectionItemId): void
    {
        $item = PlayerCollectionItem::where('id', $collectionItemId)
            ->where('player_user_id', Auth::guard('player')->id())
            ->first();

        if (!$item) return;

        $alreadyAllocated = PlayerDeckCard::where('player_collection_item_id', $item->id)
            ->where('is_virtual', false)
            ->sum('quantity');

        if ($alreadyAllocated >= $item->quantity) {
            $this->dispatch('notify', type: 'error', message: 'Todas as cópias físicas desta carta já estão em uso.');
            return;
        }

        $existing = PlayerDeckCard::where('player_deck_id', $this->deck->id)
            ->where('catalog_print_id', $item->catalog_print_id)
            ->where('board_type', $this->targetBoard)
            ->where('player_collection_item_id', $item->id)
            ->first();

        if ($existing) {
            $existing->increment('quantity');
        } else {
            PlayerDeckCard::create([
                'player_deck_id'            => $this->deck->id,
                'catalog_print_id'          => $item->catalog_print_id,
                'player_collection_item_id' => $item->id,
                'quantity'                  => 1,
                'board_type'                => $this->targetBoard,
                'is_virtual'                => false,
            ]);
        }

        $this->loadDeck();
    }

    public function addAsWishlist(int $catalogPrintId): void
    {
        $print = CatalogPrint::find($catalogPrintId);
        if (!$print) return;

        $existing = PlayerDeckCard::where('player_deck_id', $this->deck->id)
            ->where('catalog_print_id', $print->id)
            ->where('board_type', $this->targetBoard)
            ->where('is_virtual', true)
            ->first();

        if ($existing) {
            $existing->increment('quantity');
        } else {
            PlayerDeckCard::create([
                'player_deck_id'            => $this->deck->id,
                'catalog_print_id'          => $print->id,
                'player_collection_item_id' => null,
                'quantity'                  => 1,
                'board_type'                => $this->targetBoard,
                'is_virtual'                => true,
            ]);
        }

        $this->loadDeck();
    }

    public function incrementCard(int $deckCardId): void
    {
        $card = PlayerDeckCard::where('id', $deckCardId)
            ->where('player_deck_id', $this->deck->id)
            ->first();

        if (!$card) return;

        if (!$card->is_virtual && $card->player_collection_item_id) {
            $item = $card->collectionItem;
            $allocated = PlayerDeckCard::where('player_collection_item_id', $item->id)
                ->where('is_virtual', false)
                ->sum('quantity');

            if ($allocated >= $item->quantity) {
                $this->dispatch('notify', type: 'error', message: 'Sem mais cópias físicas disponíveis no acervo.');
                return;
            }
        }

        $card->increment('quantity');
        $this->loadDeck();
    }

    public function decrementCard(int $deckCardId): void
    {
        $card = PlayerDeckCard::where('id', $deckCardId)
            ->where('player_deck_id', $this->deck->id)
            ->first();

        if (!$card) return;

        if ($card->quantity > 1) {
            $card->decrement('quantity');
        } else {
            $card->delete();
        }

        $this->loadDeck();
    }

    public function setAsCover(int $catalogPrintId, ?string $imagePath = null): void
    {
        $this->deck->update([
            'cover_catalog_print_id' => $catalogPrintId,
            'cover_image_path'       => $imagePath,
        ]);

        $this->loadDeck();
    }

    public function render()
    {
        $playerId = Auth::guard('player')->id();
        $searchResults = collect();

        if (strlen($this->search) >= 2) {
            if ($this->sourceTab === 'collection') {
                $rawItems = PlayerCollectionItem::query()
                    ->where('player_user_id', $playerId)
                    ->whereHas('catalogPrint.set', fn($q) => $q->where('game_id', $this->deck->game_id))
                    ->where(function ($q) {
                        $q->whereHas('catalogPrint', fn($cq) => $cq->where('printed_name', 'like', "%{$this->search}%"))
                          ->orWhereHas('catalogPrint.concept', fn($cq) => $cq->where('name', 'like', "%{$this->search}%"));
                    })
                    ->with(['catalogPrint.set', 'catalogPrint.concept'])
                    ->take(24)
                    ->get();

                $searchResults = $rawItems->map(function ($item) {
                    $print = $item->catalogPrint;
                    $allocated = PlayerDeckCard::where('player_collection_item_id', $item->id)
                        ->where('is_virtual', false)
                        ->sum('quantity');

                    return [
                        'type'             => 'collection',
                        'item_id'          => $item->id,
                        'print_id'         => $print->id,
                        'name'             => $print->printed_name ?: ($print->concept->name ?? 'Carta'),
                        'set_name'         => $print->set->name ?? '',
                        'set_code'         => $print->set->code ?? '',
                        'collector_number' => $print->collector_number ?? '',
                        'image'            => $print->image_path ?: '/images/placeholder-card.png',
                        'quantity'         => $item->quantity,
                        'available'        => max(0, $item->quantity - $allocated),
                        'condition'        => $item->condition ?? 'NM',
                    ];
                });
            } else {
                $rawPrints = CatalogPrint::query()
                    ->whereHas('set', fn($q) => $q->where('game_id', $this->deck->game_id))
                    ->where(function ($q) {
                        $q->where('printed_name', 'like', "%{$this->search}%")
                          ->orWhereHas('concept', fn($cq) => $cq->where('name', 'like', "%{$this->search}%"));
                    })
                    ->with(['set', 'concept'])
                    ->take(24)
                    ->get();

                $searchResults = $rawPrints->map(function ($print) {
                    return [
                        'type'             => 'catalog',
                        'print_id'         => $print->id,
                        'name'             => $print->printed_name ?: ($print->concept->name ?? 'Carta'),
                        'set_name'         => $print->set->name ?? '',
                        'set_code'         => $print->set->code ?? '',
                        'collector_number' => $print->collector_number ?? '',
                        'image'            => $print->image_path ?: '/images/placeholder-card.png',
                    ];
                });
            }
        }

        $presenter = $this->getPresenter();
        $zones = $presenter->getDeckZones($this->deck->format);
        $totalCards = $this->deck->cards->sum('quantity');
        $physicalCount = $this->deck->cards->where('is_virtual', false)->sum('quantity');
        $wishlistCount = $this->deck->cards->where('is_virtual', true)->sum('quantity');

        return view('livewire.lobby.deck-builder', [
            'searchResults' => $searchResults,
            'zones'         => $zones,
            'totalCards'    => $totalCards,
            'physicalCount' => $physicalCount,
            'wishlistCount' => $wishlistCount,
        ]);
    }
}
