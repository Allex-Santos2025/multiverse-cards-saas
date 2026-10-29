<?php

namespace App\Livewire\Lobby;

use Livewire\Component;
use App\Models\PlayerBinder;
use App\Models\PlayerBinderSlot;
use App\Models\PlayerCollectionItem;
use App\Models\PlayerUser;
use Illuminate\Support\Facades\Auth;

class FicharioViewer extends Component
{
    public $slug;
    public $nickname = null;
    public $currentLayout = '3x3';
    public $viewMode = 'book';
    public $currentPageIndex = 1;

    // Controle de Compartilhamento e Capa Fechada
    public bool $isShared = false;
    public bool $isCoverOpen = false; // Inicia sempre com a capa fechada

    // Slot ativo selecionado (ex: "1_0" = página 1, posição 0)
    public $activeSlotKey = null;
    public $selectedPage = null;
    public $selectedSlotPos = null;

    // Modal de Carta Alocada
    public $modalSlotOpen = false;
    public $activeSlotCard = null;

    // Modal de Seleção do Acervo
    public $modalPickerOpen = false;
    public $pickerSearch = '';

    public function mount($slug = null, $nickname = null, bool $isShared = false)
    {
        if (request()->route('nickname') && request()->route('slug')) {
            $this->nickname = request()->route('nickname');
            $this->slug = request()->route('slug');
            $this->isShared = true;
        } else {
            $this->slug = $slug;
            $this->nickname = $nickname;
            $this->isShared = $isShared || !empty($nickname);
        }

        $this->isCoverOpen = false;

        $binder = $this->getBinderProperty();

        if ($binder && in_array($binder->pocket_layout, ['2x2', '3x3', '4x4'])) {
            $this->currentLayout = $binder->pocket_layout;
        }
    }

    public function openCover(): void
    {
        $this->isCoverOpen = true;
    }

