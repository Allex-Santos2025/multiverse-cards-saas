<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\Game;
use App\Models\Store;
use App\Models\StockItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GlobalSearch extends Component
{
    public string $query     = '';
    public array  $results   = [];
    public string $storeSlug = '';
    public bool   $isLojista = false;

    public function mount(string $storeSlug, bool $isLojista = false): void
    {
        $this->storeSlug = $storeSlug;
        $this->isLojista = $isLojista;
    }

    public function updatedQuery(): void
    {
        $term = trim($this->query);
        if (mb_strlen($term) < 2) {
            $this->results = [];
            return;
        }
        $this->loadResults($term);
    }

    public function search(): void
    {
        $term = trim($this->query);
        if (mb_strlen($term) < 2) return;

        $this->redirect(
            route('store.catalog.search', [
                'slug'     => $this->storeSlug,
                'gameSlug' => 'magic',
            ]) . '?q=' . urlencode($term)
        );
    }

    private function loadResults(string $term): void
    {
        $numberFilter = null;
        if (preg_match('/^(.*?)(?:[\s|$$|#]+)(\d+)[$|]*$/', $term, $m)) {
            $termToSearch = trim($m[1]);
            $numberFilter = trim($m[2]);
        } else {
            $termToSearch = $term;
        }

        $raw  = CatalogConcept::search($termToSearch)->raw();
        $hits = collect($raw['hits'] ?? []);

        if ($hits->isEmpty() && $numberFilter) {
            $raw          = CatalogConcept::search($term)->raw();
            $hits         = collect($raw['hits'] ?? []);
            $numberFilter = null;
        }

        if ($hits->isEmpty()) {
            $this->results = [];
            return;
        }

        $conceptIds = $hits->pluck('id')->all();
        $storeId    = Store::where('url_slug', $this->storeSlug)->value('id');
        $games      = Game::whereIn('id', $hits->pluck('game_id')->unique())
                          ->pluck('url_slug', 'id');

        // MICRO-CACHE DE ARTISTAS COM ISOLAMENTO POR SET
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

        $printsReais = CatalogPrint::select(
                'catalog_prints.*',
                'stock_items.quantity as stock_qty',
                'stock_items.price as stock_price',
                'stock_items.extras as stock_extras',
                'stock_items.discount_percent as stock_desconto',
                'mtg_prints.artist',
                'sets.code as set_code'
            )
            ->join('stock_items', 'catalog_prints.id', '=', 'stock_items.catalog_print_id')
            ->leftJoin('mtg_prints', 'catalog_prints.specific_id', '=', 'mtg_prints.id')
            ->join('sets', 'catalog_prints.set_id', '=', 'sets.id')
            ->where('stock_items.store_id', $storeId)
            ->where('stock_items.quantity', '>', 0)
            ->whereIn('catalog_prints.concept_id', $conceptIds)
            ->when($numberFilter, fn($q) => $q->where('catalog_prints.collector_number', $numberFilter))
            ->with(['concept', 'set'])
            ->get();

        $groupedPrints = [];
        foreach ($printsReais as $p) {
            $isBasicLand = stripos($p->type_line ?? '', 'Basic Land') !== false;
            $isVariantSet = in_array(strtoupper($p->set_code), ['FEM', 'ALL', 'HML']);
            $hasLetterInNumber = preg_match('/[a-zA-Z]/', $p->collector_number);
            
            if ($isBasicLand || ($isVariantSet && $hasLetterInNumber)) {
                $vNum = $p->collector_number;
            } else {
                $vNum = '';
            }
            $groupedPrints[$p->concept_id][$vNum][] = $p;
        }

        $estoqueResults = [];
        $globalResults  = [];

        foreach ($hits as $hit) {
            $cId = $hit['id'];
            $vNumsInStoreBySet = [];

            // 1. TEM ESTOQUE
            if (isset($groupedPrints[$cId])) {
                foreach ($groupedPrints[$cId] as $vNum => $printsArray) {
                    $printsCol   = collect($printsArray);
                    $firstPrint  = $printsCol->first();
                    $sid         = $firstPrint->set_id;
                    $vNumsInStoreBySet[$sid][] = (string) $vNum;

                    $concept     = $firstPrint->concept;
                    $isBasicLand = stripos($firstPrint->type_line ?? '', 'Basic Land') !== false;
                    
                    $isVariantSet = in_array(strtoupper($firstPrint->set_code), ['FEM', 'ALL', 'HML']);
                    $hasLetterInNumber = preg_match('/[a-zA-Z]/', $vNum);
                    $isArtVariant = $isVariantSet && $hasLetterInNumber && !$isBasicLand;

                    $printPt = $printsCol->first(fn($p) =>
                        in_array(strtolower($p->language_code), ['pt', 'pt-br', 'pt_br']) &&
                        !empty(trim($p->printed_name ?? ''))
                    );

                    $nomeEn = $concept->name ?? '';
                    $nomePt = $printPt->printed_name ?? ($hit['name_pt'] ?? $nomeEn);

                    $printImg    = $printsCol->first(fn($p) => !empty($p->image_url) || !empty($p->image_path)) ?? $firstPrint;
                    $imagemBruta = $printImg->image_url ?? $printImg->image_path ?? null;
                    $imagemFinal = $imagemBruta
                        ? (filter_var($imagemBruta, FILTER_VALIDATE_URL) ? $imagemBruta : asset($imagemBruta))
                        : 'https://placehold.co/250x350/eeeeee/999999?text=X';

                    if ($isArtVariant && !empty($firstPrint->artist)) {
                        $cacheKey = $cId . '_' . $sid;
                        $nomeArtistaBase = trim($firstPrint->artist);
                        $nomeArtistaFinal = $nomeArtistaBase;

                        if (isset($artistIndexesCache[$cacheKey][$nomeArtistaBase]) && count($artistIndexesCache[$cacheKey][$nomeArtistaBase]) > 1) {
                            $idx = array_search(strtolower(trim($vNum)), $artistIndexesCache[$cacheKey][$nomeArtistaBase]);
                            if ($idx !== false) $nomeArtistaFinal .= ' ' . ($idx + 1);
                        }

                        $nomeEn .= ' (' . $nomeArtistaFinal . ')';
                        $nomePt .= ' (' . $nomeArtistaFinal . ')';
                        $conceptSlug = Str::slug(($concept->name ?? $nomeEn) . '-' . $nomeArtistaFinal);
                    } elseif ($isBasicLand && $vNum !== '') {
                        $nomeEn .= ' #' . $vNum;
                        $nomePt .= ' #' . $vNum;
                        $tiposBasicos   = ['Plains', 'Island', 'Swamp', 'Mountain', 'Forest'];
                        $tipoEncontrado = null;
                        foreach ($tiposBasicos as $tipo) {
                            if (stripos($firstPrint->type_line, $tipo) !== false) {
                                $tipoEncontrado = $tipo;
                                break;
                            }
                        }
                        $conceptSlug = Str::slug($tipoEncontrado ?: $concept->name) . '-' . $vNum;
                    } else {
                        $conceptSlug = $this->cleanSlug($concept->slug ?? Str::slug($concept->name));
                    }

                    $menorPrecoItem = $printsCol->sortBy('stock_price')->first();
                    $extrasStr      = strtolower($menorPrecoItem->stock_extras ?? '');
                    $isEtched       = str_contains($extrasStr, 'etched');
                    $isFoil         = str_contains($extrasStr, 'foil') && !$isEtched;
                    $desconto       = (float) ($menorPrecoItem->stock_desconto ?? 0);
                    $precoBase      = (float) ($printsCol->min('stock_price') ?? 0);
                    $precoFinal     = $desconto > 0 ? $precoBase * (1 - ($desconto / 100)) : $precoBase;

                    $estoqueResults[] = [
                        'status'          => 'available',
                        'name'            => $nomeEn,
                        'nome_localizado' => $nomePt,
                        'set_name'        => $firstPrint->set?->name,
                        'imagem_final'    => $imagemFinal,
                        'total_estoque'   => $printsCol->sum('stock_qty'),
                        'menor_preco'     => $precoBase,
                        'preco_final'     => $precoFinal,
                        'desconto'        => $desconto,
                        'is_foil'         => $isFoil,
                        'is_etched'       => $isEtched,
                        'url'             => route('store.catalog.product', [
                            'slug'        => $this->storeSlug,
                            'gameSlug'    => $games[$concept->game_id] ?? 'magic',
                            'conceptSlug' => $conceptSlug,
                        ]),
                    ];
                }
            }

            // 2. FANTASMAS (SÓ LOJISTA)
            if ($this->isLojista) {
                $isBasicLand = preg_match('/^(Plains|Island|Swamp|Mountain|Forest)$/i', $hit['name'] ?? '');

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
                                 $globalResults[] = $this->generateGhostData($hit, collect($printsDoFantasma), ($vNumFantasma !== 'default' ? $vNumFantasma : null), $games, $artistIndexesCache, $sidFantasma);
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
                                $globalResults[] = $this->generateGhostData($hit, $printsDesteNumero, (string) $num, $games, $artistIndexesCache, $sidFantasma);
                            }
                        }
                    }
                }
            }
        }

        $termNormalized = mb_strtolower($termToSearch ?? $term);

        $estoqueSorted = collect($estoqueResults)->unique('url')
            ->sortByDesc(fn($item) =>
                str_contains(mb_strtolower($item['nome_localizado']), $termNormalized) ||
                str_contains(mb_strtolower($item['name']), $termNormalized) ? 1 : 0
            )->values()->all();

        $globalSorted = collect($globalResults)->unique('url')
            ->sortByDesc(fn($item) =>
                str_contains(mb_strtolower($item['nome_localizado']), $termNormalized) ||
                str_contains(mb_strtolower($item['name']), $termNormalized) ? 1 : 0
            )->values()->all();

        $this->results = collect($estoqueSorted)->merge($globalSorted)->take(8)->all();
    }

    private function generateGhostData($hit, $prints, $vNum = null, $games = [], $artistCache = [], $sid = null): array
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
                if ($idx !== false) $nomeArtistaFinal .= ' ' . ($idx + 1);
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
            $conceptSlug = preg_replace('/-[a-f0-9]{4}$/', '', $hit['slug'] ?? Str::slug($nomeEn));
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
            'url'             => route('store.catalog.product', [
                'slug'        => $this->storeSlug,
                'gameSlug'    => $games[$hit['game_id']] ?? 'magic',
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
        return view('livewire.global-search');
    }
}