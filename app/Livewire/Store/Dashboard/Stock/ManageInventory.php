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
    public $importText = '';   // Onde ficará o texto colado
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
        if (!isset($formData['items']) || empty($formData['items'])) return;

        // 1. Descobre quais itens dessa página JÁ existem no banco para não fazer consultas repetidas
        $printIds = array_keys($formData['items']);
        $existingItems = StockItem::where('store_id', $this->userStoreId)
            ->whereIn('catalog_print_id', $printIds)
            ->pluck('catalog_print_id')
            ->toArray();

        DB::beginTransaction();
        try {
            foreach ($formData['items'] as $printId => $data) {
                
                // --- CORREÇÃO 1: PREÇO ---
                // Simplesmente troca vírgula por ponto. 
                // "0,25" vira "0.25". "25,00" vira "25.00".
                $priceRaw = $data['price'] ?? 0;
                $price = (float) str_replace(',', '.', $priceRaw); 

                $qty = (int) ($data['qty'] ?? 0);
                $cond = $data['condition'] ?? 'NM';
                $language = $data['language'] ?? 'en'; 
                $extras = $data['extras'] ?? [];

                // --- CORREÇÃO 2: EVITAR SALVAR VAZIOS ---
                // Verifica se tem algum dado relevante (Preço, Qtd ou Extras)
                $hasData = ($qty > 0) || ($price > 0.0) || (!empty($extras));
                
                // Verifica se o item já existe no banco
                $existsInDb = in_array($printId, $existingItems);

                // SE não existe no banco E não tem dados preenchidos -> PULA (Não salva lixo)
                if (!$existsInDb && !$hasData) {
                    continue;
                }

                // Se chegou aqui, ou já existia (e vamos atualizar) ou é novo e tem dados
                StockItem::updateOrCreate(
                    [
                        'store_id'         => $this->userStoreId,
                        'catalog_print_id' => $printId,
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
            DB::commit();
            $this->dispatch('notify', type: 'success', message: 'Estoque salvo com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('notify', type: 'error', message: 'Erro: ' . $e->getMessage());
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
            ['store_id' => $this->userStoreId, 'catalog_print_id' => $this->editingPrintId],
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
    // 1. Buscamos o ID do Jogo primeiro. Isso tira um peso gigantesco da consulta principal.
    $gameId = \App\Models\Game::where('url_slug', $this->gameSlug)->value('id');

    $query = CatalogPrint::query()
        ->with([
            'concept', 
            'set', 
            'stockItems' => fn($q) => $q->where('store_id', $this->userStoreId)
        ]);

    // 2. Filtro ultra-otimizado (1 nível apenas, usando o ID em vez do slug na tabela distante)
    $query->whereHas('concept', fn($q) => $q->where('game_id', $gameId));

    if ($this->searchType === 'minhaLoja') {
        $query->whereHas('stockItems', function($q) {
            $q->where('store_id', $this->userStoreId)
            ->where('quantity', '>', 0);
        });
    }

    if ($this->search) {
        $term = $this->search;
        $query->where(function(Builder $q) use ($term) {
            $q->where('printed_name', 'like', "%{$term}%")
            ->orWhereHas('concept', fn($c) => $c->where('name', 'like', "%{$term}%"))
            ->orWhere('collector_number', $term);
        });
    }

    if ($this->filterSet) $query->whereHas('set', fn($q) => $q->where('code', $this->filterSet)->orWhere('name', 'like', "%{$this->filterSet}%"));
    if ($this->filterColor) $query->whereHas('concept', fn($q) => $q->where('color_identity', $this->filterColor));
    if ($this->filterRarity) $query->where('rarity', $this->filterRarity);
    if ($this->filterLanguage) $query->where('language_code', $this->filterLanguage);

    switch ($this->sortOption) {
        case 'name_asc': 
            $query->orderBy('printed_name', 'asc');
            break;

        case 'name_desc': 
            $query->orderBy('printed_name', 'desc');
            break;    

        case 'name_en_asc': 
            $query->join('catalog_concepts', 'catalog_prints.concept_id', '=', 'catalog_concepts.id')
                ->orderBy('catalog_concepts.name', 'asc')
                ->select('catalog_prints.*');
            break;

        case 'name_en_desc': 
            $query->join('catalog_concepts', 'catalog_prints.concept_id', '=', 'catalog_concepts.id')
                ->orderBy('catalog_concepts.name', 'desc')
                ->select('catalog_prints.*');
            break;

        case 'price_asc': 
            $query->leftJoin('stock_items', function($join) {
                $join->on('catalog_prints.id', '=', 'stock_items.catalog_print_id')
                    ->where('stock_items.store_id', $this->userStoreId);
            })->orderBy('stock_items.price', 'asc')->select('catalog_prints.*');
            break;

        case 'price_desc': 
            $query->leftJoin('stock_items', function($join) {
                $join->on('catalog_prints.id', '=', 'stock_items.catalog_print_id')
                    ->where('stock_items.store_id', $this->userStoreId);
            })->orderBy('stock_items.price', 'desc')->select('catalog_prints.*');
            break;

        case 'quantity_asc': 
            $query->leftJoin('stock_items', function($join) {
                $join->on('catalog_prints.id', '=', 'stock_items.catalog_print_id')
                    ->where('stock_items.store_id', $this->userStoreId);
            })->orderBy('stock_items.quantity', 'asc')->select('catalog_prints.*');
            break;

        case 'quantity_desc': 
            $query->leftJoin('stock_items', function($join) {
                $join->on('catalog_prints.id', '=', 'stock_items.catalog_print_id')
                    ->where('stock_items.store_id', $this->userStoreId);
            })->orderBy('stock_items.quantity', 'desc')->select('catalog_prints.*');
            break;

        case 'number_desc': 
            $query->orderBy('collector_number', 'desc');
            break;

        case 'number_asc':
        default: 
            $query->orderBy('collector_number', 'asc');
            break;
    }

    // 3. Voltamos ao paginate(50) para respeitar o seu Blade e mostrar a contagem total
    return view('livewire.store.dashboard.stock.manage-inventory', [
        'items' => $query->paginate(50)
    ])->extends('layouts.dashboard')->section('content');
    }
}