    public function closeCover(): void
    {
        $this->isCoverOpen = false;
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['book', 'zoom'])) {
            $this->viewMode = $mode;
        }
    }

    public function nextPage(int $totalPages): void
    {
        if ($this->viewMode === 'book') {
            if ($this->currentPageIndex === 1) {
                $this->currentPageIndex = 2;
            } elseif ($this->currentPageIndex + 2 <= $totalPages + 1) {
                $this->currentPageIndex += 2;
            }
        } else {
            if ($this->currentPageIndex < $totalPages) {
                $this->currentPageIndex++;
            }
        }
    }

    public function prevPage(): void
    {
        if ($this->viewMode === 'book') {
            if ($this->currentPageIndex === 2) {
                $this->currentPageIndex = 1;
            } elseif ($this->currentPageIndex > 2) {
                $this->currentPageIndex -= 2;
            }
        } else {
            if ($this->currentPageIndex > 1) {
                $this->currentPageIndex--;
            }
        }
    }

    public function handleSlotClick(string $slotKey): void
    {
        if ($this->isShared) {
            return;
        }

        $parts = explode('_', $slotKey);
        $this->selectedPage = (int) $parts[0];
        $this->selectedSlotPos = (int) $parts[1];
        $this->activeSlotKey = $slotKey;

        $slot = PlayerBinderSlot::where('binder_id', $this->getBinderProperty()->id)
            ->where('page_number', $this->selectedPage)
            ->where('slot_position', $this->selectedSlotPos)
            ->with(['collectionItem.catalogPrint.concept'])
            ->first();

        if ($slot && $slot->collectionItem) {
            $print = $slot->collectionItem->catalogPrint;
            $set = $print?->set;
            $concept = $print?->concept;
            $cardName = $print?->printed_name ?: ($concept?->name ?? ($print?->name ?? 'Carta'));
            $item = $slot->collectionItem;

            $this->activeSlotCard = [
                'slot_id'   => $slot->id,
                'item_id'   => $slot->player_collection_item_id,
                'name'      => $cardName,
                'image'     => $this->resolveCardImage($print),
                'copies'    => $slot->quantity_in_slot,
                'code'      => $set?->code ?? 'CARD',
                'number'    => $print?->collector_number ?? '---',
                'set'       => $set?->name ?? 'Coleção',
                'language'  => $item?->language ?? 'PT',
                'condition' => $item?->condition ?? 'NM',
                'price'     => ($item?->acquisition_price ?? $item?->price ?? 0) > 0 ? 'R$ ' . number_format(($item?->acquisition_price ?? $item?->price), 2, ',', '.') : 'R$ 0,00',
                'max_avail' => $item?->quantity ?? 4,
            ];
            $this->modalSlotOpen = true;
        } else {
            $this->pickerSearch = '';
            $this->modalPickerOpen = true;
        }
    }

    public function getBinderProperty(): PlayerBinder
    {
        $query = PlayerBinder::where('slug', $this->slug);

        if ($this->nickname) {
            $owner = PlayerUser::where('nickname', $this->nickname)
                ->orWhere('name', $this->nickname)
                ->first();

            if ($owner) {
                $query->where('player_user_id', $owner->id);
            }
        } elseif (!$this->isShared) {
            $query->where('player_user_id', Auth::guard('player')->id());
        }

        return $query->with(['playerUser' => function ($q) {
            $q->select('id', 'name', 'nickname', 'avatar');
        }])->firstOrFail();
    }

    private function resolveCardImage($catalogPrint): string
    {
        if (!$catalogPrint) {
            return '/images/placeholder-card.png';
        }

        $raw = $catalogPrint->image_url ?: $catalogPrint->image_path;

        if (empty($raw)) {
            return '/images/placeholder-card.png';
        }

        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }

        return '/' . ltrim($raw, '/');
    }

    public function render()
    {
        $binder = $this->getBinderProperty();

        $slots = PlayerBinderSlot::where('binder_id', $binder->id)
            ->with(['collectionItem.catalogPrint.concept'])
            ->get();

        $slotsMap = [];
        $allocatedMap = [];

        foreach ($slots as $slot) {
            $key = "{$slot->page_number}_{$slot->slot_position}";
            $item = $slot->collectionItem;

            if ($item) {
                $print = $item->catalogPrint;
                $slotsMap[$key] = [
                    'slot_id' => $slot->id,
                    'name'    => $print?->name ?? 'Carta',
                    'image'   => $this->resolveCardImage($print),
                    'copies'  => $slot->quantity_in_slot,
                    'item_id' => $item->id,
                ];

                $allocatedMap[$item->id] = ($allocatedMap[$item->id] ?? 0) + $slot->quantity_in_slot;
            }
        }

        $availableCollectionItems = collect();
        if (!$this->isShared) {
            $query = PlayerCollectionItem::where('player_user_id', Auth::guard('player')->id())
                ->with(['catalogPrint.concept']);

            if (!empty($this->pickerSearch)) {
                $term = '%' . $this->pickerSearch . '%';
                $query->whereHas('catalogPrint', function ($q) use ($term) {
                    $q->where(function ($sub) use ($term) {
                    $sub->where('printed_name', 'like', $term)
                        ->orWhere('collector_number', 'like', $term)
                        ->orWhereHas('concept', function ($cq) use ($term) {
                            $cq->where('name', 'like', $term);
                        });
                });
                });
            }

            $availableCollectionItems = $query->latest()
                ->get()
                ->filter(function ($item) use ($allocatedMap) {
                    $used = $allocatedMap[$item->id] ?? 0;
                    return ($item->quantity - $used) > 0;
                })
                ->map(function ($item) use ($allocatedMap) {
                    $used = $allocatedMap[$item->id] ?? 0;
                    $item->free_quantity = $item->quantity - $used;
                    return $item;
                })
                ->take(24)
                ->values();
        }

        $totalPages = $binder->current_sheets_count * 2;
        $pocketsPerPage = match ($this->currentLayout) {
            '2x2' => 4,
            '4x4' => 16,
            default => 9,
        };

        $owner = $binder->playerUser;

        $view = view('livewire.lobby.fichario-viewer', compact(
            'binder',
            'owner',
            'slotsMap',
            'totalPages',
            'pocketsPerPage',
            'availableCollectionItems'
        ));

        if ($this->isShared) {
            return $view->layout('layouts.shared-binder', [
                'title' => "Fichário: {$binder->name} | Versus TCG",
                'owner' => $owner,
            ]);
        }

        return $view;
    }

    public function closeModals(): void
    {
        $this->modalPickerOpen = false;
        $this->modalSlotOpen = false;
        $this->activeSlotCard = null;
        $this->activeSlotKey = null;
        $this->pickerSearch = "";
    }

    public function assignCardToSlot(int $itemId): void
    {
        if ($this->isShared || !$this->selectedPage || $this->selectedSlotPos === null) {
            return;
        }

        $binder = $this->getBinderProperty();

        \App\Models\PlayerBinderSlot::updateOrCreate(
            [
                "binder_id"     => $binder->id,
                "page_number"   => $this->selectedPage,
                "slot_position" => $this->selectedSlotPos,
            ],
            [
                "player_collection_item_id" => $itemId,
                "quantity_in_slot"          => 1,
            ]
        );

        $this->closeModals();
    }

    public function adjustCopies(int $delta): void
    {
        if ($this->isShared || empty($this->activeSlotCard["slot_id"])) {
            return;
        }

        $slot = \App\Models\PlayerBinderSlot::find($this->activeSlotCard["slot_id"]);
        if (!$slot) {
            return;
        }

        $newCopies = $slot->quantity_in_slot + $delta;
        $maxAvail = $this->activeSlotCard["max_avail"] ?? 4;
        $maxAllowed = min(4, $maxAvail);

        if ($newCopies >= 1 && $newCopies <= $maxAllowed) {
            $slot->update(["quantity_in_slot" => $newCopies]);
            $this->activeSlotCard["copies"] = $newCopies;
        }
    }

    public function removeCardFromSlot(): void
    {
        if ($this->isShared || empty($this->activeSlotCard["slot_id"])) {
            return;
        }

        \App\Models\PlayerBinderSlot::where("id", $this->activeSlotCard["slot_id"])->delete();
        $this->closeModals();
    }

    public function addNewSheet(): void
    {
        if ($this->isShared) {
            return;
        }

        $binder = $this->getBinderProperty();
        if ($binder->is_locked_sheets) {
            return;
        }

        $binder->increment("current_sheets_count");
    }

}
