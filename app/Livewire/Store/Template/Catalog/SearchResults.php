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
        $estoqueVirtualNumber = 'CASE WHEN cp.type_line LIKE "%Basic Land%" THEN cp.collector_number ELSE "" END';

        // Busca estoque real agrupado por conceito + set + virtual_number
        // Inclui extras e discount_percent para foil/etched/desconto
        $stocksRaw = StockItem::selectRaw("
                cp.concept_id,
                cp.set_id,
                MAX(cp.type_line) as type_line,
                {$estoqueVirtualNumber} as virtual_number,
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

            // ESTOQUE REAL (disponível e esgotado)
            if ($stocksByConcept->has($cId)) {
                foreach ($stocksByConcept->get($cId) as $row) {
                    $vNum      = $row->virtual_number;
                    $printInfo = CatalogPrint::with('set')->find($row->print_id_in_stock);

                    if (!$printInfo) continue;

                    $nomeEn = $hit['name'] ?? '';
                    $nomePt = $printInfo->printed_name ?? $hit['name_pt'] ?? $nomeEn;

                    if ($isBasicLand && $vNum !== '') {
                        $nomeEn     .= ' #' . $vNum;
                        $nomePt     .= ' #' . $vNum;
                        $conceptSlug = Str::slug($hit['name']) . '-' . $vNum;
                    } else {
                        $conceptSlug = $this->cleanSlug($hit['slug'] ?? Str::slug($nomeEn));
                    }

                    $imagemFinal = $printInfo->image_path
                        ? (filter_var($printInfo->image_path, FILTER_VALIDATE_URL) ? $printInfo->image_path : asset($printInfo->image_path))
                        : 'https://placehold.co/250x350/eeeeee/999999?text=X';

                    // Foil / Etched / Desconto
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

            // FANTASMAS — só para lojista
            if ($this->isLojista) {
                if (!$isBasicLand) {
                    if (!$stocksByConcept->has($cId)) {
                        $prints   = CatalogPrint::where('concept_id', $cId)->get();
                        $global[] = $this->generateGhostData($hit, $prints);
                    }
                } else {
                    $allNumbers = CatalogPrint::where('concept_id', $cId)
                        ->when($numberFilter, fn($q) => $q->where('collector_number', $numberFilter))
                        ->distinct()
                        ->pluck('collector_number');

                    $numbersInStore = $stocksByConcept->has($cId)
                        ? $stocksByConcept->get($cId)->pluck('virtual_number')->map(fn($n) => (string) $n)->all()
                        : [];

                    foreach ($allNumbers as $num) {
                        if (!in_array((string) $num, $numbersInStore)) {
                            $printsDesteNumero = CatalogPrint::where('concept_id', $cId)
                                ->where('collector_number', $num)
                                ->get();
                            $global[] = $this->generateGhostData($hit, $printsDesteNumero, (string) $num);
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

    private function generateGhostData($hit, $prints, $vNum = null): array
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

        if ($vNum) {
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
            // fantasmas não têm preço nem foil definido
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