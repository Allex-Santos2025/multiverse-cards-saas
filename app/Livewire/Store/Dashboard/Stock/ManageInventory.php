<?php

namespace App\Livewire\Store\Dashboard\Stock;

use Livewire\Component;
use Livewire\WithPagination; 
use Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Catalog\CatalogPrint;
use App\Models\StockItem;


class ManageInventory extends Component
{
    use WithPagination; 
    use WithFileUploads;

    protected $paginationTheme = 'tailwind'; 


    // --- VARIÁVEIS DE CONTROLE DE TELA (O QUE FALTOU) ---
    public $viewMode = 'list'; // Define qual tela aparece: 'list', 'import', 'export'    
    public $importLog = [];    // Para mostrar o resultado depois

    // ... Restante do código IGUAL ...
    public $search = '';
    public $gameSlug;
    public $slug;
    public $userStoreId;
    public $searchType = 'padrao';

    public $filterSet = '';
    public $filterColor = '';
    public $filterLanguage = '';
    public $filterRarity = '';
    public $filterCondition = '';

    public $modalPhoto;
    public $existingPhotoUrl;
    public $modalComment;
    public $editingPrintId;
    
    public $sortOption = 'number_asc';

    // Propriedades para o Módulo de Importação
    public $importText = '';
    public $importErrors = [];
    public $limitToFour = 1; 
    public $selectedExtras = []; 

    // Regex Universal: Aceita códigos de edição de 2 a 5 caracteres
    protected $importPattern = '/^(?<qtd>\d+)\s+(?<name>.+?)\s+\[(?<set>[A-Z0-9]{2,5})\]\s+(?<cond>M|NM|SP|MP|HP|D)\s+(?<lang>[A-Z]{2,3})(?:\s+\((?<extras>[^\)]+)\))?(?:\s+(?<price>[\d\.,]+))?\s*$/iu';

    protected $queryString = [
        'search'          => ['except' => ''],
        'filterSet'       => ['as' => 'set', 'except' => ''],
        'filterColor'     => ['as' => 'color', 'except' => ''],
        'filterLanguage'  => ['as' => 'lang', 'except' => ''],
        'sortOption'      => ['as' => 'sort', 'except' => 'number_asc'],
        
    ];

    public function mount($slug, $game_slug) 
    {
        $this->slug = $slug;
        $this->gameSlug = $game_slug;
        
        // Agora sim, pegando o ID correto da loja que o lojista está operando!
        $this->userStoreId = auth('store_user')->user()->current_store_id;
    }

