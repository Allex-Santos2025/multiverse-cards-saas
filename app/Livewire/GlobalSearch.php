<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\Game;
use App\Models\Store;
use App\Models\StockItem;
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

        // Prints com estoque real incluindo extras e discount_percent
        $printsReais = CatalogPrint::select(
                'catalog_prints.*',
                'stock_items.quantity as stock_qty',
                'stock_items.price as stock_price',
                'stock_items.extras as stock_extras',
                'stock_items.discount_percent as stock_desconto'
            )
            ->join('stock_items', 'catalog_prints.id', '=', 'stock_items.catalog_print_id')
            ->where('stock_items.store_id', $storeId)
            ->where('stock_items.quantity', '>', 0)
            ->whereIn('catalog_prints.concept_id', $conceptIds)
            ->when($numberFilter, fn($q) => $q->where('catalog_prints.collector_number', $numberFilter))
            ->with(['concept', 'set'])
            ->get();

        $groupedPrints = [];
        foreach ($printsReais as $p) {
            $vNum = stripos($p->type_line ?? '', 'Basic Land') !== false
                ? $p->collector_number
                : '';
            $groupedPrints[$p->concept_id][$vNum][] = $p;
        }

        $estoqueResults = [];
        $globalResults  = [];

        foreach ($hits as $hit) {
            $cId = $hit['id'];

            // TEM ESTOQUE
            if (isset($groupedPrints[$cId])) {
                foreach ($groupedPrints[$cId] as $vNum => $printsArray) {
                    $printsCol   = collect($printsArray);
                    $firstPrint  = $printsCol->first();
                    $concept     = $firstPrint->concept;
                    $isBasicLand = stripos($firstPrint->type_line ?? '', 'Basic Land') !== false;

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

                    if ($isBasicLand && $vNum !== '') {
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

                    // Foil / Etched / Desconto — pega do item de menor preço
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

            // SEM ESTOQUE — só lojista
            } elseif ($this->isLojista) {
                $nomeEn      = $hit['name'] ?? '';
                $nomePt      = $hit['name_pt'] ?? $nomeEn;
                $isBasicLand = preg_match('/^(Plains|Island|Swamp|Mountain|Forest)$/i', $nomeEn);

                if ($isBasicLand && $numberFilter) {
                    $nomeEn     .= ' #' . $numberFilter;
                    $nomePt     .= ' #' . $numberFilter;
                    $conceptSlug = Str::slug($hit['name']) . '-' . $numberFilter;

                    $printParaImagem = CatalogPrint::where('concept_id', $cId)
                        ->where('collector_number', $numberFilter)
                        ->where('language_code', 'en')
                        ->whereNotNull('image_path')
                        ->where('image_path', '!=', '')
                        ->orderByDesc('id')
                        ->first()
                        ?? CatalogPrint::where('concept_id', $cId)
                            ->where('collector_number', $numberFilter)
                            ->whereNotNull('image_path')
                            ->where('image_path', '!=', '')
                            ->orderByDesc('id')
                            ->first();
                } else {
                    $conceptSlug = $this->cleanSlug($hit['slug'] ?? Str::slug($nomeEn));

                    $printParaImagem = CatalogPrint::where('concept_id', $cId)
                        ->where('language_code', 'en')
                        ->whereNotNull('image_path')
                        ->where('image_path', '!=', '')
                        ->orderByDesc('id')
                        ->first()
                        ?? CatalogPrint::where('concept_id', $cId)
                            ->whereNotNull('image_path')
                            ->where('image_path', '!=', '')
                            ->orderByDesc('id')
                            ->first();
                }

                $imagemBruta = $printParaImagem?->image_path ?? null;
                $imagemFinal = $imagemBruta
                    ? (filter_var($imagemBruta, FILTER_VALIDATE_URL) ? $imagemBruta : asset($imagemBruta))
                    : 'https://placehold.co/250x350/eeeeee/999999?text=X';

                $globalResults[] = [
                    'status'          => 'ghost',
                    'name'            => $nomeEn,
                    'nome_localizado' => $nomePt,
                    'set_name'        => $printParaImagem?->set?->name,
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

    private function cleanSlug(string $slug): string
    {
        return preg_replace('/-[a-f0-9]{4}$/', '', $slug);
    }

    public function render()
    {
        return view('livewire.global-search');
    }
}