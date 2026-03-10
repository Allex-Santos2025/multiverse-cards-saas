<?php 

namespace App\Livewire\Store\Dashboard\Stock;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed; 
use Livewire\Attributes\Isolate;  
use Livewire\WithFileUploads;
use App\Models\CatalogProduct;
use App\Models\StockItem;

#[Isolate]
class ProductInventory extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $showModal = false;
    public $editingProductId;
    public $modalPhoto;
    public $existingPhotoUrl;
    public $modalComment;

    public $gameSlug;
    public $gameId;
    
    public $search = '';
    public $searchType = 'padrao'; 
    public $filterType = ''; 
    
    protected $queryString = [
        'search'     => ['as' => 'p_search', 'except' => ''],
        'searchType' => ['as' => 'p_searchType', 'except' => 'padrao'],
        'filterType' => ['as' => 'p_filterType', 'except' => 'sealed'],
    ];
    public function updatingSearch()
    {
        $this->resetPage();
    }

    #[On('set-product-filter')]
    public function setProductFilter($filter)
    {
        $this->filterType = is_array($filter) ? ($filter['filter'] ?? '') : $filter;
        $this->resetPage();
    }

    public function applyFilters()
    {
        $this->resetPage();
    }

    public function saveForm($data)
    {
        $storeId = auth('store_user')->user()->current_store_id ?? auth('store_user')->user()->store_id;

        if (!isset($data['items']) || empty($data['items'])) return;

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($data['items'] as $productId => $fields) {
                
                $priceRaw = $fields['price'] ?? 0;
                $price = (float) str_replace(',', '.', $priceRaw);
                $qty = (int) ($fields['qty'] ?? 0);

                // 🛡️ REGRA DE OURO: Preço zero não entra. Ignora o resto da tela!
                if ($price <= 0) {
                    continue;
                }

                $stockItem = StockItem::withoutGlobalScopes()
                    ->where('catalog_product_id', $productId)
                    ->where('store_id', $storeId)
                    ->first();

                if ($stockItem) {
                    $stockItem->quantity = $qty;
                    $stockItem->price = $price;
                    
                    // 🎯 Só salva se você realmente alterou o preço ou a quantidade
                    if ($stockItem->isDirty()) {
                        $stockItem->save();
                    }
                } else {
                    StockItem::create([
                        'store_id' => $storeId,
                        'catalog_product_id' => $productId,
                        'catalog_print_id' => null,
                        'quantity' => $qty,
                        'price' => $price,
                    ]);
                }
            }
            \Illuminate\Support\Facades\DB::commit();
            $this->dispatch('estoque-salvo');
            $this->dispatch('notify', type: 'success', message: 'Produtos salvos com sucesso!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            $this->dispatch('notify', type: 'error', message: 'Erro crítico: ' . $e->getMessage());
        }
    }

    public function saveDetails()
    {
        if (!$this->editingProductId) return;

        $storeId = auth('store_user')->user()->current_store_id ?? auth('store_user')->user()->store_id;

        $item = StockItem::firstOrCreate(
            ['store_id' => $storeId, 'catalog_product_id' => $this->editingProductId, 'catalog_print_id' => null],
            ['quantity' => 0, 'price' => 0]
        );

        $item->comments = $this->modalComment;

        if ($this->modalPhoto) {
            $path = $this->modalPhoto->store('stock-photos', 'public');
            $item->image_path = $path;
        }

        $item->save();
        $this->dispatch('notify', type: 'success', message: 'Detalhes e foto atualizados!');
        $this->showModal = false;
        $this->editingProductId = null;
    }

    public function mount()
    {
        $this->gameId = \App\Models\Game::where('url_slug', $this->gameSlug)->value('id');

        if (empty($this->filterType)) {
            $this->filterType = 'sealed';
        }
    }

    #[Computed]
    public function produtosQuery()
    {
        $storeId = auth('store_user')->user()->current_store_id ?? auth('store_user')->user()->store_id;

        $query = CatalogProduct::query()
            ->with(['stockItems' => function($q) use ($storeId) {
                // Remove o filtro fantasma aqui
                $q->withoutGlobalScopes()->where('store_id', $storeId);
            }])
            ->where('is_active', true)
            ->where(function($q) {
                $q->where('game_id', $this->gameId)
                ->orWhereNull('game_id');
            });

        if (!empty($this->search)) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $query->where('type', $this->filterType);

        if ($this->searchType === 'minhaLoja') {
            $query->whereHas('stockItems', function($q) use ($storeId) {
                // Remove o filtro fantasma aqui também e garante o zero
                $q->withoutGlobalScopes()
                ->where('store_id', $storeId)
                ->where('quantity', '>=', 0);
            });
        }

        return $query->orderBy('name')->paginate(50);
    }

    public function render()
    {
        $products = $this->produtosQuery;

        $listaFinal = []; 
        foreach ($products as $product) {
            $listaFinal[] = [
                'product' => $product,
                'stock' => $product->stockItems->first()
            ];
        }

        return view('livewire.store.dashboard.stock.product-inventory', [
            'items' => $listaFinal, 
            'pagination' => $products
        ]);
    }
}