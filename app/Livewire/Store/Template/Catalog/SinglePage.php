<?php

namespace App\Livewire\Store\Template\Catalog;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Store;
use App\Models\Game;
use App\Models\Catalog\CatalogPrint;
use App\Models\Catalog\CatalogConcept;

class SinglePage extends Component
{
    use WithPagination;

    // Variáveis da Rota
    public $slug;
    public $gameSlug;

    // Variáveis da Página
    public $loja;
    public $game;

    // Filtros da Tela
    public $sortOrder = 'name_asc'; 
    public $raridade = 'todas';
    public $cor = 'todas';
    public $perPage = 30;
    public $com_estoque = false;
    
    // O Toggle de Agrupamento Mágico
    public $agrupar_conceito = true; 

    public function mount($slug, $gameSlug)
    {
        $this->slug = $slug;
        $this->loja = Store::with('visual')->where('url_slug', $slug)->firstOrFail();

        $this->gameSlug = $gameSlug;
        $this->game = Game::where('url_slug', $gameSlug)->firstOrFail();
    }

    public function updated($propertyName)
    {
        $this->resetPage();
    }

    public function render()
    {
        if ($this->agrupar_conceito) {
            // ----------------------------------------------------------------
            // MODO AGRUPADO (POR CONCEITO)
            // ----------------------------------------------------------------
            $query = CatalogConcept::select('catalog_concepts.*')
                        ->where('catalog_concepts.game_id', $this->game->id)
                        ->where('catalog_concepts.name', 'NOT LIKE', 'A-%');

            if ($this->cor !== 'todas') {
                $query->join('mtg_concepts as mc', 'catalog_concepts.specific_id', '=', 'mc.id');

                if (in_array($this->cor, ['W', 'U', 'B', 'R', 'G'])) {
                    $query->where('mc.colors', 'LIKE', '%"' . $this->cor . '"%')
                             ->where('mc.colors', 'NOT LIKE', '%,%');
                } elseif ($this->cor === 'M') {
                    $query->where('mc.colors', 'LIKE', '%,%');
                } elseif ($this->cor === 'C') {
                    $query->where(function($q) {
                        $q->whereNull('mc.colors')
                          ->orWhere('mc.colors', '[]')
                          ->orWhere('mc.colors', '');
                    })
                    ->where('catalog_concepts.type_line', 'NOT LIKE', '%Artifact%')
                    ->where('catalog_concepts.type_line', 'NOT LIKE', '%Land%');
                } elseif ($this->cor === 'A') {
                    $query->where('catalog_concepts.type_line', 'LIKE', '%Artifact%');
                } elseif ($this->cor === 'L') {
                    $query->where('catalog_concepts.type_line', 'LIKE', '%Land%');
                }
            }

            // MÁGICA: Além de somar o estoque, pegamos o ID exato do print vencedor (menor preço)
            $estoqueSubquery = \App\Models\StockItem::select(
                'cp.concept_id',
                \DB::raw('SUM(stock_items.quantity) as total_estoque'),
                \DB::raw('MIN(CASE WHEN stock_items.quantity > 0 THEN stock_items.price END) as menor_preco'),
                \DB::raw('MIN(stock_items.price) as ultimo_preco'),
                \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.extras END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_extras"),
                \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.discount_percent END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_desconto"),
                \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN cp.id END ORDER BY stock_items.price ASC SEPARATOR ','), ',', 1) as print_id_in_stock"),
                \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(cp.id ORDER BY stock_items.price ASC SEPARATOR ','), ',', 1) as print_id_out_stock")
            )
            ->join('catalog_prints as cp', 'stock_items.catalog_print_id', '=', 'cp.id')
            ->where('stock_items.store_id', $this->loja->id)
            ->groupBy('cp.concept_id');

            $query->addSelect(
                    \DB::raw('COALESCE(estoque.total_estoque, 0) as total_estoque'),
                    'estoque.menor_preco',
                    'estoque.ultimo_preco',
                    'estoque.menor_preco_extras',
                    'estoque.menor_preco_desconto',
                    'estoque.print_id_in_stock',
                    'estoque.print_id_out_stock'
                )
                ->leftJoinSub($estoqueSubquery, 'estoque', function ($join) {
                    $join->on('catalog_concepts.id', '=', 'estoque.concept_id');
                });

        } else {
            // ----------------------------------------------------------------
            // MODO DESAGRUPADO (POR PRINT)
            // ----------------------------------------------------------------
            $query = CatalogPrint::select('catalog_prints.*')
                        ->join('catalog_concepts as cc', 'catalog_prints.concept_id', '=', 'cc.id')
                        ->where('cc.game_id', $this->game->id)
                        ->where('catalog_prints.printed_name', 'NOT LIKE', 'A-%'); 
                        // REMOVIDA A TRAVA DE LÍNGUA: Mostra todas as línguas como produtos separados!

            if ($this->cor !== 'todas') {
                if ($this->cor === 'A') {
                    $query->where('catalog_prints.type_line', 'LIKE', '%Artifact%');
                } elseif ($this->cor === 'L') {
                    $query->where('catalog_prints.type_line', 'LIKE', '%Land%');
                } else {
                    $query->join('mtg_concepts as mc', 'cc.specific_id', '=', 'mc.id');

                    if (in_array($this->cor, ['W', 'U', 'B', 'R', 'G'])) {
                        $query->where('mc.colors', 'LIKE', '%"' . $this->cor . '"%')
                                 ->where('mc.colors', 'NOT LIKE', '%,%');
                    } elseif ($this->cor === 'M') {
                        $query->where('mc.colors', 'LIKE', '%,%');
                    } elseif ($this->cor === 'C') {
                        $query->where(function($q) {
                            $q->whereNull('mc.colors')
                              ->orWhere('mc.colors', '[]')
                              ->orWhere('mc.colors', '');
                        })
                        ->where('catalog_prints.type_line', 'NOT LIKE', '%Artifact%')
                        ->where('catalog_prints.type_line', 'NOT LIKE', '%Land%');
                    }
                }
            }

            if ($this->raridade !== 'todas') {
                $query->where('catalog_prints.rarity', $this->raridade); 
            }

            $estoqueSubquery = \App\Models\StockItem::select(
                'catalog_print_id',
                \DB::raw('SUM(stock_items.quantity) as total_estoque'),
                \DB::raw('MIN(CASE WHEN stock_items.quantity > 0 THEN stock_items.price END) as menor_preco'),
                \DB::raw('MIN(stock_items.price) as ultimo_preco'),
                \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.extras END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_extras"),
                \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.discount_percent END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_desconto")
            )
            ->where('store_id', $this->loja->id)
            ->groupBy('catalog_print_id');

            $query->addSelect(
                    \DB::raw('COALESCE(estoque.total_estoque, 0) as total_estoque'),
                    'estoque.menor_preco',
                    'estoque.ultimo_preco',
                    'estoque.menor_preco_extras',
                    'estoque.menor_preco_desconto'
                )
                ->with(['concept'])
                ->leftJoinSub($estoqueSubquery, 'estoque', function ($join) {
                    $join->on('catalog_prints.id', '=', 'estoque.catalog_print_id');
                });
        }

        // ==========================================
        // FILTROS GERAIS E ORDENAÇÃO
        // ==========================================
        if ($this->com_estoque) {
            $query->where('estoque.total_estoque', '>', 0);
        }

        $tabela = $this->agrupar_conceito ? 'catalog_concepts' : 'catalog_prints';
        $colunaNome = $this->agrupar_conceito ? 'name' : 'printed_name';
        
        switch ($this->sortOrder) {
            case 'price_asc':
                $query->orderByRaw('estoque.menor_preco IS NULL')->orderBy('estoque.menor_preco', 'asc');
                break;
            case 'price_desc':
                $query->orderByRaw('estoque.menor_preco IS NULL')->orderBy('estoque.menor_preco', 'desc');
                break;
            case 'name_desc':
                $query->orderBy("$tabela.$colunaNome", 'desc');
                break;
            case 'number_desc':
                if(!$this->agrupar_conceito) {
                    $query->orderByRaw('CAST(catalog_prints.collector_number AS UNSIGNED) DESC');
                }
                break;
            case 'number_asc':
                if(!$this->agrupar_conceito) {
                    $query->orderByRaw('CAST(catalog_prints.collector_number AS UNSIGNED) ASC');
                }
                break;
            case 'name_asc':
            default:
                $query->orderBy("$tabela.$colunaNome", 'asc');
                break;
        }

        $cartas = $query->paginate($this->perPage)->onEachSide(0);

        // ==========================================
        // MÁGICA DE CAPAS E TRADUÇÃO (SÓ PARA AS CARTAS DA TELA)
        // ==========================================
        $printsRelacionados = collect();
        if ($cartas->count() > 0) {
            if ($this->agrupar_conceito) {
                // Modo Conceito: Puxa TODOS os prints dessas 30 cartas para acharmos a imagem vencedora e o texto PT
                $conceptIds = $cartas->pluck('id')->toArray();
                $printsRelacionados = CatalogPrint::whereIn('concept_id', $conceptIds)
                                                ->orderBy('id', 'desc')
                                                ->get()
                                                ->groupBy('concept_id');
            } else {
                // Modo Print: Busca APENAS os textos PT para servir de legenda
                $setIds = $cartas->pluck('set_id')->unique()->toArray();
                $collectorNumbers = $cartas->pluck('collector_number')->unique()->toArray();

                $printsRelacionados = CatalogPrint::whereIn('set_id', $setIds)
                                                ->whereIn('collector_number', $collectorNumbers)
                                                ->whereIn('language_code', ['pt', 'PT', 'pt-br', 'pt-BR'])
                                                ->get()
                                                ->groupBy(function($item) {
                                                    return $item->set_id . '_' . $item->collector_number;
                                                });
            }
        }

        $cartas->getCollection()->transform(function ($carta) use ($printsRelacionados) {
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
            
            if ($this->agrupar_conceito) {
                $prints = $printsRelacionados->get($carta->id, collect());
                
                // 1. Título em Português (O mais recente não nulo)
                $printPt = $prints->first(function($p) {
                    return in_array(strtolower($p->language_code), ['pt', 'pt-br']) && !empty(trim($p->printed_name));
                });
                
                $carta->nome_localizado = $printPt?->printed_name ?? $carta->name;
                $carta->name = $carta->name; // Legenda (Inglês oficial do conceito)
                
                // 2. Imagem Vencedora baseada no Estoque
                $imagemBruta = null;

                if ($total > 0 && !empty($carta->print_id_in_stock)) {
                    $printVencedor = $prints->firstWhere('id', $carta->print_id_in_stock);
                    $imagemBruta = $printVencedor?->image_url ?? $printVencedor?->image_path;
                } elseif ($total === 0 && $ultimoPreco > 0 && !empty($carta->print_id_out_stock)) {
                    $printVencedor = $prints->firstWhere('id', $carta->print_id_out_stock);
                    $imagemBruta = $printVencedor?->image_url ?? $printVencedor?->image_path;
                }

                // Fallback de imagem (Fantasma)
                if (empty($imagemBruta)) {
                    $printEn = $prints->first(fn($p) => strtolower($p->language_code) === 'en' && (!empty($p->image_url) || !empty($p->image_path))) 
                                ?? $prints->first(fn($p) => !empty($p->image_url) || !empty($p->image_path)) 
                                ?? $prints->first();
                    $imagemBruta = $printEn?->image_url ?? $printEn?->image_path ?? $carta->image_url ?? $carta->image_path ?? 'https://placehold.co/250x350/eeeeee/999999?text=Sem+Imagem';
                }
                
                $slugDb = $carta->slug;
                $carta->slug_seguro = !empty($slugDb) ? $slugDb : 'card-id-' . $carta->id;
            } else {
                // Modo Print (Desagrupado)
                $key = $carta->set_id . '_' . $carta->collector_number;
                $printPt = $printsRelacionados->get($key, collect())->first(function($p) {
                    return !empty(trim($p->printed_name));
                });

                // INVERSÃO: Nome principal é o original impresso na carta (JP, RU, EN, etc)
                $carta->nome_localizado = !empty($carta->printed_name) ? $carta->printed_name : $carta->concept?->name; 
                
                // Legenda: Nome em português se existir, senão o Inglês do Conceito
                $carta->name = $printPt?->printed_name ?? $carta->concept?->name ?? '---';

                $imagemBruta = $carta->image_url ?? $carta->image_path ?? $carta->concept?->image_url ?? $carta->concept?->image_path ?? 'https://placehold.co/250x350/eeeeee/999999?text=Sem+Imagem';
                
                $slugDb = $carta->concept?->slug ?? \Str::slug($carta->name);
                $carta->slug_seguro = !empty($slugDb) ? $slugDb : 'card-id-' . $carta->id;
            }

            $carta->imagem_final = filter_var($imagemBruta, FILTER_VALIDATE_URL) 
                                 ? $imagemBruta 
                                 : asset($imagemBruta);
            
            $carta->foil = false; 
            $carta->is_concept = $this->agrupar_conceito;

            return $carta;
        });

        return view('livewire.store.template.catalog.single-page', [
            'cartas' => $cartas
        ])->layout('layouts.template', ['loja' => $this->loja]);
    }
}