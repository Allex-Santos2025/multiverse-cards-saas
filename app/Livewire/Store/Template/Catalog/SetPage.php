<?php

namespace App\Livewire\Store\Template\Catalog;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Store;
use App\Models\Game;
use App\Models\Set;
use App\Models\Catalog\CatalogPrint;
use App\Models\Catalog\CatalogConcept;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SetPage extends Component
{
    use WithPagination;

    public $slug, $gameSlug, $setCode, $loja, $game, $set;

    #[Url(except: 'number_asc', history: true)]
    public $sortOrder = 'number_asc';

    #[Url(except: 'todas', history: true)]
    public $raridade = 'todas';

    #[Url(except: 'todas', history: true)]
    public $cor = 'todas';

    #[Url(except: 30, history: true)]
    public $perPage = 30;

    #[Url(except: false, history: true)]
    public $com_estoque = false;

    public function mount($slug, $gameSlug, $setCode)
    {
        $this->slug = $slug;
        $this->loja = Store::with('visual')->where('url_slug', $slug)->firstOrFail();
        $this->gameSlug = $gameSlug;
        $this->game = Game::where('url_slug', $gameSlug)->firstOrFail();
        $this->setCode = $setCode;
        $this->set = Set::where('game_id', $this->game->id)->where('code', $setCode)->firstOrFail();
    }

    public function updated($propertyName) { $this->resetPage(); }

    public function render()
    {
        $lojaTrabalhaComOSet = \App\Models\StockItem::where('store_id', $this->loja->id)
            ->whereHas('catalogPrint', fn($q) => $q->where('set_id', $this->set->id))->exists();

        if (!$lojaTrabalhaComOSet) {
            $cartas = collect([]); 
        } else {
            // --- LÓGICA DE FALLBACK DE IDIOMA (EN -> PT -> OUTROS) ---
            $langsInSet = \App\Models\Catalog\CatalogPrint::where('set_id', $this->set->id)
                ->distinct()->pluck('language_code')->toArray();

            $mainLanguage = 'en';
            if (!in_array('en', $langsInSet)) {
                // Se não tem inglês, busca qualquer variante de português
                $ptLang = collect($langsInSet)->first(fn($l) => in_array(strtolower($l), ['pt', 'pt-br', 'pt-br', 'pt_br']));
                $mainLanguage = $ptLang ?: ($langsInSet[0] ?? 'en');
            }

            $queryIds = \App\Models\Catalog\CatalogPrint::where('set_id', $this->set->id)
                ->where('language_code', $mainLanguage)
                ->where('printed_name', 'NOT LIKE', 'A-%');

            if ($this->set->card_count > 0) {
                $queryIds->whereRaw('CAST(collector_number AS UNSIGNED) <= ?', [$this->set->card_count]);
            }

            if ($this->cor !== 'todas') {
                if ($this->cor === 'A') {
                    $queryIds->where('type_line', 'LIKE', '%Artifact%');
                } elseif ($this->cor === 'L') {
                    $queryIds->where('type_line', 'LIKE', '%Land%');
                } else {
                    $queryIds->join('catalog_concepts as cc', 'catalog_prints.concept_id', '=', 'cc.id')
                             ->join('mtg_concepts as mc', 'cc.specific_id', '=', 'mc.id');

                    if (in_array($this->cor, ['W', 'U', 'B', 'R', 'G'])) {
                        $queryIds->where('mc.colors', 'LIKE', '%"' . $this->cor . '"%')->where('mc.colors', 'NOT LIKE', '%,%');
                    } elseif ($this->cor === 'M') {
                        $queryIds->where('mc.colors', 'LIKE', '%,%');
                    } elseif ($this->cor === 'C') {
                        $queryIds->where(fn($q) => $q->whereNull('mc.colors')->orWhere('mc.colors', '[]')->orWhere('mc.colors', ''))
                        ->where('catalog_prints.type_line', 'NOT LIKE', '%Artifact%')
                        ->where('catalog_prints.type_line', 'NOT LIKE', '%Land%');
                    }
                }
            }

            $printIds = $queryIds->pluck('catalog_prints.id');

            $estoqueSubquery = \App\Models\StockItem::select(
                'cp.collector_number', 
                \DB::raw('SUM(stock_items.quantity) as total_estoque'),
                \DB::raw('MIN(CASE WHEN stock_items.quantity > 0 THEN stock_items.price END) as menor_preco'),
                \DB::raw('MIN(stock_items.price) as ultimo_preco'),
                \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.extras END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_extras"),
                \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.discount_percent END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_desconto")
            )
            ->join('catalog_prints as cp', 'stock_items.catalog_print_id', '=', 'cp.id')
            ->where('stock_items.store_id', $this->loja->id)
            ->where('cp.set_id', $this->set->id)
            ->groupBy('cp.collector_number');

            $query = \App\Models\Catalog\CatalogPrint::select(
                    'catalog_prints.*',
                    \DB::raw('COALESCE(estoque.total_estoque, 0) as total_estoque'),
                    'estoque.menor_preco', 'estoque.ultimo_preco', 'estoque.menor_preco_extras', 'estoque.menor_preco_desconto',
                    'mtg.artist' 
                )
                ->leftJoin('mtg_prints as mtg', 'catalog_prints.specific_id', '=', 'mtg.id')
                ->with(['concept', 'concept.prints']) 
                ->whereIn('catalog_prints.id', $printIds)
                ->leftJoinSub($estoqueSubquery, 'estoque', fn($join) => $join->on('catalog_prints.collector_number', '=', 'estoque.collector_number'));

            if ($this->raridade !== 'todas') $query->where('catalog_prints.rarity', $this->raridade); 
            if ($this->com_estoque) $query->where('total_estoque', '>', 0);

            switch ($this->sortOrder) {
                case 'price_asc': $query->orderByRaw('estoque.menor_preco IS NULL')->orderBy('estoque.menor_preco', 'asc'); break;
                case 'price_desc': $query->orderByRaw('estoque.menor_preco IS NULL')->orderBy('estoque.menor_preco', 'desc'); break;
                case 'name_desc': $query->orderBy('catalog_prints.printed_name', 'desc'); break;
                case 'number_desc': $query->orderByRaw('CAST(catalog_prints.collector_number AS UNSIGNED) DESC'); break;
                case 'name_asc': $query->orderBy('catalog_prints.printed_name', 'asc'); break;
                case 'number_asc': default: $query->orderByRaw('CAST(catalog_prints.collector_number AS UNSIGNED) ASC'); break;
            }

            $setCodeForTransform = strtoupper($this->set->code);
            $cartas = $query->paginate($this->perPage)->onEachSide(0);

            $cartas->getCollection()->transform(function ($carta) use ($setCodeForTransform) {
                $total = (int) ($carta->total_estoque ?? 0);
                $precoBase = (float) ($carta->menor_preco ?? 0);
                $ultimoPreco = (float) ($carta->ultimo_preco ?? 0);
                $percentualDesconto = (float) ($carta->menor_preco_desconto ?? 0);

                $refCalc = ($total > 0) ? $precoBase : $ultimoPreco;
                $carta->preco_final = ($percentualDesconto > 0) ? $refCalc * (1 - ($percentualDesconto / 100)) : $refCalc;
                $carta->total_estoque = $total;
                $carta->menor_preco = $precoBase;
                $carta->desconto = $percentualDesconto;

                $extrasRaw = strtolower($carta->menor_preco_extras ?? '');
                $carta->is_etched = str_contains($extrasRaw, 'etched');
                $carta->is_foil   = str_contains($extrasRaw, 'foil') && !$carta->is_etched;

                $englishName = $carta->concept->name ?? $carta->printed_name;
                $globalPtName = null;

                if ($carta->concept && $carta->concept->relationLoaded('prints')) {
                    $globalPtName = $carta->concept->prints
                                    ->firstWhere(fn ($p) => in_array($p->language_code, ['pt', 'PT', 'pt-br', 'pt-BR']))
                                    ?->printed_name;
                }

                $isBasicLand = str_contains($carta->type_line, 'Basic Land');
                $isVariantSet = in_array($setCodeForTransform, ['FEM', 'ALL', 'HML']); 
                $hasLetterInNumber = preg_match('/[a-zA-Z]/', $carta->collector_number); 
                $isArtVariant = $isVariantSet && $hasLetterInNumber && !$isBasicLand;

                // --- APLICAÇÃO DO CONCEITO VIRTUAL (ISOLADO POR SET E ARTISTA) ---
                if ($isArtVariant && !empty($carta->artist)) {
                    static $artistIndexesCache = []; // RAM Cache
                    $cid = $carta->concept_id;
                    $sid = $carta->set_id; 
                    $cacheKey = $cid . '_' . $sid;
                    $nomeArtistaBase = trim($carta->artist);
                    
                    if (!isset($artistIndexesCache[$cacheKey])) {
                        $artistIndexesCache[$cacheKey] = [];
                        $siblings = \Illuminate\Support\Facades\DB::table('catalog_prints')
                            ->join('mtg_prints', 'catalog_prints.specific_id', '=', 'mtg_prints.id')
                            ->where('catalog_prints.concept_id', $cid)
                            ->where('catalog_prints.set_id', $sid) 
                            ->where('catalog_prints.collector_number', 'REGEXP', '[a-zA-Z]')
                            ->select('catalog_prints.collector_number', 'mtg_prints.artist')
                            ->orderBy('catalog_prints.collector_number', 'asc')
                            ->get();
                            
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

                    $carta->nome_localizado = ($globalPtName ?: $englishName) . ' (' . $nomeArtistaFinal . ')';
                    $carta->name = sprintf('%s (%s) • #%s', $englishName, $nomeArtistaFinal, $carta->collector_number);
                    $carta->concept_slug = \Str::slug($englishName . '-' . $nomeArtistaFinal);

                } else {
                    // CARTA NORMAL (Ou Terreno Básico)
                    $carta->nome_localizado = $globalPtName ?: $englishName;
                    $carta->name = sprintf('%s • #%s', $englishName, $carta->collector_number);

                    if ($isBasicLand) {
                        $tiposBasicos = ['Plains', 'Island', 'Swamp', 'Mountain', 'Forest'];
                        $tipoEncontrado = collect($tiposBasicos)->first(fn($t) => str_contains($carta->type_line, $t));
                        $carta->concept_slug = \Str::slug($tipoEncontrado ?: $englishName) . '-' . $carta->collector_number;
                    } else {
                        $carta->concept_slug = \Str::slug($englishName);
                    }
                }

                $imagemBruta = $carta->image_url ?? $carta->image_path ?? $carta->concept->image_url ?? $carta->concept->image_path ?? 'https://placehold.co/250x350/eeeeee/999999?text=Sem+Imagem';
                $carta->imagem_final = filter_var($imagemBruta, FILTER_VALIDATE_URL) ? $imagemBruta : asset($imagemBruta);
                $carta->foil = false; 

                return $carta;
            });
        }

        return view('livewire.store.template.catalog.set-page', ['cartas' => $cartas])->layout('layouts.template', ['loja' => $this->loja]);
    }
}