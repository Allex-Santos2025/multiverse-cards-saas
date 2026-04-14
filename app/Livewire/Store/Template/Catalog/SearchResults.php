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
    public $exactResults   = [];
    public $relatedResults = [];

    public function mount($slug, $gameSlug)
    {
        $this->slug     = $slug;
        $this->gameSlug = $gameSlug;
        $this->loja     = Store::where('url_slug', $slug)->firstOrFail();
        $this->query    = request('q', '');

        if (mb_strlen(trim($this->query)) >= 2) {
            $this->runSearch();
        }
    }

    private function runSearch(): void
    {
        $term = trim($this->query);
        
        // Regex Inteligente: Isola números se o cliente buscar "#292" ou "(292)"
        $numberFilter = null;
        if (preg_match('/^(.*?)(?:[\s\(\)#]+)(\d+)[\)]*$/', $term, $m)) {
            $termToSearch = trim($m[1]);
            $numberFilter = trim($m[2]);
        } else {
            $termToSearch = $term;
        }

        $raw  = CatalogConcept::search($termToSearch)->raw();
        $hits = collect($raw['hits'] ?? [])->keyBy('id');

        if ($hits->isEmpty() && $numberFilter) {
            $raw = CatalogConcept::search($term)->raw();
            $hits = collect($raw['hits'] ?? [])->keyBy('id');
            $numberFilter = null;
        }

        if ($hits->isEmpty()) return;

        $conceptIds = $hits->keys()->all();
        
        // A MESMA MÁGICA DA SINGLE PAGE: Separa os Terrenos e junta as Cartas Comuns
        $estoqueVirtualNumber = 'CASE WHEN cp.type_line LIKE "%Basic Land%" THEN cp.collector_number ELSE "" END';

        // 1. Puxa O ESTOQUE REAL agrupado corretamente
        $stocksRaw = StockItem::selectRaw("
                cp.concept_id,
                MAX(cp.type_line) as type_line,
                $estoqueVirtualNumber as virtual_number,
                MIN(stock_items.price) as menor_preco,
                MAX(stock_items.price) as ultimo_preco,
                SUM(stock_items.quantity) as total_estoque,
                SUBSTRING_INDEX(GROUP_CONCAT(cp.id ORDER BY stock_items.price ASC SEPARATOR ','), ',', 1) as print_id_in_stock
            ")
            ->join('catalog_prints as cp', 'stock_items.catalog_print_id', '=', 'cp.id')
            ->where('stock_items.store_id', $this->loja->id)
            ->whereIn('cp.concept_id', $conceptIds)
            ->where('stock_items.quantity', '>', 0) // IGNORA TUDO QUE NÃO TEM ESTOQUE!
            ->when($numberFilter, fn($q) => $q->where('cp.collector_number', $numberFilter))
            ->groupBy('cp.concept_id', DB::raw($estoqueVirtualNumber))
            ->get();

        // 2. Traz os Prints para as imagens e traduções
        $printsFisicos = CatalogPrint::whereIn('concept_id', $conceptIds)->get();

        $termNormalized = mb_strtolower($termToSearch);
        $exact   = [];
        $related = [];

        // 3. Monta os Cards apenas pro que existe no Banco da Loja
        foreach ($stocksRaw as $row) {
            $hit = $hits->get($row->concept_id);
            if (!$hit) continue;

            $isBasicLand = stripos($row->type_line, 'Basic Land') !== false;
            $vNum = $row->virtual_number;
            
            // Puxa as traduções do bloco específico
            $printsDoConceito = $printsFisicos->where('concept_id', $row->concept_id);
            if ($isBasicLand && $vNum !== "") {
                $printsDoConceito = $printsDoConceito->where('collector_number', $vNum);
            }

            $printPt = $printsDoConceito->first(fn($p) => in_array(strtolower($p->language_code), ['pt', 'pt-br']) && !empty(trim($p->printed_name)));
            $printImg = $printsFisicos->where('id', $row->print_id_in_stock)->first() ?? $printsDoConceito->first();
            
            $nomeEn = $hit['name'] ?? '';
            $nomePt = $printPt->printed_name ?? $hit['name_pt'] ?? $nomeEn;
            
            if ($isBasicLand && $vNum !== "") {
                $nomeEn .= ' #' . $vNum;
                $nomePt .= ' #' . $vNum;
                
                $tiposBasicos = ['Plains', 'Island', 'Swamp', 'Mountain', 'Forest'];
                $tipoEncontrado = null;
                foreach ($tiposBasicos as $tipo) {
                    if (stripos($row->type_line, $tipo) !== false) {
                        $tipoEncontrado = $tipo; break;
                    }
                }
                $conceptSlug = Str::slug($tipoEncontrado ?: $hit['name']) . '-' . $vNum;
            } else {
                $conceptSlug = $this->cleanSlug($hit['slug']);
            }
            
            $imagemBruta = $printImg->image_url ?? $printImg->image_path ?? null;
            $imagemFinal = $imagemBruta ? (filter_var($imagemBruta, FILTER_VALIDATE_URL) ? $imagemBruta : asset($imagemBruta)) : 'https://placehold.co/250x350/eeeeee/999999?text=Sem+Imagem';

            $item = [
                'nome_localizado' => $nomePt,
                'name'            => $nomeEn,
                'set_name'        => null,
                'imagem_final'    => $imagemFinal,
                'total_estoque'   => $row->total_estoque,
                'menor_preco'     => $row->menor_preco,
                'ultimo_preco'    => $row->ultimo_preco,
                'status'          => 'available', // Tudo que passa agora tem estoque
                'url'             => route('store.catalog.product', [
                    'slug'        => $this->slug,
                    'gameSlug'    => $this->gameSlug,
                    'conceptSlug' => $conceptSlug,
                ]),
            ];

            $namePtCheck = mb_strtolower($hit['name_pt'] ?? '');
            $nameEnCheck = mb_strtolower($hit['name'] ?? '');
            if ($namePtCheck === $termNormalized || $nameEnCheck === $termNormalized) {
                $exact[] = $item;
            } else {
                $related[] = $item;
            }
        }

        $this->exactResults   = $exact;
        $this->relatedResults = $related;
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