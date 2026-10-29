<?php

namespace App\Livewire\Lobby;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Set;
use App\Models\Catalog\CatalogPrint;
use App\Models\PlayerCollectionItem;
use App\Models\PlayerBinderSlot;
use App\Enums\StockExtra;
use App\Services\GamePresenters\GamePresenterFactory;
use Illuminate\Support\Facades\Auth;

class PlayerSetPage extends Component
{
    use WithPagination;

    public $setId;
    public $language = 'pt';
    public $isFoil = false;
    public $viewType = 'album';
    public $filterStatus = 'all';
    public $search = '';
    public $perPage = 60;

    // Modal de Edição da Carta do Acervo
    public $modalEditCard = false;
    public $editItemId = null;
    public $editCardData = null;
    public $editLanguage = 'pt';
    public $editCondition = 'NM';
    public $editSelectedExtras = [];
    public $availableExtras = [];
    public $editQuantity = 1;
    public $editPrice = '';
    public $availableLanguages = [];

    public function mount(int $setId, string $language = 'pt', bool $isFoil = false)
    {
        $this->setId = $setId;
        $this->language = strtolower($language);
        $this->isFoil = $isFoil;

        $set = Set::find($setId);
        if ($set) {
            try {
                $presenter = GamePresenterFactory::make($set->game_id);
                $this->availableExtras = $presenter->getAvailableStockExtras();
            } catch (\Throwable $e) {
                $this->availableExtras = StockExtra::options();
            }
        }
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedFilterStatus(): void { $this->resetPage(); }
    public function updatedPerPage(): void { $this->resetPage(); }

    public function setViewType(string $type): void
    {
        if (in_array($type, ['album', 'list'])) {
            $this->viewType = $type;
        }
    }

    public function openEditCardModal(int $itemId): void
    {
        $playerId = Auth::guard('player')->id();
        $item = PlayerCollectionItem::where('id', $itemId)
            ->where('player_user_id', $playerId)
            ->with(['catalogPrint.concept', 'catalogPrint.set'])
            ->firstOrFail();

        $print = $item->catalogPrint;
        $this->editItemId = $item->id;
        $this->editCardData = [
            'name'   => $print->printed_name ?: ($print->concept->name ?? 'Carta'),
            'code'   => $print->set->code ?? '',
            'number' => $print->collector_number,
            'image'  => $print->image_path ?: '/images/placeholder-card.png',
        ];

        $this->availableLanguages = CatalogPrint::where('set_id', $print->set_id)
            ->where('collector_number', $print->collector_number)
            ->get()
            ->map(fn($p) => [
                'code'  => $p->language_code,
                'label' => strtoupper($p->language_code),
            ])->toArray();

        $this->editLanguage = strtolower($item->language);
        $this->editCondition = $item->condition;
        $this->editSelectedExtras = is_array($item->extras) ? $item->extras : [];
        $this->editQuantity = $item->quantity;
        $this->editPrice = $item->acquisition_price ? number_format($item->acquisition_price, 2, ',', '.') : '';

        $this->modalEditCard = true;
    }

    public function toggleEditExtra(string $extra): void
    {
        $extraLower = strtolower($extra);
        $current = array_map('strtolower', $this->editSelectedExtras);

        if (in_array($extraLower, $current)) {
            $this->editSelectedExtras = array_values(array_filter($this->editSelectedExtras, fn($e) => strtolower($e) !== $extraLower));
        } else {
            $this->editSelectedExtras[] = $extraLower;
        }
    }

    public function updateCard(): void
    {
        $playerId = Auth::guard('player')->id();
        $item = PlayerCollectionItem::where('id', $this->editItemId)
            ->where('player_user_id', $playerId)
            ->firstOrFail();

        $this->validate([
            'editQuantity'  => 'required|integer|min:1',
            'editLanguage'  => 'required|string',
            'editCondition' => 'required|string',
        ]);

        $cleanPrice = $this->editPrice ? (float) str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $this->editPrice) : null;

        $item->update([
            'language'          => strtolower($this->editLanguage),
            'condition'         => $this->editCondition,
            'extras'            => array_values($this->editSelectedExtras),
            'quantity'          => (int) $this->editQuantity,
            'acquisition_price' => $cleanPrice,
        ]);

        $this->modalEditCard = false;
        $this->dispatch('notify', type: 'success', message: 'Carta atualizada com sucesso!');
    }

    public function deleteCard(int $itemId): void
    {
        $playerId = Auth::guard('player')->id();
        $item = PlayerCollectionItem::where('id', $itemId)
            ->where('player_user_id', $playerId)
            ->first();

        if ($item) {
            // Remove de qualquer bolso de fichário em que estiver alocada
            PlayerBinderSlot::where('player_collection_item_id', $item->id)->delete();
            $item->delete();
            $this->dispatch('notify', type: 'success', message: 'Carta removida do acervo.');
        }
    }

    public function render()
    {
        $set = Set::with('game')->findOrFail($this->setId);
        $playerId = Auth::guard('player')->id();

        $userCards = PlayerCollectionItem::where('player_user_id', $playerId)
            ->whereHas('catalogPrint', fn($q) => $q->where('set_id', $this->setId))
            ->where('language', $this->language)
            ->get()
            ->groupBy('catalog_print_id');

        $ownedPrintIds = $userCards->keys()->toArray();

        $printsQuery = CatalogPrint::where('set_id', $this->setId)
            ->where(function($q) {
                $q->where('language_code', $this->language)
                  ->orWhere('language_code', 'en');
            })
            ->where('printed_name', 'NOT LIKE', 'A-%')
            ->with(['concept']);

        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $printsQuery->where(function($q) use ($term) {
                $q->where('printed_name', 'like', $term)
                  ->orWhere('collector_number', 'like', $term)
                  ->orWhereHas('concept', fn($c) => $c->where('name', 'like', $term));
            });
        }

        if ($this->filterStatus === 'owned') {
            $printsQuery->whereIn('id', $ownedPrintIds);
        } elseif ($this->filterStatus === 'missing') {
            $printsQuery->whereNotIn('id', $ownedPrintIds);
        }

        $printsQuery->orderByRaw("CAST(REGEXP_REPLACE(collector_number, '[^0-9]', '') AS UNSIGNED) ASC");

        $paginatedPrints = $printsQuery->paginate($this->perPage);

        $items = collect($paginatedPrints->items())->map(function ($print) use ($userCards) {
            $ownedCollection = $userCards->get($print->id, collect());
            $ownedCount = $ownedCollection->sum('quantity');

            return [
                'id'               => $print->id,
                'collector_number' => $print->collector_number,
                'name'             => $print->printed_name ?: ($print->concept->name ?? 'Carta'),
                'english_name'     => $print->concept->name ?? $print->printed_name,
                'rarity'           => $print->rarity,
                'image'            => $print->image_path ?: '/images/placeholder-card.png',
                'is_owned'         => $ownedCount > 0,
                'owned_count'      => $ownedCount,
                'owned_records'    => $ownedCollection,
            ];
        });

        return view('livewire.lobby.player-set-page', [
            'set'             => $set,
            'items'           => $items,
            'paginatedPrints' => $paginatedPrints,
        ]);
    }
}
