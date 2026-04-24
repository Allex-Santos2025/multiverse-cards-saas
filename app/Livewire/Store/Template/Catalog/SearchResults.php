<?php

namespace App\Livewire\Store\Template\Catalog;

use Livewire\Component;
use App\Models\Store;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\StockItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchResults extends Component
{
    public $slug, $gameSlug, $query;
    public $loja;
    public array $estoqueResults = [];
    public array $globalResults  = [];
    public bool  $isLojista      = false;

    public function mount($slug, $gameSlug)
    {
        $this->slug     = $slug;
        $this->gameSlug = $gameSlug;
        $this->loja     = Store::where('url_slug', $slug)->firstOrFail();
        $this->query    = request('q', '');

        $this->isLojista = auth('store_user')->check()
            && auth('store_user')->user()->store?->id === $this->loja->id;

        if (mb_strlen(trim($this->query)) >= 2) {
            $this->runSearch();
        }
    }

    private function runSearch(): void
    {
        $term         = trim($this->query);
        $numberFilter = null;

        if (preg_match('/^(.*?)(?:[\s|$$|#]+)(\d+)[$|]*$/', $term, $m)) {
            $termToSearch = trim($m[1]);
            $numberFilter = trim($m[2]);
        } else {
            $termToSearch = $term;
        }

        $raw  = CatalogConcept::search($termToSearch)->raw();
        $hits = collect($raw['hits'] ?? [])->keyBy('id');

        if ($hits->isEmpty() && $numberFilter) {
            $raw          = CatalogConcept::search($term)->raw();
            $hits         = collect($raw['hits'] ?? [])->keyBy('id');
            $numberFilter = null;
        }

        if ($hits->isEmpty()) return;

        $conceptIds           = $hits->keys()->all();
        
        // MICRO-CACHE DE ARTISTAS COM ISOLAMENTO POR CONCEITO + SET
        $artistIndexesCache = [];
        $siblings = DB::table('catalog_prints')
            ->join('mtg_prints', 'catalog_prints.specific_id', '=', 'mtg_prints.id')
            ->whereIn('catalog_prints.concept_id', $conceptIds)
            ->where('catalog_prints.collector_number', 'REGEXP', '[a-zA-Z]')
            ->select('catalog_prints.concept_id', 'catalog_prints.set_id', 'catalog_prints.collector_number', 'mtg_prints.artist')
            ->orderBy('catalog_prints.collector_number', 'asc')
            ->get();

        foreach($siblings as $sib) {
            $cacheKey = $sib->concept_id . '_' . $sib->set_id;
            $art = trim($sib->artist ?: 'Artista Desconhecido');
            $cNum = strtolower(trim($sib->collector_number));

            if(!isset($artistIndexesCache[$cacheKey][$art])) {
                $artistIndexesCache[$cacheKey][$art] = [];
            }
            
            if (!in_array($cNum, $artistIndexesCache[$cacheKey][$art])) {
                $artistIndexesCache[$cacheKey][$art][] = $cNum;
            }
        }
        
        $estoqueVirtualNumber = 'CASE 
            WHEN cp.type_line LIKE "%Basic Land%" THEN cp.collector_number 
            WHEN s_estoque.code IN ("FEM", "ALL", "HML") AND cp.collector_number REGEXP "[a-zA-Z]" THEN cp.collector_number 
            ELSE "" 
        END';

        $stocksRaw = StockItem::selectRaw("
                cp.concept_id,
                cp.set_id,
                MAX(s_estoque.code) as set_code,
                MAX(cp.type_line) as type_line,
                MAX({$estoqueVirtualNumber}) as virtual_number,
                SUM(stock_items.quantity) as total_estoque,
                MIN(CASE WHEN stock_items.quantity > 0 THEN stock_items.price END) as menor_preco,
                MAX(stock_items.price) as ultimo_preco,
                SUBSTRING_INDEX(GROUP_CONCAT(
                    CASE WHEN stock_items.quantity > 0 THEN stock_items.extras END
                    ORDER BY stock_items.price ASC SEPARATOR '|||'
                ), '|||', 1) as menor_preco_extras,
                SUBSTRING_INDEX(GROUP_CONCAT(
                    CASE WHEN stock_items.quantity > 0 THEN stock_items.discount_percent END
                    ORDER BY stock_items.price ASC SEPARATOR '|||'
                ), '|||', 1) as menor_preco_desconto,
                SUBSTRING_INDEX(GROUP_CONCAT(cp.id ORDER BY stock_items.price ASC SEPARATOR ','), ',', 1) as print_id_in_stock
            ")
            ->join('catalog_prints as cp', 'stock_items.catalog_print_id', '=', 'cp.id')
            ->join('sets as s_estoque', 'cp.set_id', '=', 's_estoque.id')
            ->where('stock_items.store_id', $this->loja->id)
            ->whereIn('cp.concept_id', $conceptIds)
            ->when($numberFilter, fn($q) => $q->where('cp.collector_number', $numberFilter))
            ->groupBy('cp.concept_id', 'cp.set_id', DB::raw($estoqueVirtualNumber))
            ->get();

        $stocksByConcept = $stocksRaw->groupBy('concept_id');

        $estoque = [];
        $global  = [];

        foreach ($hits as $hit) {
            $cId         = $hit['id'];
            $isBasicLand = preg_match('/(Plains|Island|Swamp|Mountain|Forest)/i', $hit['name'])
                || (isset($hit['type_line']) && stripos($hit['type_line'], 'Basic Land') !== false);

            $vNumsInStoreBySet = [];

            // 1. ESTOQUE REAL
            if ($stocksByConcept->has($cId)) {
                foreach ($stocksByConcept->get($cId) as $row) {
                    $vNum      = $row->virtual_number;
                    $sid       = $row->set_id;
                    $vNumsInStoreBySet[$sid][] = (string) $vNum;
                    
                    $printInfo = CatalogPrint::select('catalog_prints.*', 'mtg_prints.artist')
                                            ->leftJoin('mtg_prints', 'catalog_prints.specific_id', '=', 'mtg_prints.id')
                                            ->with('set')
                                            ->find($row->print_id_in_stock);

                    if (!$printInfo) continue;

                    $nomeEn = $hit['name'] ?? '';
                    $nomePt = $printInfo->printed_name ?? $hit['name_pt'] ?? $nomeEn;
                    
                    $isVariantSet = in_array(strtoupper($row->set_code), ['FEM', 'ALL', 'HML']);
                    $hasLetterInNumber = preg_match('/[a-zA-Z]/', $vNum);
                    $isArtVariant = $isVariantSet && $hasLetterInNumber && !$isBasicLand;

                    if ($isArtVariant && !empty($printInfo->artist)) {
                        $cacheKey = $cId . '_' . $sid;
                        $nomeArtistaBase = trim($printInfo->artist);
                        $nomeArtistaFinal = $nomeArtistaBase;

                        if (isset($artistIndexesCache[$cacheKey][$nomeArtistaBase]) && count($artistIndexesCache[$cacheKey][$nomeArtistaBase]) > 1) {
                            $idx = array_search(strtolower(trim($vNum)), $artistIndexesCache[$cacheKey][$nomeArtistaBase]);
                            if ($idx !== false) $nomeArtistaFinal .= ' ' . ($idx + 1);
                        }

                        $nomeEn .= ' (' . $nomeArtistaFinal . ')';
                        $nomePt .= ' (' . $nomeArtistaFinal . ')';
                        $conceptSlug = Str::slug($hit['name'] . '-' . $nomeArtistaFinal);
                    } elseif ($isBasicLand && $vNum !== '') {
                        $nomeEn     .= ' #' . $vNum;
                        $nomePt     .= ' #' . $vNum;
                        $conceptSlug = Str::slug($hit['name']) . '-' . $vNum;
                    } else {
                        $conceptSlug = $this->cleanSlug($hit['slug'] ?? Str::slug($nomeEn));
                    }

                    $imagemFinal = $printInfo->image_path
                        ? (filter_var($printInfo->image_path, FILTER_VALIDATE_URL) ? $printInfo->image_path : asset($printInfo->image_path))
                        : 'https://placehold.co/250x350/eeeeee/999999?text=X';

                    $extrasStr  = strtolower($row->menor_preco_extras ?? '');
                    $isEtched   = str_contains($extrasStr, 'etched');
                    $isFoil     = str_contains($extrasStr, 'foil') && !$isEtched;
                    $desconto   = (float) ($row->menor_preco_desconto ?? 0);
                    $precoBase  = (float) ($row->menor_preco ?? 0);
                    $precoFinal = $desconto > 0 ? $precoBase * (1 - ($desconto / 100)) : $precoBase;

                    $estoque[] = [
                        'nome_localizado' => $nomePt,
                        'name'            => $nomeEn,
                        'set_name'        => $printInfo->set?->name,
                        'imagem_final'    => $imagemFinal,
                        'total_estoque'   => (int) $row->total_estoque,
                        'menor_preco'     => $precoBase,
                        'ultimo_preco'    => (float) ($row->ultimo_preco ?? 0),
                        'preco_final'     => $precoFinal,
                        'desconto'        => $desconto,
                        'is_foil'         => $isFoil,
                        'is_etched'       => $isEtched,
                        'status'          => $row->total_estoque > 0 ? 'available' : 'out_of_stock',
                        'url'             => route('store.catalog.product', [
                            'slug'        => $this->slug,
                            'gameSlug'    => $this->gameSlug,
                            'conceptSlug' => $conceptSlug,
                        ]),
                    ];
                }
            }

            // 2. FANTASMAS
            if ($this->isLojista) {
                if (!$isBasicLand) {
                    $prints = CatalogPrint::select('catalog_prints.*', 'mtg_prints.artist', 'sets.code as set_code')
                                            ->leftJoin('mtg_prints', 'catalog_prints.specific_id', '=', 'mtg_prints.id')
                                            ->join('sets', 'catalog_prints.set_id', '=', 'sets.id')
                                            ->where('concept_id', $cId)->get();
                    
                    $printsAgrupadosParaFantasma = [];
                    foreach($prints as $p) {
                        $isVar = in_array(strtoupper($p->set_code), ['FEM', 'ALL', 'HML']) && preg_match('/[a-zA-Z]/', $p->collector_number);
                        $vId = $isVar ? $p->collector_number : 'default';
                        $printsAgrupadosParaFantasma[$p->set_id][$vId][] = $p;
                    }
                    
                    foreach($printsAgrupadosParaFantasma as $sidFantasma => $variants) {
                        foreach($variants as $vNumFantasma => $printsDoFantasma) {
                            $compareId = $vNumFantasma !== 'default' ? (string)$vNumFantasma : '';
                            
                            $inEstoque = isset($vNumsInStoreBySet[$sidFantasma]) && in_array($compareId, $vNumsInStoreBySet[$sidFantasma]);
                            
                            if (!$inEstoque) {
                                $global[] = $this->generateGhostData($hit, collect($printsDoFantasma), ($vNumFantasma !== 'default' ? $vNumFantasma : null), $artistIndexesCache, $sidFantasma);
                            }
                        }
                    }
                } else {
                    $allNumbersBySet = CatalogPrint::where('concept_id', $cId)
                        ->when($numberFilter, fn($q) => $q->where('collector_number', $numberFilter))
                        ->select('collector_number', 'set_id')
                        ->get()
                        ->groupBy('set_id');

                    foreach ($allNumbersBySet as $sidFantasma => $numbers) {
                        foreach ($numbers->pluck('collector_number')->unique() as $num) {
                            $inEstoque = isset($vNumsInStoreBySet[$sidFantasma]) && in_array((string)$num, $vNumsInStoreBySet[$sidFantasma]);
                            
                            if (!$inEstoque) {
                                $printsDesteNumero = CatalogPrint::where('concept_id', $cId)
                                    ->where('set_id', $sidFantasma)
                                    ->where('collector_number', $num)
                                    ->get();
                                $global[] = $this->generateGhostData($hit, $printsDesteNumero, (string) $num, $artistIndexesCache, $sidFantasma);
                            }
                        }
                    }
                }
            }
        }

        $this->estoqueResults = collect($estoque)
            ->sortByDesc(fn($i) => $i['status'] === 'available' ? 1 : 0)
            ->values()->all();

        $this->globalResults = collect($global)->values()->all();
    }

    private function generateGhostData($hit, $prints, $vNum = null, $artistCache = [], $sid = null): array
    {
        $nomeEn   = $hit['name'] ?? '';
        $printEn  = $prints->filter(fn($p) => strtolower($p->language_code) === 'en' && !empty($p->image_path))->sortByDesc('id')->first();
        $printImg = $printEn
            ?? $prints->filter(fn($p) => !empty($p->image_path))->sortByDesc('id')->first()
            ?? $prints->first();
        $printPt = $prints->filter(fn($p) =>
            in_array(strtolower($p->language_code), ['pt', 'pt-br', 'pt_br']) &&
            !empty(trim($p->printed_name ?? ''))
        )->sortByDesc('id')->first();
        $nomePt = $printPt->printed_name ?? $hit['name_pt'] ?? $nomeEn;

        $isVariantSet = in_array(strtoupper($printImg?->set_code ?? $printImg?->set?->code ?? ''), ['FEM', 'ALL', 'HML']);
        $hasLetterInNumber = preg_match('/[a-zA-Z]/', $vNum ?? '');
        $isArtVariant = $isVariantSet && $hasLetterInNumber;

        if ($isArtVariant && !empty($printImg?->artist)) {
            $cacheKey = ($hit['id'] ?? null) . '_' . $sid;
            $nomeArtistaBase = trim($printImg->artist);
            $nomeArtistaFinal = $nomeArtistaBase;

            if ($sid && isset($artistCache[$cacheKey][$nomeArtistaBase]) && count($artistCache[$cacheKey][$nomeArtistaBase]) > 1) {
                $idx = array_search(strtolower(trim($vNum ?? '')), $artistCache[$cacheKey][$nomeArtistaBase]);
                if ($idx !== false) {
                    $nomeArtistaFinal .= ' ' . ($idx + 1);
                }
            }

            $displayEn   = "$nomeEn (" . $nomeArtistaFinal . ")";
            $displayPt   = "$nomePt (" . $nomeArtistaFinal . ")";
            $conceptSlug = Str::slug($hit['name'] . '-' . $nomeArtistaFinal);
        } elseif ($vNum && !$isVariantSet) { 
            $displayEn   = "$nomeEn #$vNum";
            $displayPt   = "$nomePt #$vNum";
            $conceptSlug = Str::slug($hit['name']) . '-' . $vNum;
        } else {
            $displayEn   = $nomeEn;
            $displayPt   = $nomePt;
            $conceptSlug = $this->cleanSlug($hit['slug'] ?? Str::slug($nomeEn));
        }

        $imagemFinal = $printImg && $printImg->image_path
            ? (filter_var($printImg->image_path, FILTER_VALIDATE_URL) ? $printImg->image_path : asset($printImg->image_path))
            : 'https://placehold.co/250x350/eeeeee/999999?text=X';

        return [
            'status'          => 'ghost',
            'name'            => $displayEn,
            'nome_localizado' => $displayPt,
            'set_name'        => ($printImg && $printImg->set) ? $printImg->set->name : null,
            'imagem_final'    => $imagemFinal,
            'is_foil'         => false,
            'is_etched'       => false,
            'desconto'        => 0,
            'preco_final'     => 0,
            'menor_preco'     => 0,
            'ultimo_preco'    => 0,
            'total_estoque'   => 0,
            'url'             => route('store.catalog.product', [
                'slug'        => $this->slug,
                'gameSlug'    => $this->gameSlug,
                'conceptSlug' => $conceptSlug,
            ]),
        ];
    }

    private function cleanSlug(string $slug): string
    {
        return preg_replace('/-[a-f0-9]{4}$/', '', $slug);
    }

    public function render()
    {
        return view('livewire.store.template.catalog.search-results')
            ->layout('layouts.template', ['loja' => $this->loja]);
    }
}