    public function applyFilters() 
    { 
        $this->resetPage(); 
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    // --- AGORA VAI FUNCIONAR POIS O MODEL FOI IMPORTADO ---
    public function saveForm($formData)
    {
        // Verifica se a tela mandou os dados
        if (!isset($formData['items']) || empty($formData['items'])) return;

        DB::beginTransaction();
        try {
            foreach ($formData['items'] as $uniqueId => $data) {
                
                $isExistingStock = str_starts_with($uniqueId, 's');
                $realId = substr($uniqueId, 1);

                $priceRaw = $data['price'] ?? 0;
                $price = (float) str_replace(',', '.', $priceRaw); 
                $qty = (int) ($data['qty'] ?? 0);
                $cond = $data['condition'] ?? 'NM';
                $language = $data['language'] ?? 'en'; 
                $extras = $data['extras'] ?? [];

                // 🛡️ REGRA DE OURO: Preço zero não entra no estoque e não altera estoque existente.
                // Isso ignora instantaneamente as 49 cartas da tela que você não mexeu.
                if ($price <= 0) {
                    continue; 
                }

                if ($isExistingStock) {
                    // SE JÁ EXISTE: Puxamos do banco primeiro
                    $stockItem = \App\Models\StockItem::where('id', $realId)
                                    ->where('store_id', $this->userStoreId)
                                    ->first();

                    if ($stockItem) {
                        $stockItem->quantity = $qty;
                        $stockItem->price = $price;
                        $stockItem->condition = $cond;
                        $stockItem->extras = $extras;
                        $stockItem->language = $language;

                        // 🎯 MÁGICA DO LARAVEL: isDirty() verifica se alguma das variáveis acima é diferente do banco.
                        // Se você não mudou nada na tela, ele NÃO faz query no banco. Zero lentidão.
                        if ($stockItem->isDirty()) {
                            $stockItem->save();
                        }
                    }
                } else {
                    // SE É UMA CARTA NOVA:
                    // Como passou pelo "Escudo do Preço > 0", sabemos que você digitou um preço intencionalmente.
                    // Pode salvar tranquilamente, mesmo que o estoque seja zero (ex: você criando pré-venda).
                    \App\Models\StockItem::updateOrCreate(
                        [
                            'store_id'           => $this->userStoreId,
                            'catalog_print_id'   => $realId,
                            'catalog_product_id' => null,
                        ],
                        [
                            'quantity'  => $qty,
                            'price'     => $price,
                            'condition' => $cond,
                            'extras'    => $extras,
                            'language'  => $language,
                            'updated_at'=> now()
                        ]
                    );
                }
            }
            DB::commit();
            $this->dispatch('notify', type: 'success', message: 'Estoque salvo com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: 'Erro crítico: ' . $e->getMessage());
        }
    }

    // ... Restante das funções (Modal, Render) iguais ...
    public function updatedEditingPrintId($value)
    {
        $this->resetValidation();
        $this->reset('modalPhoto', 'modalComment', 'existingPhotoUrl');

        if (!$value) return;

        $item = StockItem::where('store_id', $this->userStoreId)
                         ->where('catalog_print_id', $value)
                         ->first();

        if ($item) {
            $this->modalComment = $item->comments;
            $this->existingPhotoUrl = $item->image_path;
        }
    }

    public function saveDetails()
    {
        if (!$this->editingPrintId) return;

        $item = StockItem::firstOrCreate(
            ['store_id' => $this->userStoreId, 'catalog_print_id' => $this->editingPrintId, 'catalog_product_id' => null],
            ['quantity' => 0, 'price' => 0]
        );

        $item->comments = $this->modalComment;

        if ($this->modalPhoto) {
            $path = $this->modalPhoto->store('stock-photos', 'public');
            $item->image_path = $path;
        }

        $item->save();
        $this->dispatch('notify', type: 'success', message: 'Detalhes atualizados!');
        $this->showModal = false;
    }

    public function render()
    {
        $gameId = \App\Models\Game::where('url_slug', $this->gameSlug)->value('id');
        $sort = $this->sortOption ?? 'name_asc';

        $query = \App\Models\Catalog\CatalogPrint::query()
            ->select('catalog_prints.*')
            ->with(['concept', 'set', 'stockItems' => fn($q) => $q->withoutGlobalScopes()->where('store_id', $this->userStoreId)]);

        // APLICAÇÃO DA ORDENAÇÃO GLOBAL COM OS NOVOS ÍNDICES
        if (str_contains($sort, 'name_en')) {
            $query->join('catalog_concepts', 'catalog_prints.concept_id', '=', 'catalog_concepts.id')
                ->orderBy('catalog_concepts.name', str_contains($sort, 'asc') ? 'asc' : 'desc');
        } elseif (str_contains($sort, 'number')) {
            $query->join('sets', 'catalog_prints.set_id', '=', 'sets.id')
                ->orderBy('sets.released_at', str_contains($sort, 'asc') ? 'desc' : 'asc')
                ->orderBy('catalog_prints.collector_number', 'asc');
        } elseif (str_contains($sort, 'price')) {
            $query->leftJoin('stock_items', 'catalog_prints.id', '=', 'stock_items.catalog_print_id')
                ->orderBy('stock_items.price', str_contains($sort, 'asc') ? 'asc' : 'desc');
        } elseif (str_contains($sort, 'qty')) {
            $query->leftJoin('stock_items', 'catalog_prints.id', '=', 'stock_items.catalog_print_id')
                ->orderBy('stock_items.quantity', str_contains($sort, 'asc') ? 'asc' : 'desc');
        } else {
            $query->orderBy('catalog_prints.printed_name', str_contains($sort, 'asc') ? 'asc' : 'desc');
        }

        $query->whereHas('set', fn($q) => $q->where('game_id', $gameId));

        if ($this->searchType === 'minhaLoja') {
            $query->whereHas('stockItems', function($q) {
                $q->withoutGlobalScopes() // <--- ESSA É A CHAVE MÁGICA
                ->where('store_id', $this->userStoreId)
                ->where('quantity', '>=', 0);
            });
        }

        if ($this->search) {
            $term = $this->search;
            $query->where(function($q) use ($term) {
                $q->where('printed_name', 'like', "%{$term}%")
                ->orWhereHas('concept', fn($c) => $c->where('name', 'like', "%{$term}%"));
            });
        }

        $catalogItems = $query->paginate(50);
        $expandedItems = collect();

        foreach ($catalogItems as $print) {
            if ($print->stockItems->isEmpty()) {
                $expandedItems->push(['print' => $print, 'stock' => null, 'unique_row_id' => 'p' . $print->id]);
            } else {
                foreach ($print->stockItems as $stock) {
                    $expandedItems->push(['print' => $print, 'stock' => $stock, 'unique_row_id' => 's' . $stock->id]);
                }
            }
        }

        return view('livewire.store.dashboard.stock.manage-inventory', [
            'items' => $expandedItems,
            'pagination' => $catalogItems
        ])->extends('layouts.dashboard')->section('content');
    }

    public function processImport()
    {
        $this->importErrors = [];
        $lines = explode("\n", str_replace("\r", "", trim($this->importText)));
        
        if (empty($lines[0])) {
            $this->importErrors[] = "A caixa de texto está vazia.";
            return;
        }

        $validData = [];
        $storeId = auth('store_user')->user()->current_store_id;

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match($this->importPattern, $line, $matches)) {
                $qtd = (int)$matches['qtd'];
                if ($this->limitToFour == 1 && $qtd > 4) $qtd = 4;

                $lineExtras = [];
                if (!empty($matches['extras'])) {
                    $lineExtras = array_map('trim', explode(',', strtolower($matches['extras'])));
                }
                $finalExtras = array_values(array_unique(array_merge($this->selectedExtras, $lineExtras)));
                sort($finalExtras);

                $validData[] = [
                    'line'      => $index + 1,
                    'quantity'  => $qtd,
                    'name'      => trim($matches['name']),
                    'set_code'  => strtoupper($matches['set']),
                    'condition' => strtoupper($matches['cond']),
                    'language'  => strtoupper($matches['lang']),
                    'extras'    => $finalExtras,
                    'price'     => !empty($matches['price']) ? (float)$matches['price'] : 0,
                ];
            } else {
                $this->importErrors[] = "Linha " . ($index + 1) . ": Formato inválido.";
            }
        }

        if (!empty($this->importErrors)) return;

        \DB::beginTransaction();

        try {
            foreach ($validData as $item) {
                $print = \App\Models\Catalog\CatalogPrint::whereHas('set', function($q) use ($item) {
                    $q->where('code', $item['set_code']);
                })
                ->where(function($q) use ($item) {
                    $q->where('printed_name', $item['name'])
                    ->orWhereHas('concept', function($sub) use ($item) {
                        $sub->where('name', $item['name']);
                    });
                })->first();

                if (!$print) {
                    // Substituímos o dd() por um erro visual que não trava o processo
                    $this->importErrors[] = "Carta não encontrada no catálogo: " . $item['name'] . " [" . $item['set_code'] . "]";
                    continue; 
                }

                $existingItems = \App\Models\StockItem::where('store_id', $storeId)
                    ->where('catalog_print_id', $print->id)
                    ->where('condition', $item['condition'])
                    ->where('language', $item['language'])
                    ->get();

                $existing = $existingItems->first(function($si) use ($item) {
                    return $si->extras === $item['extras'];
                });

                if ($existing) {
                    $existing->quantity = $existing->quantity + $item['quantity'];
                    if ($this->limitToFour == 1 && $existing->quantity > 4) $existing->quantity = 4;
                    $existing->price = $item['price'] > 0 ? $item['price'] : $existing->price;
                    $existing->save();
                } else {
                    \App\Models\StockItem::create([
                        'store_id'         => $storeId,
                        'catalog_print_id' => $print->id,
                        'condition'        => $item['condition'],
                        'language'         => $item['language'],
                        'extras'           => $item['extras'],
                        'quantity'         => $item['quantity'],
                        'price'            => $item['price']
                    ]);
                    // O dd() de teste de criação foi removido daqui
                }
            }

            if (!empty($this->importErrors)) {
                \DB::rollBack();                
                return; // Impede que finalize se houve erro em alguma carta
            }

            \DB::commit();
            // O dd() do último ID foi removido daqui

            $this->reset(['importText', 'selectedExtras']);
            
            if (property_exists($this, 'activeTab')) {
                $this->activeTab = 'lista';
            } elseif (property_exists($this, 'viewMode')) {
                $this->viewMode = 'list';
            }
            
            $this->dispatch('inventory-updated');
            $this->dispatch('notify', type: 'success', message: 'Importação concluída com sucesso!');

        } catch (\Exception $e) {
            \DB::rollBack();
            // Substituímos o dd() do "Assassino Silencioso" por um alerta de erro na tela
            $this->dispatch('notify', type: 'error', message: 'Erro crítico: ' . $e->getMessage());
        }
    }
}