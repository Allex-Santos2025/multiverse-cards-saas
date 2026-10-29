<?php

namespace App\Livewire\Lobby;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\PlayerBinder;
use App\Models\PlayerCollectionItem;
use App\Models\PlayerBinderSlot;
use App\Models\Catalog\CatalogPrint;
use App\Services\PlayerCollectionService;
use App\Services\GamePresenters\GamePresenterFactory;
use App\Enums\StockExtra;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Colecao extends Component
{
    use WithFileUploads;

    public $metaColecao = '1x';
    public $selectedGameId = null;
    public $activeBinderSlug = null;

    // --- CONTROLE DA SET PAGE ---
    public $activeSetId = null;
    public $activeSetLang = 'pt';
    public $activeSetIsFoil = false;

    // --- MODAL: NOVO FICHÁRIO ---
    public $modalCreateBinder = false;
    public $newBinderName = '';
    public $newBinderDesc = '';
    public $newBinderLayout = '3x3';
    public $newBinderCover = 'cover-black';
    public $newBinderStrap = 'orange';
    public $newBinderLockedSheets = false;
    public $newBinderSelectedArt = null;
    public $newBinderUploadCover = null;
    public $selectedGameTab = 'magic';

    // --- MODAL: GERENCIAR FICHÁRIO ---
    public $modalEditBinder = false;
    public $editBinderId = null;
    public $editBinderName = '';
    public $editBinderDesc = '';
    public $editBinderCover = 'cover-black';
    public $editBinderStrap = 'orange';
    public $editBinderSelectedArt = null;
    public $editBinderUploadCover = null;
    public $editGameTab = 'magic';
    public $editBinderLayout = '3x3';
    public $editBinderIsLocked = false;

    // --- MODAL: CADASTRAR / GERENCIAR CARTA ---
    public $modalAddCard = false;
    public $searchQuery = '';
    public $selectedPrintId = null;
    public $selectedPrintData = null;
    public $cardLanguage = 'pt';
    public $cardCondition = 'NM';
    public $selectedExtras = [];
    public $availableExtras = [];
    public $cardQuantity = 1;
    public $cardPrice = '';

    // Controle de Edição de Lote no Modal
    public $isEditingItem = false;
    public $editingItemId = null;

    public function getIconicArtsProperty(): array
    {
        return [
            'magic' => [
                ['name' => 'Liliana do Véu', 'url' => '/images/binders/covers/magic/liliana.jpg'],
                ['name' => 'Jace Beleren', 'url' => '/images/binders/covers/magic/jace.jpg'],
                ['name' => 'Black Lotus', 'url' => '/images/binders/covers/magic/black-lotus.jpg'],
                ['name' => 'Nicol Bolas', 'url' => '/images/binders/covers/magic/nicol-bolas.jpg'],
                ['name' => 'Serra Angel', 'url' => '/images/binders/covers/magic/serra-angel.jpg'],
            ],
            'pokemon' => [
                ['name' => 'Charizard', 'url' => '/images/binders/covers/pokemon/charizard.png'],
                ['name' => 'Pikachu', 'url' => '/images/binders/covers/pokemon/pikachu.png'],
                ['name' => 'Mewtwo', 'url' => '/images/binders/covers/pokemon/mewtwo.png'],
                ['name' => 'Rayquaza', 'url' => '/images/binders/covers/pokemon/rayquaza.png'],
                ['name' => 'Umbreon VMAX', 'url' => '/images/binders/covers/pokemon/umbreon.png'],
            ],
            'yugioh' => [
                ['name' => 'Blue-Eyes White Dragon', 'url' => '/images/binders/covers/yugioh/blue-eyes.jpg'],
                ['name' => 'Dark Magician', 'url' => '/images/binders/covers/yugioh/dark-magician.jpg'],
                ['name' => 'Exodia', 'url' => '/images/binders/covers/yugioh/exodia.jpg'],
                ['name' => 'Slifer the Sky Dragon', 'url' => '/images/binders/covers/yugioh/slifer.jpg'],
                ['name' => 'Red-Eyes Black Dragon', 'url' => '/images/binders/covers/yugioh/red-eyes.jpg'],
            ],
            'onepiece' => [
                ['name' => 'Monkey D. Luffy', 'url' => '/images/binders/covers/onepiece/luffy.webp'],
                ['name' => 'Roronoa Zoro', 'url' => '/images/binders/covers/onepiece/zoro.webp'],
                ['name' => 'Shanks', 'url' => '/images/binders/covers/onepiece/shanks.webp'],
                ['name' => 'Portgas D. Ace', 'url' => '/images/binders/covers/onepiece/ace.webp'],
                ['name' => 'Nami', 'url' => '/images/binders/covers/onepiece/nami.webp'],
            ],
            'battlescenes' => [
                ['name' => 'Homem de Ferro', 'url' => '/images/binders/covers/battlescenes/iron-man.png'],
                ['name' => 'Wolverine', 'url' => '/images/binders/covers/battlescenes/wolverine.png'],
                ['name' => 'Capitão América', 'url' => '/images/binders/covers/battlescenes/capitao.png'],
                ['name' => 'Thanos', 'url' => '/images/binders/covers/battlescenes/thanos.png'],
                ['name' => 'Carnificina', 'url' => '/images/binders/covers/battlescenes/carnificina.png'],
            ],
        ];
    }

    public function setMeta(string $meta): void
    {
        $this->metaColecao = in_array($meta, ['1x', '4x']) ? $meta : '1x';
    }

    public function filterByGame(?int $gameId): void
    {
        $this->selectedGameId = $gameId ?: null;
    }

    public function openBinder(string $slug): void
    {
        $this->activeBinderSlug = $slug;
    }

    public function closeBinder(): void
    {
        $this->activeBinderSlug = null;
    }

    public function openSetPage(int $setId, string $language, bool $isFoil): void
    {
        $this->activeSetId = $setId;
        $this->activeSetLang = $language;
        $this->activeSetIsFoil = $isFoil;
    }

    public function closeSetPage(): void
    {
        $this->activeSetId = null;
    }

    public function openCreateBinderModal(): void
    {
        $playerId = Auth::guard('player')->id();
        $count = PlayerBinder::where('player_user_id', $playerId)->count() + 1;

        $this->newBinderName = "Fichário #{$count}";
        $this->newBinderDesc = '';
        $this->newBinderLayout = '3x3';
        $this->newBinderCover = 'cover-black';
        $this->newBinderStrap = 'orange';
        $this->newBinderLockedSheets = false;
        $this->newBinderSelectedArt = null;
        $this->newBinderUploadCover = null;
        $this->selectedGameTab = 'magic';
        $this->modalCreateBinder = true;
    }

    public function selectCoverArt(string $url): void
    {
        $this->newBinderSelectedArt = $url;
        $this->newBinderUploadCover = null;
    }

    public function selectEditCoverArt(string $url): void
    {
        $this->editBinderSelectedArt = $url;
        $this->editBinderUploadCover = null;
    }

    public function saveNewBinder(): void
    {
        $playerId = Auth::guard('player')->id();
        if (!$playerId) return;

        $this->validate([
            'newBinderName'         => 'required|min:2|max:60',
            'newBinderLayout'       => 'required|in:2x2,3x3,4x4,bulk',
            'newBinderCover'        => 'required|string',
            'newBinderStrap'        => 'required|string',
            'newBinderLockedSheets' => 'boolean',
            'newBinderUploadCover'  => 'nullable|image|max:3072',
        ]);

        $coverImagePath = null;

        if ($this->newBinderUploadCover) {
            $destDir = public_path('images/binders/uploads');
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }
            $filename = 'binder-' . uniqid() . '.' . $this->newBinderUploadCover->getClientOriginalExtension();
            file_put_contents($destDir . '/' . $filename, file_get_contents($this->newBinderUploadCover->getRealPath()));
            $coverImagePath = '/images/binders/uploads/' . $filename;
        } elseif ($this->newBinderSelectedArt) {
            $coverImagePath = $this->newBinderSelectedArt;
        }

        PlayerBinder::create([
            'player_user_id'       => $playerId,
            'name'                 => trim($this->newBinderName),
            'slug'                 => Str::slug($this->newBinderName) . '-' . uniqid(),
            'description'          => trim($this->newBinderDesc),
            'pocket_layout'        => $this->newBinderLayout,
            'factory_sheets_count' => 20,
            'current_sheets_count' => 20,
            'is_locked_sheets'     => (bool) $this->newBinderLockedSheets,
            'cover_material'       => $this->newBinderCover,
            'cover_image_path'     => $coverImagePath,
            'strap_color'          => $this->newBinderStrap,
            'is_public'            => false,
        ]);

        $this->modalCreateBinder = false;
    }

    public function openEditBinder(int $id): void
    {
        $binder = PlayerBinder::where('id', $id)
            ->where('player_user_id', Auth::guard('player')->id())
            ->first();

        if (!$binder) return;

        $this->editBinderId = $binder->id;
        $this->editBinderName = $binder->name;
        $this->editBinderDesc = $binder->description ?? '';
        $this->editBinderCover = $binder->cover_material ?? 'cover-black';
        $this->editBinderStrap = $binder->strap_color ?? 'orange';
        $this->editBinderSelectedArt = $binder->cover_image_path;
        $this->editBinderUploadCover = null;
        $this->editGameTab = 'magic';
        $this->editBinderLayout = $binder->pocket_layout;
        $this->editBinderIsLocked = (bool) $binder->is_locked_sheets;
        $this->modalEditBinder = true;
    }

    public function updateBinder(): void
    {
        $this->validate([
            'editBinderName'        => 'required|min:2|max:60',
            'editBinderUploadCover' => 'nullable|image|max:3072',
        ]);

        $binder = PlayerBinder::where('id', $this->editBinderId)
            ->where('player_user_id', Auth::guard('player')->id())
            ->first();

        if ($binder) {
            $coverImagePath = $this->editBinderSelectedArt ?: $binder->cover_image_path;

            if ($this->editBinderUploadCover) {
                $destDir = public_path('images/binders/uploads');
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0777, true);
                }
                $filename = 'binder-' . uniqid() . '.' . $this->editBinderUploadCover->getClientOriginalExtension();
                file_put_contents($destDir . '/' . $filename, file_get_contents($this->editBinderUploadCover->getRealPath()));
                $coverImagePath = '/images/binders/uploads/' . $filename;
            }

            $binder->update([
                'name'             => trim($this->editBinderName),
                'description'      => trim($this->editBinderDesc),
                'cover_material'   => $this->editBinderCover,
                'cover_image_path' => $coverImagePath,
                'strap_color'      => $this->editBinderStrap,
            ]);
        }

        $this->modalEditBinder = false;
    }

    public function deleteBinder(): void
    {
        $binder = PlayerBinder::where('id', $this->editBinderId)
            ->where('player_user_id', Auth::guard('player')->id())
            ->first();

        if ($binder) {
            $binder->slots()->delete();
            $binder->delete();
        }

        $this->modalEditBinder = false;
    }

    public function openAddCardModal(): void
    {
        $this->searchQuery = '';
        $this->selectedPrintId = null;
        $this->selectedPrintData = null;
        $this->availableExtras = [];
        $this->selectedExtras = [];
        $this->cardLanguage = 'pt';
        $this->cardCondition = 'NM';
        $this->cardQuantity = 1;
        $this->cardPrice = '';
        $this->isEditingItem = false;
        $this->editingItemId = null;
        $this->modalAddCard = true;
    }

    /**
     * Ao selecionar a estampa física exata, herdamos o idioma exato dela
     */
    public function selectPrint(int $id): void
    {
        $print = CatalogPrint::with(['concept', 'set.game'])->find($id);
        if ($print) {
            $this->selectedPrintId = $print->id;
            $this->selectedPrintData = [
                'name'          => $print->printed_name ?: ($print->concept->name ?? 'Carta'),
                'canonical'     => $print->concept->name ?? $print->printed_name,
                'language_code' => $print->language_code,
                'set_name'      => $print->set->name ?? '',
                'code'          => $print->set->code ?? '',
                'number'        => $print->collector_number,
                'image'         => $print->image_path ?: '/images/placeholder-card.png',
            ];

            try {
                $presenter = GamePresenterFactory::make($print->set->game_id);
                $this->availableExtras = $presenter->getAvailableStockExtras();
            } catch (\Throwable $e) {
                $this->availableExtras = StockExtra::options();
            }

            // O idioma padrão é o idioma real desta estampa selecionada
            $this->cardLanguage = strtolower($print->language_code ?: 'en');
            $this->selectedExtras = [];
            $this->cardQuantity = 1;
            $this->cardPrice = '';
            $this->isEditingItem = false;
            $this->editingItemId = null;
            $this->searchQuery = '';
        }
    }

    public function resetCardPicker(): void
    {
        $this->selectedPrintId = null;
        $this->selectedPrintData = null;
        $this->searchQuery = '';
        $this->isEditingItem = false;
        $this->editingItemId = null;
        $this->selectedExtras = [];
        $this->cardQuantity = 1;
        $this->cardPrice = '';
    }

    public function editCollectionItem(int $itemId): void
    {
        $playerId = Auth::guard('player')->id();
        $item = PlayerCollectionItem::where('id', $itemId)
            ->where('player_user_id', $playerId)
            ->first();

        if ($item) {
            $this->isEditingItem = true;
            $this->editingItemId = $item->id;
            $this->cardLanguage = strtolower($item->language);
            $this->cardCondition = $item->condition;
            $this->selectedExtras = is_array($item->extras) ? $item->extras : [];
            $this->cardQuantity = $item->quantity;
            $this->cardPrice = $item->acquisition_price ? number_format($item->acquisition_price, 2, ',', '.') : '';
        }
    }

    public function cancelItemEdit(): void
    {
        $this->isEditingItem = false;
        $this->editingItemId = null;
        $this->cardQuantity = 1;
        $this->cardPrice = '';
        $this->selectedExtras = [];
    }

    public function deleteCollectionItem(int $itemId): void
    {
        $playerId = Auth::guard('player')->id();
        $item = PlayerCollectionItem::where('id', $itemId)
            ->where('player_user_id', $playerId)
            ->first();

        if ($item) {
            PlayerBinderSlot::where('player_collection_item_id', $item->id)->delete();
            $item->delete();

            if ($this->editingItemId === $itemId) {
                $this->cancelItemEdit();
            }

            $this->dispatch('notify', type: 'success', message: 'Cópia removida do acervo.');
        }
    }

    public function saveCardToCollection(): void
    {
        $playerId = Auth::guard('player')->id();
        if (!$playerId || !$this->selectedPrintId) return;

        $this->validate([
            'cardQuantity'  => 'required|integer|min:1',
            'cardLanguage'  => 'required|string',
            'cardCondition' => 'required|string',
        ]);

        $cleanPrice = $this->cardPrice ? (float) str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $this->cardPrice) : null;

        $data = [
            'language'          => strtolower($this->cardLanguage),
            'condition'         => $this->cardCondition,
            'extras'            => array_values($this->selectedExtras),
            'quantity'          => (int) $this->cardQuantity,
            'acquisition_price' => $cleanPrice,
        ];

        if ($this->isEditingItem && $this->editingItemId) {
            PlayerCollectionItem::where('id', $this->editingItemId)
                ->where('player_user_id', $playerId)
                ->update($data);
            $this->dispatch('notify', type: 'success', message: 'Cópia atualizada!');
            $this->cancelItemEdit();
        } else {
            PlayerCollectionItem::create(array_merge($data, [
                'player_user_id'   => $playerId,
                'catalog_print_id' => $this->selectedPrintId,
            ]));
            $this->dispatch('notify', type: 'success', message: 'Carta cadastrada no acervo!');
            $this->cardQuantity = 1;
            $this->cardPrice = '';
            $this->selectedExtras = [];
        }
    }

    public function render(PlayerCollectionService $collectionService)
    {
        $playerId = Auth::guard('player')->id();

        $binders = $playerId ? $collectionService->getFeaturedBinders($playerId) : collect();
        $totalBindersCount = $playerId ? PlayerBinder::where('player_user_id', $playerId)->count() : 0;

        $availableGames = $playerId ? $collectionService->getAvailableGamesForPlayer($playerId) : collect();
        $completedSets = $playerId ? $collectionService->getCompletedSetsSummary($playerId, $this->selectedGameId) : collect();

        // BUSCA PURA POR ESTAMPA (PRINT): CADA PRINT É UM CARD INDIVIDUAL
        $searchResults = collect();
        if ($this->modalAddCard && strlen(trim($this->searchQuery)) >= 2) {
            $terms = array_filter(preg_split('/\s+/', trim($this->searchQuery)));

            $query = CatalogPrint::with(['concept', 'set']);

            // Cada termo digitado deve bater em printed_name, concept.name, set.code ou set.name
            foreach ($terms as $t) {
                $query->where(function ($q) use ($t) {
                    $q->where('printed_name', 'like', "%{$t}%")
                      ->orWhereHas('concept', fn($c) => $c->where('name', 'like', "%{$t}%"))
                      ->orWhereHas('set', fn($s) => $s->where('code', 'like', "%{$t}%")->orWhere('name', 'like', "%{$t}%"));
                });
            }

            // Traz até 60 estampas reais sem nenhum agrupamento ou exclusão
            $searchResults = $query->orderBy('set_id', 'desc')
                ->orderBy('collector_number', 'asc')
                ->take(60)
                ->get();
        }

        $existingPrintItems = collect();
        if ($this->modalAddCard && $this->selectedPrintId && $playerId) {
            $existingPrintItems = PlayerCollectionItem::where('player_user_id', $playerId)
                ->where('catalog_print_id', $this->selectedPrintId)
                ->latest()
                ->get();
        }

        return view('livewire.lobby.colecao', compact(
            'binders',
            'totalBindersCount',
            'availableGames',
            'completedSets',
            'searchResults',
            'existingPrintItems'
        ));
    }
}
