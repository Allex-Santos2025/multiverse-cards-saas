<?php

namespace App\Livewire\Store\Template\Catalog;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Store;
use App\Models\Game;
use App\Models\Catalog\CatalogPrint;
use App\Models\Catalog\CatalogConcept;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SinglePage extends Component
{
    use WithPagination;

    public $slug, $gameSlug, $loja, $game;

    #[Url(except: 'name_asc', history: true)]
    public $sortOrder = 'name_asc'; 

    #[Url(except: 'todas', history: true)]
    public $raridade = 'todas';

    #[Url(except: 'todas', history: true)]
    public $cor = 'todas';

    #[Url(except: 30, history: true)]
    public $perPage = 30;

    #[Url(except: false, history: true)]
    public $com_estoque = false;
    
    #[Url(except: false, history: true)]
    public $desagrupar = false;

    public function mount($slug, $gameSlug)
    {
        $this->slug = $slug;
        $this->loja = Store::with('visual')->where('url_slug', $slug)->firstOrFail();
        $this->gameSlug = $gameSlug;
        $this->game = Game::where('url_slug', $gameSlug)->firstOrFail();
    }

    public function updated($propertyName) { $this->resetPage(); }

    public function render()
    {
        $precisaCruzarEstoque = $this->com_estoque || in_array($this->sortOrder, ['price_asc', 'price_desc']);

        if (!$this->desagrupar) {
            // MODO AGRUPADO
            $virtualNumberRaw = 'CASE 
                WHEN catalog_prints.type_line LIKE "%Basic Land%" THEN catalog_prints.collector_number 
                WHEN s.code IN ("FEM", "ALL", "HML") AND catalog_prints.collector_number REGEXP "[a-zA-Z]" THEN catalog_prints.collector_number 
                ELSE "" 
            END';

            $query = CatalogPrint::select(
                        \DB::raw('MAX(catalog_prints.id) as id'), 
                        'cc.id as concept_id', 
                        'cc.name',
                        \DB::raw('MAX(catalog_prints.set_id) as set_id'), // Adicionado para o cache de artista
                        \DB::raw('MAX(catalog_prints.type_line) as type_line'), 
                        'cc.slug',
                        \DB::raw("MAX($virtualNumberRaw) as virtual_number")
                    )
                    ->join('catalog_concepts as cc', 'catalog_prints.concept_id', '=', 'cc.id')
                    ->join('sets as s', 'catalog_prints.set_id', '=', 's.id')
                    ->where('cc.game_id', $this->game->id)->where('catalog_prints.is_valid', true)
                    ->groupBy('cc.id', \DB::raw($virtualNumberRaw), 'cc.name', 'cc.slug');

            if ($this->cor !== 'todas') {
                $query->join('mtg_concepts as mc', 'cc.specific_id', '=', 'mc.id');
                if (in_array($this->cor, ['W', 'U', 'B', 'R', 'G'])) {
                    $query->where('mc.colors', 'LIKE', '%"' . $this->cor . '"%')->where('mc.colors', 'NOT LIKE', '%,%');
                } elseif ($this->cor === 'M') {
                    $query->where('mc.colors', 'LIKE', '%,%');
                } elseif ($this->cor === 'C') {
                    $query->where(fn($q) => $q->whereNull('mc.colors')->orWhere('mc.colors', '[]')->orWhere('mc.colors', ''))
                    ->where('catalog_prints.type_line', 'NOT LIKE', '%Artifact%')->where('catalog_prints.type_line', 'NOT LIKE', '%Land%');
                } elseif ($this->cor === 'A') {
                    $query->where('catalog_prints.type_line', 'LIKE', '%Artifact%');
                } elseif ($this->cor === 'L') {
                    $query->where('catalog_prints.type_line', 'LIKE', '%Land%');
                }
            }

            if ($precisaCruzarEstoque) {
                $estoqueVirtualNumber = 'CASE 
                    WHEN cp.type_line LIKE "%Basic Land%" THEN cp.collector_number 
                    WHEN s_estoque.code IN ("FEM", "ALL", "HML") AND cp.collector_number REGEXP "[a-zA-Z]" THEN cp.collector_number 
                    ELSE "" 
                END';

                $estoqueSubquery = \App\Models\StockItem::select(
                    'cp.concept_id', \DB::raw("MAX($estoqueVirtualNumber) as virtual_number"),
                    \DB::raw('SUM(stock_items.quantity) as total_estoque'),
                    \DB::raw('MIN(CASE WHEN stock_items.quantity > 0 THEN stock_items.price END) as menor_preco'),
                    \DB::raw('MIN(stock_items.price) as ultimo_preco'),
                    \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.extras END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_extras"),
                    \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.discount_percent END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_desconto"),
                    \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN cp.id END ORDER BY stock_items.price ASC SEPARATOR ','), ',', 1) as print_id_in_stock"),
                    \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(cp.id ORDER BY stock_items.price ASC SEPARATOR ','), ',', 1) as print_id_out_stock")
                )
                ->join('catalog_prints as cp', 'stock_items.catalog_print_id', '=', 'cp.id')
                ->join('sets as s_estoque', 'cp.set_id', '=', 's_estoque.id')
                ->where('stock_items.store_id', $this->loja->id)
                ->groupBy('cp.concept_id', \DB::raw($estoqueVirtualNumber));

                $query->addSelect(\DB::raw('COALESCE(estoque.total_estoque, 0) as total_estoque'), 'estoque.menor_preco', 'estoque.ultimo_preco', 'estoque.menor_preco_extras', 'estoque.menor_preco_desconto', 'estoque.print_id_in_stock', 'estoque.print_id_out_stock');
                $query->groupBy('estoque.total_estoque', 'estoque.menor_preco', 'estoque.ultimo_preco', 'estoque.menor_preco_extras', 'estoque.menor_preco_desconto', 'estoque.print_id_in_stock', 'estoque.print_id_out_stock');

                if ($this->com_estoque) {
                    $query->joinSub($estoqueSubquery, 'estoque', fn($join) => $join->on('cc.id', '=', 'estoque.concept_id')->on(\DB::raw($virtualNumberRaw), '=', 'estoque.virtual_number'));
                } else {
                    $query->leftJoinSub($estoqueSubquery, 'estoque', fn($join) => $join->on('cc.id', '=', 'estoque.concept_id')->on(\DB::raw($virtualNumberRaw), '=', 'estoque.virtual_number'));
                }
            }

        } else {
            // MODO DESAGRUPADO
            $query = CatalogPrint::select('catalog_prints.*', 'mtg_prints.artist', 's.code as set_code')
                        ->join('catalog_concepts as cc', 'catalog_prints.concept_id', '=', 'cc.id')
                        ->join('sets as s', 'catalog_prints.set_id', '=', 's.id')
                        ->leftJoin('mtg_prints', 'catalog_prints.specific_id', '=', 'mtg_prints.id')
                        ->where('cc.game_id', $this->game->id)->where('catalog_prints.is_valid', true);

            if ($this->cor !== 'todas') {
                if ($this->cor === 'A') $query->where('catalog_prints.type_line', 'LIKE', '%Artifact%');
                elseif ($this->cor === 'L') $query->where('catalog_prints.type_line', 'LIKE', '%Land%');
                else {
                    $query->join('mtg_concepts as mc', 'cc.specific_id', '=', 'mc.id');
                    if (in_array($this->cor, ['W', 'U', 'B', 'R', 'G'])) $query->where('mc.colors', 'LIKE', '%"' . $this->cor . '"%')->where('mc.colors', 'NOT LIKE', '%,%');
                    elseif ($this->cor === 'M') $query->where('mc.colors', 'LIKE', '%,%');
                    elseif ($this->cor === 'C') $query->where(fn($q) => $q->whereNull('mc.colors')->orWhere('mc.colors', '[]')->orWhere('mc.colors', ''))->where('catalog_prints.type_line', 'NOT LIKE', '%Artifact%')->where('catalog_prints.type_line', 'NOT LIKE', '%Land%');
                }
            }

            if ($this->raridade !== 'todas') $query->where('catalog_prints.rarity', $this->raridade); 

            if ($precisaCruzarEstoque) {
                $estoqueSubquery = \App\Models\StockItem::select(
                    'catalog_print_id', \DB::raw('SUM(stock_items.quantity) as total_estoque'),
                    \DB::raw('MIN(CASE WHEN stock_items.quantity > 0 THEN stock_items.price END) as menor_preco'), \DB::raw('MIN(stock_items.price) as ultimo_preco'),
                    \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.extras END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_extras"),
                    \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.discount_percent END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_desconto")
                )->where('store_id', $this->loja->id)->groupBy('catalog_print_id');

                $query->addSelect(\DB::raw('COALESCE(estoque.total_estoque, 0) as total_estoque'), 'estoque.menor_preco', 'estoque.ultimo_preco', 'estoque.menor_preco_extras', 'estoque.menor_preco_desconto')->with(['concept']);
                if ($this->com_estoque) $query->joinSub($estoqueSubquery, 'estoque', fn($join) => $join->on('catalog_prints.id', '=', 'estoque.catalog_print_id'));
                else $query->leftJoinSub($estoqueSubquery, 'estoque', fn($join) => $join->on('catalog_prints.id', '=', 'estoque.catalog_print_id'));
            } else {
                $query->with(['concept']);
            }
        }

        $tabela = !$this->desagrupar ? 'cc' : 'catalog_prints';
        $colunaNome = !$this->desagrupar ? 'name' : 'printed_name';
        
        switch ($this->sortOrder) {
            case 'price_asc': $query->orderByRaw('estoque.menor_preco IS NULL')->orderBy('estoque.menor_preco', 'asc'); break;
            case 'price_desc': $query->orderByRaw('estoque.menor_preco IS NULL')->orderBy('estoque.menor_preco', 'desc'); break;
            case 'name_desc': $query->orderBy("$tabela.$colunaNome", 'desc'); break;
            case 'number_desc': if($this->desagrupar) $query->orderByRaw('CAST(catalog_prints.collector_number AS UNSIGNED) DESC'); break;
            case 'number_asc': if($this->desagrupar) $query->orderByRaw('CAST(catalog_prints.collector_number AS UNSIGNED) ASC'); break;
            case 'name_asc': default: $query->orderBy("$tabela.$colunaNome", 'asc'); break;
        }

        $cacheKey = "count_v10_{$this->game->id}_{$this->desagrupar}_{$this->cor}_{$this->raridade}_{$this->com_estoque}_{$this->loja->id}";
        $totalReal = cache()->remember($cacheKey, now()->addHours(4), function () use ($query) {
            $queryParaCount = clone $query->getQuery();
            $queryParaCount->orders = null;
            return $queryParaCount->getCountForPagination();
        });

        if ($this->desagrupar && !$precisaCruzarEstoque) {
            $query->select('catalog_prints.id');
            $cartas = $query->paginate($this->perPage, ['*'], 'page', null, $totalReal)->onEachSide(0);
            $idsNaPagina = $cartas->pluck('id')->toArray();
            
            if (!empty($idsNaPagina)) {
                $idsString = implode(',', $idsNaPagina);
                $cartasCompletas = CatalogPrint::with(['concept'])
                    ->select('catalog_prints.*', 'mtg_prints.artist', 'sets.code as set_code') 
                    ->leftJoin('mtg_prints', 'catalog_prints.specific_id', '=', 'mtg_prints.id')
                    ->join('sets', 'catalog_prints.set_id', '=', 'sets.id')
                    ->whereIn('catalog_prints.id', $idsNaPagina)
                    ->orderByRaw("FIELD(catalog_prints.id, $idsString)")
                    ->get();
                $cartas->setCollection($cartasCompletas);
            }
        } else {
            $cartas = $query->paginate($this->perPage, ['*'], 'page', null, $totalReal)->onEachSide(0);
        }

        if (!$precisaCruzarEstoque && $cartas->count() > 0) {
            if (!$this->desagrupar) {
                $idsNaTela = $cartas->pluck('concept_id')->toArray();
                $estoqueVirtualNumber = 'CASE 
                    WHEN cp.type_line LIKE "%Basic Land%" THEN cp.collector_number 
                    WHEN s_estoque.code IN ("FEM", "ALL", "HML") AND cp.collector_number REGEXP "[a-zA-Z]" THEN cp.collector_number 
                    ELSE "" 
                END';

                $estoquesRapidos = \App\Models\StockItem::select(
                    'cp.concept_id', \DB::raw("MAX($estoqueVirtualNumber) as virtual_number"),
                    \DB::raw('SUM(stock_items.quantity) as total_estoque'), \DB::raw('MIN(CASE WHEN stock_items.quantity > 0 THEN stock_items.price END) as menor_preco'),
                    \DB::raw('MIN(stock_items.price) as ultimo_preco'), \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.extras END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_extras"),
                    \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.discount_percent END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_desconto"),
                    \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN cp.id END ORDER BY stock_items.price ASC SEPARATOR ','), ',', 1) as print_id_in_stock"),
                    \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(cp.id ORDER BY stock_items.price ASC SEPARATOR ','), ',', 1) as print_id_out_stock")
                )
                ->join('catalog_prints as cp', 'stock_items.catalog_print_id', '=', 'cp.id')->join('sets as s_estoque', 'cp.set_id', '=', 's_estoque.id')
                ->where('stock_items.store_id', $this->loja->id)->whereIn('cp.concept_id', $idsNaTela) 
                ->groupBy('cp.concept_id', \DB::raw($estoqueVirtualNumber))->get()
                ->keyBy(fn($item) => $item->concept_id . '_' . $item->virtual_number);

                foreach ($cartas as $carta) {
                    $key = $carta->concept_id . '_' . $carta->virtual_number;
                    $st = $estoquesRapidos->get($key);
                    $carta->total_estoque = $st->total_estoque ?? 0;
                    $carta->menor_preco = $st->menor_preco ?? null; $carta->ultimo_preco = $st->ultimo_preco ?? null;
                    $carta->menor_preco_extras = $st->menor_preco_extras ?? null; $carta->menor_preco_desconto = $st->menor_preco_desconto ?? null;
                    $carta->print_id_in_stock = $st->print_id_in_stock ?? null; $carta->print_id_out_stock = $st->print_id_out_stock ?? null;
                }
            } else {
                $idsNaTela = $cartas->pluck('id')->toArray();
                $estoquesRapidos = \App\Models\StockItem::select(
                    'catalog_print_id', \DB::raw('SUM(stock_items.quantity) as total_estoque'), \DB::raw('MIN(CASE WHEN stock_items.quantity > 0 THEN stock_items.price END) as menor_preco'),
                    \DB::raw('MIN(stock_items.price) as ultimo_preco'), \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.extras END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_extras"),
                    \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.discount_percent END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_desconto")
                )
                ->where('store_id', $this->loja->id)->whereIn('catalog_print_id', $idsNaTela)->groupBy('catalog_print_id')->get()->keyBy('catalog_print_id');

                foreach ($cartas as $carta) {
                    $st = $estoquesRapidos->get($carta->id);
                    $carta->total_estoque = $st->total_estoque ?? 0; $carta->menor_preco = $st->menor_preco ?? null; $carta->ultimo_preco = $st->ultimo_preco ?? null;
                    $carta->menor_preco_extras = $st->menor_preco_extras ?? null; $carta->menor_preco_desconto = $st->menor_preco_desconto ?? null;
                }
            }
        }

        $printsRelacionados = collect();
        if ($cartas->count() > 0) {
            if (!$this->desagrupar) {
                $conceptIds = $cartas->pluck('concept_id')->toArray();
                $printsRelacionados = CatalogPrint::select('catalog_prints.*', 'mtg_prints.artist', 'sets.code as set_code')
                                                ->leftJoin('mtg_prints', 'catalog_prints.specific_id', '=', 'mtg_prints.id')
                                                ->leftJoin('sets', 'catalog_prints.set_id', '=', 'sets.id')
                                                ->whereIn('catalog_prints.concept_id', $conceptIds)
                                                ->orderBy('catalog_prints.id', 'desc')->get()->groupBy('concept_id');
            } else {
                $setIds = $cartas->pluck('set_id')->unique()->toArray();
                $collectorNumbers = $cartas->pluck('collector_number')->unique()->toArray();
                $printsRelacionados = CatalogPrint::whereIn('set_id', $setIds)->whereIn('collector_number', $collectorNumbers)
                                                ->whereIn('language_code', ['pt', 'PT', 'pt-br', 'pt-BR'])
                                                ->get()->groupBy(fn($item) => $item->set_id . '_' . $item->collector_number);
            }
        }

        $cartas->getCollection()->transform(function ($carta) use ($printsRelacionados) {
            $total = (int) ($carta->total_estoque ?? 0);
            $precoBase = (float) ($carta->menor_preco ?? 0);
            $ultimoPreco = (float) ($carta->ultimo_preco ?? 0);
            $percentualDesconto = (float) ($carta->menor_preco_desconto ?? 0);

            $refCalc = ($total > 0) ? $precoBase : $ultimoPreco;
            $carta->preco_final = ($percentualDesconto > 0) ? $refCalc * (1 - ($percentualDesconto / 100)) : $refCalc;
            $carta->total_estoque = $total; $carta->menor_preco = $precoBase; $carta->desconto = $percentualDesconto;
            $carta->is_foil = (bool) str_contains(strtolower($carta->menor_preco_extras ?? ''), 'foil');
            
            static $artistIndexesCache = [];

            if (!$this->desagrupar) {
                $prints = $printsRelacionados->get($carta->concept_id, collect());
                if ($carta->virtual_number !== "") $prints = $prints->where('collector_number', $carta->virtual_number);
                
                $printPt = $prints->first(fn($p) => in_array(strtolower($p->language_code), ['pt', 'pt-br']) && !empty(trim($p->printed_name)));
                $isBasicLand = str_contains($carta->type_line, 'Basic Land');
                $englishName = $carta->name; 
                
                $printVencedor = null;
                if ($total > 0 && !empty($carta->print_id_in_stock)) $printVencedor = $prints->firstWhere('id', $carta->print_id_in_stock);
                elseif ($total === 0 && $ultimoPreco > 0 && !empty($carta->print_id_out_stock)) $printVencedor = $prints->firstWhere('id', $carta->print_id_out_stock);
                if (!$printVencedor) $printVencedor = $prints->first(fn($p) => strtolower($p->language_code) === 'en') ?? $prints->first();

                $isVariantSet = in_array(strtoupper($printVencedor?->set_code ?? ''), ['FEM', 'ALL', 'HML']);
                $hasLetterInNumber = preg_match('/[a-zA-Z]/', $carta->virtual_number);
                $isArtVariant = $isVariantSet && $hasLetterInNumber && !$isBasicLand;

                if ($isArtVariant && !empty($printVencedor?->artist)) {
                    $cid = $carta->concept_id;
                    $sid = $carta->set_id; // ISOLAMENTO POR SET
                    $cacheKey = $cid . '_' . $sid;
                    $nomeArtistaBase = trim($printVencedor->artist);
                    
                    if (!isset($artistIndexesCache[$cacheKey])) {
                        $artistIndexesCache[$cacheKey] = [];
                        $siblings = \Illuminate\Support\Facades\DB::table('catalog_prints')
                            ->join('mtg_prints', 'catalog_prints.specific_id', '=', 'mtg_prints.id')
                            ->where('catalog_prints.concept_id', $cid)->where('catalog_prints.set_id', $sid)->where('catalog_prints.collector_number', 'REGEXP', '[a-zA-Z]')
                            ->select('catalog_prints.collector_number', 'mtg_prints.artist')->orderBy('catalog_prints.collector_number', 'asc')->get();
                        foreach($siblings as $sib) {
                            $art = trim($sib->artist ?: 'Artista Desconhecido');
                            $cNum = strtolower(trim($sib->collector_number));
                            if(!isset($artistIndexesCache[$cacheKey][$art])) $artistIndexesCache[$cacheKey][$art] = [];
                            if (!in_array($cNum, $artistIndexesCache[$cacheKey][$art])) {
                                $artistIndexesCache[$cacheKey][$art][] = $cNum;
                            }
                        }
                    }
                    
                    $nomeArtistaFinal = $nomeArtistaBase;
                    if (isset($artistIndexesCache[$cacheKey][$nomeArtistaBase]) && count($artistIndexesCache[$cacheKey][$nomeArtistaBase]) > 1) {
                        $idx = array_search(strtolower(trim($carta->virtual_number)), $artistIndexesCache[$cacheKey][$nomeArtistaBase]);
                        if ($idx !== false) $nomeArtistaFinal .= ' ' . ($idx + 1);
                    }

                    $carta->nome_localizado = ($printPt?->printed_name ?? $englishName) . ' (' . $nomeArtistaFinal . ')';
                    $carta->name = sprintf('%s (%s) • #%s', $englishName, $nomeArtistaFinal, $carta->virtual_number);
                    $carta->slug_seguro = \Str::slug($englishName . '-' . $nomeArtistaFinal);
                } elseif ($isBasicLand && $carta->virtual_number !== "") {
                    $carta->nome_localizado = ($printPt?->printed_name ?? $englishName) . ' #' . $carta->virtual_number;
                    $carta->name = $englishName . ' #' . $carta->virtual_number;
                    $tiposBasicos = ['Plains', 'Island', 'Swamp', 'Mountain', 'Forest'];
                    $tipoEncontrado = collect($tiposBasicos)->first(fn($t) => str_contains($carta->type_line, $t));
                    $carta->slug_seguro = \Str::slug($tipoEncontrado ?: $englishName) . '-' . $carta->virtual_number;
                } else {
                    $carta->nome_localizado = $printPt?->printed_name ?? $englishName;
                    $carta->name = $englishName;
                    $carta->slug_seguro = $carta->slug ?? 'card-id-' . $carta->concept_id;
                }
                $imagemBruta = $printVencedor?->image_url ?? $printVencedor?->image_path;
                if (empty($imagemBruta)) $imagemBruta = 'https://placehold.co/250x350/eeeeee/999999?text=Sem+Imagem';
                
            } else {
                // MODO DESAGRUPADO
                $key = $carta->set_id . '_' . $carta->collector_number;
                $printPt = $printsRelacionados->get($key, collect())->first(fn($p) => !empty(trim($p->printed_name)));

                $isVariantSet = in_array(strtoupper($carta->set_code ?? ''), ['FEM', 'ALL', 'HML']);
                $hasLetterInNumber = preg_match('/[a-zA-Z]/', $carta->collector_number);
                $isBasicLand = str_contains($carta->type_line, 'Basic Land');
                $isArtVariant = $isVariantSet && $hasLetterInNumber && !$isBasicLand;

                $baseNameLocal = !empty($carta->printed_name) ? $carta->printed_name : $carta->concept?->name;
                $englishName = $carta->concept?->name ?? '---';

                if ($isArtVariant && !empty($carta->artist)) {
                    $cid = $carta->concept_id;
                    $sid = $carta->set_id; // ISOLAMENTO POR SET
                    $cacheKey = $cid . '_' . $sid;
                    $nomeArtistaBase = trim($carta->artist);

                    if (!isset($artistIndexesCache[$cacheKey])) {
                        $artistIndexesCache[$cacheKey] = [];
                        $siblings = \Illuminate\Support\Facades\DB::table('catalog_prints')
                            ->join('mtg_prints', 'catalog_prints.specific_id', '=', 'mtg_prints.id')
                            ->where('catalog_prints.concept_id', $cid)->where('catalog_prints.set_id', $sid)->where('catalog_prints.collector_number', 'REGEXP', '[a-zA-Z]')
                            ->select('catalog_prints.collector_number', 'mtg_prints.artist')->orderBy('catalog_prints.collector_number', 'asc')->get();
                        foreach($siblings as $sib) {
                            $art = trim($sib->artist ?: 'Artista Desconhecido');
                            $cNum = strtolower(trim($sib->collector_number));
                            if(!isset($artistIndexesCache[$cacheKey][$art])) $artistIndexesCache[$cacheKey][$art] = [];
                            if (!in_array($cNum, $artistIndexesCache[$cacheKey][$art])) {
                                $artistIndexesCache[$cacheKey][$art][] = $cNum;
                            }
                        }
                    }
                    
                    $nomeArtistaFinal = $nomeArtistaBase;
                    if (isset($artistIndexesCache[$cacheKey][$nomeArtistaBase]) && count($artistIndexesCache[$cacheKey][$nomeArtistaBase]) > 1) {
                        $idx = array_search(strtolower(trim($carta->collector_number)), $artistIndexesCache[$cacheKey][$nomeArtistaBase]);
                        if ($idx !== false) $nomeArtistaFinal .= ' ' . ($idx + 1);
                    }

                    $carta->nome_localizado = $baseNameLocal . ' (' . $nomeArtistaFinal . ')';
                    $carta->name = sprintf('%s (%s) • #%s', $englishName, $nomeArtistaFinal, $carta->collector_number);
                    $carta->slug_seguro = \Str::slug($englishName . '-' . $nomeArtistaFinal);
                } elseif ($isBasicLand) {
                    $carta->nome_localizado = $baseNameLocal;
                    $carta->name = $printPt?->printed_name ?? $englishName;
                    $tiposBasicos = ['Plains', 'Island', 'Swamp', 'Mountain', 'Forest'];
                    $tipoEncontrado = collect($tiposBasicos)->first(fn($t) => str_contains($carta->type_line, $t));
                    $carta->slug_seguro = \Str::slug($tipoEncontrado ?: $englishName) . '-' . $carta->collector_number;
                } else {
                    $carta->nome_localizado = $baseNameLocal;
                    $carta->name = $printPt?->printed_name ?? $englishName;
                    $slugDb = $carta->concept?->slug ?? \Str::slug($englishName);
                    $carta->slug_seguro = !empty($slugDb) ? $slugDb : 'card-id-' . $carta->id;
                }
                $imagemBruta = $carta->image_url ?? $carta->image_path ?? $carta->concept?->image_url ?? $carta->concept?->image_path ?? 'https://placehold.co/250x350/eeeeee/999999?text=Sem+Imagem';
            }

            $carta->imagem_final = filter_var($imagemBruta, FILTER_VALIDATE_URL) ? $imagemBruta : asset($imagemBruta);
            $carta->foil = false; 
            $carta->is_concept = !$this->desagrupar;

            return $carta;
        });

        return view('livewire.store.template.catalog.single-page', ['cartas' => $cartas])->layout('layouts.template', ['loja' => $this->loja]);
    }
}