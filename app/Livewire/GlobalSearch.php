<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\Game;
use App\Models\Store;
use Illuminate\Support\Str;

class GlobalSearch extends Component
{
    public string $query = '';
    public array $results = [];
    public string $storeSlug = '';

    public function mount(string $storeSlug): void
    {
        $this->storeSlug = $storeSlug;
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

        if (empty($this->results)) {
            $this->loadResults($term);
        }

        if (count($this->results) === 1) {
            $this->redirect($this->results[0]['url']);
            return;
        }

        $this->redirect(route('store.catalog.search', [
            'slug'     => $this->storeSlug,
            'gameSlug' => 'magic',
        ]) . '?q=' . urlencode($term));
    }

    private function loadResults(string $term): void
    {
        // 1. Extrai o número se o usuário digitar "Planície 292" ou "Planície #292"
        $numberFilter = null;
        if (preg_match('/^(.*?)(?:[\s\(\)#]+)(\d+)[\)]*$/', $term, $m)) {
            $termToSearch = trim($m[1]);
            $numberFilter = trim($m[2]);
        } else {
            $termToSearch = $term;
        }

        // 2. Acha os conceitos no Meilisearch
        $raw  = CatalogConcept::search($termToSearch)->raw();
        $hits = collect($raw['hits'] ?? []);

        if ($hits->isEmpty() && $numberFilter) {
            $raw = CatalogConcept::search($term)->raw();
            $hits = collect($raw['hits'] ?? []);
            $numberFilter = null;
        }

        if ($hits->isEmpty()) {
            $this->results = [];
            return;
        }

        $conceptIds = $hits->pluck('id')->all();
        $storeId = Store::where('url_slug', $this->storeSlug)->value('id');
        $games = Game::whereIn('id', $hits->pluck('game_id')->unique())->pluck('url_slug', 'id');

        // 3. A MÁGICA LIMPA: Busca direto os PRINTS que você TEM NO ESTOQUE!
        $printsReais = CatalogPrint::select('catalog_prints.*')
            ->join('stock_items', 'catalog_prints.id', '=', 'stock_items.catalog_print_id')
            ->where('stock_items.store_id', $storeId)
            ->where('stock_items.quantity', '>', 0) // Somente o que tem estoque real
            ->whereIn('catalog_prints.concept_id', $conceptIds)
            ->when($numberFilter, fn($q) => $q->where('catalog_prints.collector_number', $numberFilter))
            ->with('concept')
            ->get();

        // Agrupa pelo conceito e pelo número (se for terreno)
        $groupedPrints = [];
        foreach ($printsReais as $p) {
            $vNum = stripos($p->type_line ?? '', 'Basic Land') !== false ? $p->collector_number : "";
            $groupedPrints[$p->concept_id][$vNum][] = $p;
        }

        $expanded = [];

        foreach ($hits as $hit) {
            $cId = $hit['id'];
            if (!isset($groupedPrints[$cId])) continue; // Se não tem em estoque, ignora!

            foreach ($groupedPrints[$cId] as $vNum => $printsArray) {
                $printsCol = collect($printsArray);
                $printPt = $printsCol->first(fn($p) => in_array(strtolower($p->language_code), ['pt', 'pt-br', 'pt_br']) && !empty(trim($p->printed_name)));
                $firstPrint = $printsCol->first();
                $concept = $firstPrint->concept;
                
                $isBasicLand = stripos($firstPrint->type_line ?? '', 'Basic Land') !== false;

                $nomeEn = $concept->name ?? '';
                $nomePt = $printPt->printed_name ?? $hit['name_pt'] ?? $concept->name;

                // Geração Inteligente do Link
                if ($isBasicLand && $vNum !== "") {
                    $nomeEn .= ' #' . $vNum;
                    $nomePt .= ' #' . $vNum;
                    
                    $tiposBasicos = ['Plains', 'Island', 'Swamp', 'Mountain', 'Forest'];
                    $tipoEncontrado = null;
                    foreach ($tiposBasicos as $tipo) {
                        if (stripos($firstPrint->type_line, $tipo) !== false) {
                            $tipoEncontrado = $tipo; break;
                        }
                    }
                    $conceptSlug = Str::slug($tipoEncontrado ?: $concept->name) . '-' . $vNum;
                } else {
                    $conceptSlug = $this->cleanSlug($concept->slug ?? Str::slug($concept->name));
                }

                // Miniatura da Carta (Usa a imagem do primeiro print daquele grupo)
                $printImg = $printsCol->first(fn($p) => !empty($p->image_url) || !empty($p->image_path)) ?? $firstPrint;
                $imagemBruta = $printImg->image_url ?? $printImg->image_path ?? null;
                $imagemFinal = $imagemBruta ? (filter_var($imagemBruta, FILTER_VALIDATE_URL) ? $imagemBruta : asset($imagemBruta)) : 'https://placehold.co/250x350/eeeeee/999999?text=X';

                $expanded[] = [
                    'name'    => $nomeEn,
                    'name_pt' => $nomePt,
                    'imagem'  => $imagemFinal,
                    'url'     => route('store.catalog.product', [
                        'slug'        => $this->storeSlug,
                        'gameSlug'    => $games[$concept->game_id] ?? 'magic',
                        'conceptSlug' => $conceptSlug,
                    ]),
                ];
            }
        }

        // Ordena para exibir as correspondências exatas no topo
        $termNormalized = mb_strtolower($termToSearch);
        $uniqueExpanded = collect($expanded)->unique('url');
        
        $this->results = $uniqueExpanded->sortByDesc(function($item) use ($termNormalized) {
            $namePt = mb_strtolower($item['name_pt'] ?? '');
            $nameEn = mb_strtolower($item['name'] ?? '');
            return (str_contains($namePt, $termNormalized) || str_contains($nameEn, $termNormalized)) ? 1 : 0;
        })->take(8)->values()->all();
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