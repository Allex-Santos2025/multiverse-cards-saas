<?php

namespace App\Livewire\Store\Template\Catalog;

use Livewire\Component;
use App\Models\Store;
use App\Models\Game;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\StockItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchResults extends Component
{
    public $slug, $gameSlug, $query;
    public $loja;
    public array $resultsByGame = [];
    public int $totalResultados = 0;
    public bool $isLojista = false;

    public function mount($slug = null, $gameSlug = null)
    {
        $this->slug = $slug ?? request('slug') ?? request()->route('slug');
        $this->gameSlug = $gameSlug ?? request('gameSlug') ?? request()->route('gameSlug');
        $this->query = request('q', '');

        if (!$this->slug) {
            $host = request()->getHost();
            $mainDomain = env('APP_URL_DOMAIN', 'versustcg.com.br');
            $this->loja = Store::where('custom_domain', $host)
                ->orWhere('custom_domain', str_replace('www.', '', $host))
                ->first();
            if ($this->loja) {
                $this->slug = $this->loja->url_slug;
            }
        }

        if (!$this->loja && $this->slug) {
            $this->loja = Store::where('url_slug', $this->slug)->first();
        }

        if (!$this->loja) {
            $this->loja = Store::firstOrFail();
            $this->slug = $this->loja->url_slug;
        }

        $this->isLojista = auth('store_user')->check()
            && auth('store_user')->user()->store?->id === $this->loja->id;

        if (mb_strlen(trim($this->query)) >= 2) {
            $this->runSearch();
        }
    }

    private function runSearch(): void
    {
        $term = trim($this->query);
        $numberFilter = null;

        if (preg_match('/^(.*?)(?:[\s|#]+)(\d+)[$|]*$/', $term, $m)) {
            $termToSearch = trim($m[1]);
            $numberFilter = trim($m[2]);
        } else {
            $termToSearch = $term;
        }

        $raw = CatalogConcept::search($termToSearch)->raw();
        $hits = collect($raw['hits'] ?? [])->keyBy('id');

        if ($hits->isEmpty() && $numberFilter) {
            $raw = CatalogConcept::search($term)->raw();
            $hits = collect($raw['hits'] ?? [])->keyBy('id');
            $numberFilter = null;
        }

        if ($hits->isEmpty()) {
            $this->resultsByGame = [];
            $this->totalResultados = 0;
            return;
        }

        $conceptIds = $hits->keys()->all();
        $games = Game::all()->keyBy('id');
        $gameSlugs = $games->mapWithKeys(fn($g) => [$g->id => $g->url_slug])->toArray();

        // 1. ESTOQUE DA LOJA
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

        $organized = [];
        $totalCount = 0;

        foreach ($hits as $hit) {
            $cId = $hit['id'];
            $gameId = $hit['game_id'] ?? 1;
            $gSlug = $gameSlugs[$gameId] ?? 'magic';
            $gName = $games[$gameId]->name ?? ucfirst($gSlug);

            if (!isset($organized[$gameId])) {
                $organized[$gameId] = [
                    'game_id'   => $gameId,
                    'game_slug' => $gSlug,
                    'game_name' => $gName,
                    'estoque'   => [],
                    'fantasmas' => [],
                ];
            }

            $isBasicLand = preg_match('/(Plains|Island|Swamp|Mountain|Forest)/i', $hit['name'])
                || (isset($hit['type_line']) && stripos($hit['type_line'], 'Basic Land') !== false);

            $vNumsInStoreBySet = [];

            // A. ITENS DE ESTOQUE (Visíveis a todos)
            if ($stocksByConcept->has($cId)) {
                foreach ($stocksByConcept->get($cId) as $row) {
                    $vNum = $row->virtual_number;
                    $sid = $row->set_id;
                    $vNumsInStoreBySet[$sid][] = (string) $vNum;

                    $printInfo = CatalogPrint::with('set')->find($row->print_id_in_stock);
                    if (!$printInfo) continue;

                    $nomeEn = $hit['name'] ?? '';
                    $nomePt = $printInfo->printed_name ?? $hit['name_pt'] ?? $nomeEn;

                    if ($isBasicLand && $vNum !== '') {
                        $nomeEn .= ' #' . $vNum;
                        $nomePt .= ' #' . $vNum;
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

                    $organized[$gameId]['estoque'][] = [
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
                            'gameSlug'    => $gSlug,
                            'conceptSlug' => $conceptSlug,
                        ]),
                    ];
                    $totalCount++;
                }
            }

            // B. FANTASMAS (Exclusivo para Lojistas)
            if ($this->isLojista) {
                $prints = CatalogPrint::with('set')
                    ->where('concept_id', $cId)
                    ->when($numberFilter, fn($q) => $q->where('collector_number', $numberFilter))
                    ->get();

                $printsBySet = $prints->groupBy('set_id');

                foreach ($printsBySet as $sidFantasma => $printsDoSet) {
                    $jaTemNoEstoque = isset($vNumsInStoreBySet[$sidFantasma]);

                    if (!$jaTemNoEstoque) {
                        $ghostItem = $this->generateGhostData($hit, $printsDoSet, null, $gSlug);
                        if ($ghostItem) {
                            $organized[$gameId]['fantasmas'][] = $ghostItem;
                            $totalCount++;
                        }
                    }
                }
            }
        }

        $this->resultsByGame = array_filter($organized, fn($g) => !empty($g['estoque']) || !empty($g['fantasmas']));
        $this->totalResultados = $totalCount;
    }

    private function generateGhostData($hit, $prints, $vNum = null, string $gSlug = 'magic'): ?array
    {
        $nomeEn = $hit['name'] ?? '';
        $printEn = $prints->filter(fn($p) => strtolower($p->language_code ?? '') === 'en' && !empty($p->image_path))->sortByDesc('id')->first();
        $printImg = $printEn
            ?? $prints->filter(fn($p) => !empty($p->image_path))->sortByDesc('id')->first()
            ?? $prints->first();

        $printPt = $prints->filter(fn($p) =>
            in_array(strtolower($p->language_code ?? ''), ['pt', 'pt-br', 'pt_br']) &&
            !empty(trim($p->printed_name ?? ''))
        )->sortByDesc('id')->first();

        $nomePt = $printPt->printed_name ?? $hit['name_pt'] ?? $nomeEn;
        if ($vNum) {
            $nomePt .= ' #' . $vNum;
            $nomeEn .= ' #' . $vNum;
        }

        $imagemFinal = $printImg && !empty($printImg->image_path)
            ? (filter_var($printImg->image_path, FILTER_VALIDATE_URL) ? $printImg->image_path : asset($printImg->image_path))
            : 'https://placehold.co/250x350/eeeeee/999999?text=X';

        $conceptSlug = $this->cleanSlug($hit['slug'] ?? Str::slug($hit['name'] ?? ''));

        return [
            'nome_localizado' => $nomePt,
            'name'            => $nomeEn,
            'set_name'        => $printImg?->set?->name ?? 'Coleção Global',
            'imagem_final'    => $imagemFinal,
            'status'          => 'ghost',
            'total_estoque'   => 0,
            'preco_final'     => 0,
            'menor_preco'     => 0,
            'url'             => route('store.catalog.product', [
                'slug'        => $this->slug,
                'gameSlug'    => $gSlug,
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
