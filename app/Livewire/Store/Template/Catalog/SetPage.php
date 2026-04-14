<?php

namespace App\Livewire\Store\Template\Catalog;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Store;
use App\Models\Game;
use App\Models\Set;
use App\Models\Catalog\CatalogPrint;
use App\Models\Catalog\CatalogConcept;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SetPage extends Component
{
    use WithPagination;

    // Variáveis da Rota
    public $slug;
    public $gameSlug;
    public $setCode;

    // Variáveis da Página
    public $loja;
    public $game;
    public $set;

    // Filtros da Tela
    #[Url(except: 'number_asc', history: true)]
    public $sortOrder = 'number_asc';

    #[Url(except: 'todas', history: true)]
    public $raridade = 'todas';

    #[Url(except: 'todas', history: true)]
    public $cor = 'todas';

    #[Url(except: 30, history: true)]
    public $perPage = 30;

    #[Url(except: false, history: true)]
    public $com_estoque = false;

    public function mount($slug, $gameSlug, $setCode)
    {
        $this->slug = $slug;
        $this->loja = Store::with('visual')->where('url_slug', $slug)->firstOrFail();

        $this->gameSlug = $gameSlug;
        $this->game = Game::where('url_slug', $gameSlug)->firstOrFail();

        $this->setCode = $setCode;
        $this->set = Set::where('game_id', $this->game->id)
                        ->where('code', $setCode)
                        ->firstOrFail();
    }

    public function updated($propertyName)
    {
        $this->resetPage();
    }

    public function render()
    {
        // 1. O GATEKEEPER: A loja trabalha com esse Set?
        $lojaTrabalhaComOSet = \App\Models\StockItem::where('store_id', $this->loja->id)
            ->whereHas('catalogPrint', function ($q) {
                $q->where('set_id', $this->set->id);
            })->exists();

        if (!$lojaTrabalhaComOSet) {
            $cartas = collect([]); 
        } 
        else {
            // ==========================================
            // FILTRANDO OS IDs BASE (Excluindo variantes A-)
            // ==========================================
            $queryIds = \App\Models\Catalog\CatalogPrint::where('set_id', $this->set->id)
                ->where('language_code', 'en')
                ->where('printed_name', 'NOT LIKE', 'A-%');

            if ($this->set->card_count > 0) {
                $queryIds->whereRaw('CAST(collector_number AS UNSIGNED) <= ?', [$this->set->card_count]);
            }

            // ==========================================
            // A INTELIGÊNCIA HÍBRIDA (Cores vs Tipos)
            // ==========================================
            if ($this->cor !== 'todas') {

                if ($this->cor === 'A') {
                    $queryIds->where('type_line', 'LIKE', '%Artifact%');
                } 
                elseif ($this->cor === 'L') {
                    $queryIds->where('type_line', 'LIKE', '%Land%');
                } 
                else {
                    $queryIds->join('catalog_concepts as cc', 'catalog_prints.concept_id', '=', 'cc.id')
                             ->join('mtg_concepts as mc', 'cc.specific_id', '=', 'mc.id');

                    if (in_array($this->cor, ['W', 'U', 'B', 'R', 'G'])) {
                        $queryIds->where('mc.colors', 'LIKE', '%"' . $this->cor . '"%')
                                 ->where('mc.colors', 'NOT LIKE', '%,%');
                    } 
                    elseif ($this->cor === 'M') {
                        $queryIds->where('mc.colors', 'LIKE', '%,%');
                    } 
                    elseif ($this->cor === 'C') {
                        $queryIds->where(function($q) {
                            $q->whereNull('mc.colors')
                              ->orWhere('mc.colors', '[]')
                              ->orWhere('mc.colors', '');
                        })
                        ->where('catalog_prints.type_line', 'NOT LIKE', '%Artifact%')
                        ->where('catalog_prints.type_line', 'NOT LIKE', '%Land%');
                    }
                }
            }

            $printIds = $queryIds->pluck('catalog_prints.id');

            // ==========================================
            // 2. A TABELA VIRTUAL DE ESTOQUE (CORREÇÃO: AGRUPA POR NÚMERO, NÃO POR ID)
            // ==========================================
            $estoqueSubquery = \App\Models\StockItem::select(
                'cp.collector_number', // O SEGREDO ESTÁ AQUI: Agrupa todas as línguas do mesmo número!
                \DB::raw('SUM(stock_items.quantity) as total_estoque'),
                \DB::raw('MIN(CASE WHEN stock_items.quantity > 0 THEN stock_items.price END) as menor_preco'),
                \DB::raw('MIN(stock_items.price) as ultimo_preco'),
                \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.extras END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_extras"),
                \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.discount_percent END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_desconto")
            )
            ->join('catalog_prints as cp', 'stock_items.catalog_print_id', '=', 'cp.id')
            ->where('stock_items.store_id', $this->loja->id)
            ->where('cp.set_id', $this->set->id)
            ->groupBy('cp.collector_number'); // O SEGREDO ESTÁ AQUI TAMBÉM

            // ==========================================
            // 3. A QUERY MESTRA
            // ==========================================
            $query = \App\Models\Catalog\CatalogPrint::select(
                    'catalog_prints.*',
                    \DB::raw('COALESCE(estoque.total_estoque, 0) as total_estoque'),
                    'estoque.menor_preco',
                    'estoque.ultimo_preco',
                    'estoque.menor_preco_extras',
                    'estoque.menor_preco_desconto'
                )
                ->with(['concept', 'concept.prints']) 
                ->whereIn('catalog_prints.id', $printIds)
                ->leftJoinSub($estoqueSubquery, 'estoque', function ($join) {
                    // A LIGAÇÃO: Usa o número de colecionador para pescar o bloco inteiro de estoque daquele número
                    $join->on('catalog_prints.collector_number', '=', 'estoque.collector_number');
                });

            // ==========================================
            // 4. APLICAÇÃO DOS FILTROS (Raridade e Estoque)
            // ==========================================
            if ($this->raridade !== 'todas') {
                $query->where('catalog_prints.rarity', $this->raridade); 
            }

            if ($this->com_estoque) {
                $query->where('total_estoque', '>', 0);
            }

            // ==========================================
            // 5. ORDENAÇÃO DINÂMICA
            // ==========================================
            switch ($this->sortOrder) {
                case 'price_asc':
                    $query->orderByRaw('estoque.menor_preco IS NULL')
                          ->orderBy('estoque.menor_preco', 'asc');
                    break;
                case 'price_desc':
                    $query->orderByRaw('estoque.menor_preco IS NULL')
                          ->orderBy('estoque.menor_preco', 'desc');
                    break;
                case 'name_asc':
                    $query->orderBy('catalog_prints.printed_name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('catalog_prints.printed_name', 'desc');
                    break;
                case 'number_desc':
                    $query->orderByRaw('CAST(catalog_prints.collector_number AS UNSIGNED) DESC');
                    break;
                case 'number_asc':
                default:
                    $query->orderByRaw('CAST(catalog_prints.collector_number AS UNSIGNED) ASC');
                    break;
            }

            // 6. PAGINAÇÃO
            $cartas = $query->paginate($this->perPage)->onEachSide(0);

            // 7. TRANSFORMANDO DADOS (Blade)
            $cartas->getCollection()->transform(function ($carta) {
                $total = (int) ($carta->total_estoque ?? 0);
                $precoBase = (float) ($carta->menor_preco ?? 0);
                $ultimoPreco = (float) ($carta->ultimo_preco ?? 0);
                $percentualDesconto = (float) ($carta->menor_preco_desconto ?? 0);

                $referenciaParaCalculo = ($total > 0) ? $precoBase : $ultimoPreco;

                $carta->preco_final = ($percentualDesconto > 0) 
                    ? $referenciaParaCalculo * (1 - ($percentualDesconto / 100))
                    : $referenciaParaCalculo;

                $carta->total_estoque = $total;
                $carta->menor_preco = $precoBase;
                $carta->desconto = $percentualDesconto;

                $carta->is_foil = (bool) str_contains(strtolower($carta->menor_preco_extras ?? ''), 'foil');

                // --- Lógica de Nomes PT/EN e Geração de Slug Condicional ---
                $englishName = $carta->concept->name ?? $carta->printed_name;
                $globalPtName = null;

                if ($carta->concept && $carta->concept->relationLoaded('prints')) {
                    $globalPtName = $carta->concept->prints
                                    ->firstWhere(fn ($print) => in_array($print->language_code, ['pt', 'PT', 'pt-br', 'pt-BR']))
                                    ?->printed_name;
                }

                $carta->nome_localizado = $globalPtName ?: $englishName;

                $isBasicLand = str_contains($carta->type_line, 'Basic Land');

                $carta->name = sprintf('%s • #%s', $englishName, $carta->collector_number);

                // --- GERAÇÃO DO SLUG PARA O LINK DO PRODUTO (CONDICIONAL) ---
                if ($isBasicLand) {
                    $tiposBasicos = ['Plains', 'Island', 'Swamp', 'Mountain', 'Forest'];
                    $tipoEncontrado = null;
                    foreach ($tiposBasicos as $tipo) {
                        if (str_contains($carta->type_line, $tipo)) {
                            $tipoEncontrado = $tipo;
                            break;
                        }
                    }

                    if ($tipoEncontrado) {
                        $carta->concept_slug = \Str::slug($tipoEncontrado) . '-' . $carta->collector_number;
                    } else {
                        $carta->concept_slug = \Str::slug($englishName);
                    }
                } else {
                    $carta->concept_slug = \Str::slug($englishName);
                }

                $imagemBruta = $carta->image_url 
                            ?? $carta->image_path 
                            ?? $carta->concept->image_url 
                            ?? $carta->concept->image_path 
                            ?? 'https://placehold.co/250x350/eeeeee/999999?text=Sem+Imagem';

                $carta->imagem_final = filter_var($imagemBruta, FILTER_VALIDATE_URL) 
                                     ? $imagemBruta 
                                     : asset($imagemBruta);

                $carta->foil = false; 

                return $carta;
            });
        }

        return view('livewire.store.template.catalog.set-page', [
            'cartas' => $cartas
        ])->layout('layouts.template', ['loja' => $this->loja]);
    }
}