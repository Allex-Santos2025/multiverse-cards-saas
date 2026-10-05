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

        $params = ['q' => $term];
        if (!empty($this->storeSlug)) {
            $params['slug'] = $this->storeSlug;
        }

        $this->redirect(route('store.catalog.search', $params));
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
            $pres = \App\Services\GamePresenters\GamePresenterFactory::make($p->concept->game_id ?? 1);
            $vNum = $pres->getPrintVariantKey($p);
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
                    $presenter = \App\Services\GamePresenters\GamePresenterFactory::make($concept->game_id ?? 1);

                    $printPt = $printsCol->first(fn($p) =>
                        in_array(strtolower($p->language_code), ['pt', 'pt-br', 'pt_br']) &&
                        !empty(trim($p->printed_name ?? ''))
                    );

                    $nomeEnBase = $concept->name ?? '';
                    $nomePtBase = $printPt->printed_name ?? ($hit['name_pt'] ?? $nomeEnBase);

                    $isBasicLand = stripos($firstPrint->type_line ?? '', 'Basic Land') !== false;
                    $isVariantSet = in_array(strtoupper($firstPrint->set_code ?? ($firstPrint->set->code ?? '')), ['FEM', 'ALL', 'HML']);
                    $hasLetterInNumber = preg_match('/[a-zA-Z]/', (string)$vNum);
                    $isArtVariant = $isVariantSet && $hasLetterInNumber && !$isBasicLand;

                    $extraArtista = null;
                    if ($isArtVariant && !empty($firstPrint->artist)) {
                        $cacheKey = $cId . '_' . $sid;
                        $nomeArtistaBase = trim($firstPrint->artist);
                        $nomeArtistaFinal = $nomeArtistaBase;
                        if (isset($artistIndexesCache[$cacheKey][$nomeArtistaBase]) && count($artistIndexesCache[$cacheKey][$nomeArtistaBase]) > 1) {
                            $idx = array_search(strtolower(trim($vNum)), $artistIndexesCache[$cacheKey][$nomeArtistaBase]);
                            if ($idx !== false) $nomeArtistaFinal .= ' ' . ($idx + 1);
                        }
                        $extraArtista = $nomeArtistaFinal;
                    }

                    $titles = $presenter->buildDisplayTitles($nomeEnBase, $nomePtBase, $firstPrint, ($vNum !== '' ? $vNum : null), $extraArtista);
                    $nomeEn = $titles['en'];
                    $nomePt = $titles['pt'];
                    $conceptSlug = $presenter->buildProductSlug($concept, $firstPrint, ($vNum !== '' ? $vNum : null), $extraArtista);

                    $printImg = $printsCol->first(fn($p) => !empty($p->image_url) || !empty($p->image_path)) ?? $firstPrint;
                    $imagemBruta = $printImg->image_url ?? $printImg->image_path ?? null;
                    $imagemFinal = $imagemBruta
                        ? (filter_var($imagemBruta, FILTER_VALIDATE_URL) ? $imagemBruta : asset($imagemBruta))
                        : 'https://placehold.co/250x350/eeeeee/999999?text=X';

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
                $presenter = \App\Services\GamePresenters\GamePresenterFactory::make($hit['game_id'] ?? 1);
                $printsAgrupados = $presenter->groupGhostPrints($cId, $hit['name'] ?? null, $numberFilter);

                foreach ($printsAgrupados as $sidFantasma => $variants) {
                    foreach ($variants as $vNumFantasma => $printsDoFantasma) {
                        $compareId = $vNumFantasma !== 'default' ? (string)$vNumFantasma : '';
                        $inEstoque = isset($vNumsInStoreBySet[$sidFantasma]) && in_array($compareId, $vNumsInStoreBySet[$sidFantasma]);

                        if (!$inEstoque) {
                            $globalResults[] = $this->generateGhostData($hit, collect($printsDoFantasma), ($vNumFantasma !== 'default' ? $vNumFantasma : null), $games, $artistIndexesCache, $sidFantasma);
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

        $presenter = \App\Services\GamePresenters\GamePresenterFactory::make($hit['game_id'] ?? 1);
        $extraArtista = null;
        if ($isArtVariant && !empty($printImg?->artist)) {
            $cacheKey = ($hit['id'] ?? null) . '_' . $sid;
            $nomeArtistaBase = trim($printImg->artist);
            $nomeArtistaFinal = $nomeArtistaBase;
            if ($sid && isset($artistCache[$cacheKey][$nomeArtistaBase]) && count($artistCache[$cacheKey][$nomeArtistaBase]) > 1) {
                $idx = array_search(strtolower(trim($vNum ?? '')), $artistCache[$cacheKey][$nomeArtistaBase]);
                if ($idx !== false) $nomeArtistaFinal .= ' ' . ($idx + 1);
            }
            $extraArtista = $nomeArtistaFinal;
        }

        $titles = $presenter->buildDisplayTitles($nomeEn, $nomePt, $printImg, $vNum, $extraArtista);
        $displayEn = $titles['en'];
        $displayPt = $titles['pt'];
        $conceptSlug = $presenter->buildProductSlug($hit, $printImg, $vNum, $extraArtista);

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