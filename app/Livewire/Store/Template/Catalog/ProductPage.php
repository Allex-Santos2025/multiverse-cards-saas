<?php

namespace App\Livewire\Store\Template\Catalog;

use Livewire\Component;
use App\Models\Store;
use App\Models\Game;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\StockItem; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // <-- Importado para o Str::slug na Blade se você for gerar as rotas aqui

class ProductPage extends Component
{
    public $slug, $gameSlug, $conceptSlug;
    public $loja, $game, $concept, $nomeLocalizado;
    public $allPrints, $priceStats, $activePrintId, $activeStockId, $activeImage;
     
    public $displayList = []; 
    public $stockByPrint; 

    public $cardDetails;

    public function mount($slug, $gameSlug, $conceptSlug)
    {
        $this->slug = $slug;
        $this->gameSlug = $gameSlug;
        $this->conceptSlug = $conceptSlug;

        $this->loja = Store::where('url_slug', $slug)->firstOrFail();
        
        $this->concept = CatalogConcept::where(function($q) use ($conceptSlug) {
            $q->where('slug', $conceptSlug)
              ->orWhere('slug', 'like', $conceptSlug . '-%');
        })->firstOrFail();

        $printPt = CatalogPrint::where('concept_id', $this->concept->id)
            ->whereIn('language_code', ['pt', 'pt-br', 'pt-BR'])
            ->whereNotNull('printed_name')
            ->where('printed_name', '!=', '')
            ->first();
        $this->nomeLocalizado = $printPt ? $printPt->printed_name : null;

        $estoqueBruto = StockItem::with(['catalogPrint', 'catalogPrint.set'])
            ->where('store_id', $this->loja->id)
            ->whereHas('catalogPrint', fn($q) => $q->where('concept_id', $this->concept->id))
            ->get();

        $this->stockByPrint = $estoqueBruto->groupBy('catalog_print_id')
            ->map(fn($group) => collect($group->all()))
            ->toBase();

        $list = collect();
        $printsToLoad = [];
        $setsWithStock = [];

        // 1. CARREGA O ESTOQUE REAL (Estados 1 e 2)
        if ($estoqueBruto->isNotEmpty()) {
            foreach ($estoqueBruto as $stock) {
                $print = $stock->catalogPrint;
                $printsToLoad[] = $print->id;
                $setsWithStock[] = $print->set_id;

                $rawLang = strtolower($print->language_code ?? 'en');
                $langFixed = (str_contains($rawLang, 'pt')) ? 'pt' : $rawLang;

                $list->push([
                    'print_id' => $print->id,
                    'stock_id' => $stock->id,
                    'has_stock' => $stock->quantity > 0 ? 1 : 0,
                    'had_stock' => 1,
                    'stock_qty' => $stock->quantity ?? 0,
                    'stock_price' => $stock->final_price ?? $stock->price ?? 999999,
                    'release_date' => $print->set->released_at ?? '1900-01-01',
                    'language' => $langFixed, 
                    'condition' => $stock->condition,
                    'extras' => $stock->extras, 
                ]);
            }
        }

        // 2. CARREGA OS FANTASMAS (Estado 3: Avise-me)
        $queryFantasmas = CatalogPrint::with('set')
            ->where('concept_id', $this->concept->id);
            
        if (!empty($setsWithStock)) {
            $queryFantasmas->whereNotIn('set_id', array_unique($setsWithStock)); // Pula os que já têm estoque
        }
        
        $fantasmas = $queryFantasmas->get()->groupBy('set_id');

        foreach ($fantasmas as $setId => $printsDoSet) {
            // Pega o print base em inglês, ou a língua que tiver (se for um set 100% japonês, por exemplo)
            $printBase = $printsDoSet->firstWhere('language_code', 'en') ?? $printsDoSet->first();

            if ($printBase) {
                $printsToLoad[] = $printBase->id;
                $list->push([
                    'print_id' => $printBase->id,
                    'stock_id' => null, // Dispara o "@else" na Blade (Estado 3)
                    'has_stock' => 0,
                    'had_stock' => 0,
                    'stock_qty' => 0,
                    'stock_price' => 999999,
                    'release_date' => $printBase->set->released_at ?? '1900-01-01',
                    'language' => 'en',
                    'condition' => null,
                    'extras' => null,
                ]);
            }
        }

        $this->allPrints = CatalogPrint::with('set')->whereIn('id', array_unique($printsToLoad))->get()->keyBy('id');

        // Ordena: Com Estoque > Sem Estoque (Esgotado) > Fantasmas
        $this->displayList = $list->sort(function ($a, $b) {
            // 1. DISPONIBILIDADE (Peso: Estoque = 3 | Esgotado = 2 | Fantasma = 1)
            $statusA = $a['has_stock'] ? 3 : ($a['had_stock'] ? 2 : 1);
            $statusB = $b['has_stock'] ? 3 : ($b['had_stock'] ? 2 : 1);

            if ($statusA !== $statusB) {
                return $statusB <=> $statusA; // Maior status no topo
            }

            // 2. VALOR (Maior preço no topo absoluto dentro do mesmo status)
            // Aqui a Foil de 2,00 ganha da Normal de 0,50!
            if ($a['stock_price'] !== $b['stock_price']) {
                return $b['stock_price'] <=> $a['stock_price']; 
            }

            // 3. DATA DE LANÇAMENTO (Mais recentes primeiro como desempate)
            if ($a['release_date'] !== $b['release_date']) {
                return $b['release_date'] <=> $a['release_date'];
            }

            // 4. QUANTIDADE (Maior estoque primeiro)
            return $b['stock_qty'] <=> $a['stock_qty'];
        })->values()->toArray();

        if (!empty($this->displayList)) {
            $firstPrintId = $this->displayList[0]['print_id'];
            $this->updateStats($firstPrintId);
        }
    }

    public function updateStats($printId, $stockId = null)
    {
        $this->activePrintId = $printId;
        $this->activeStockId = $stockId;
        $cotacaoDolar = 5.50; // Sua cotação base

        $currentPrint = $this->allPrints->where('id', $printId)->first();
        if (!$currentPrint) return;

        $this->activeImage = asset($currentPrint->image_path);
        $this->cardDetails = DB::table('mtg_prints')->where('id', $currentPrint->specific_id)->first();

        // =========================================================================
        // 1. DETETIVE DE TRATAMENTO (Foil / Etched / Normal)
        // =========================================================================
        $isFoil = false;
        $isEtched = false;

        if ($stockId) {
            $stockItem = $this->stockByPrint->get($printId)->where('id', $stockId)->first();
            
            if ($stockItem && !empty($stockItem->extras)) {
                $extrasStr = is_array($stockItem->extras) ? implode(' ', $stockItem->extras) : (string) $stockItem->extras;
                $extrasStr = strtolower($extrasStr);

                $isEtched = str_contains($extrasStr, 'etched');
                $isFoil = !$isEtched && str_contains($extrasStr, 'foil'); 
            }
        }

        // =========================================================================
        // 2. PREÇOS DA LOJA ISOLADOS (Para o Min, Max e possível Fallback)
        // =========================================================================
        $siblingPrintIds = CatalogPrint::where('set_id', $currentPrint->set_id)
            ->where('collector_number', $currentPrint->collector_number)
            ->pluck('id');

        $allStockForThisPrint = StockItem::whereIn('catalog_print_id', $siblingPrintIds)
            ->where('store_id', $this->loja->id)
            ->get();

        // Filtra o estoque da loja isolando o tratamento correto
        $prices = $allStockForThisPrint->filter(function($item) use ($isFoil, $isEtched) {
            $extrasStr = !empty($item->extras) ? (is_array($item->extras) ? implode(' ', $item->extras) : (string) $item->extras) : '';
            $extrasStr = strtolower($extrasStr);
            
            $itemIsEtched = str_contains($extrasStr, 'etched');
            $itemIsFoil = !$itemIsEtched && str_contains($extrasStr, 'foil');
            
            return ($itemIsEtched === $isEtched) && ($itemIsFoil === $isFoil);
        })->map(fn($item) => $item->final_price ?? $item->price)->filter();

        // =========================================================================
        // 3. PREÇO MÉDIO MUNDIAL (JSON Ingest) + FALLBACK VERSUS TCG
        // =========================================================================
        $pricePrintId = $currentPrint->specific_id;
        
        // Se a carta não for em inglês, tentamos achar a equivalente em inglês para cotar
        if ($currentPrint->language_code !== 'en') {
            $englishEquivalent = CatalogPrint::where('concept_id', $currentPrint->concept_id)
                ->where('set_id', $currentPrint->set_id)
                ->where('language_code', 'en')
                ->first();
            if ($englishEquivalent) $pricePrintId = $englishEquivalent->specific_id;
        }

        $precoMedioBrl = 0;
        $mtgData = DB::table('mtg_prints')->where('id', $pricePrintId)->first();
        
        if ($mtgData && !empty($mtgData->prices)) {
            $pricesArray = is_string($mtgData->prices) ? json_decode($mtgData->prices, true) : (array)$mtgData->prices;
            if (is_array($pricesArray)) {
                
                // Tenta puxar o preço EXATO do tratamento sem misturar
                if ($isEtched) {
                    $usd = $pricesArray['usd_etched'] ?? null;
                } elseif ($isFoil) {
                    $usd = $pricesArray['usd_foil'] ?? null;
                } else {
                    $usd = $pricesArray['usd'] ?? null;
                }
                
                if ($usd > 0) {
                    $precoMedioBrl = (float)$usd * $cotacaoDolar;
                }
            }
        }

        // A REGRA DE OURO: Fallback Interno se o JSON estiver null/0
        if ($precoMedioBrl == 0 && $prices->isNotEmpty()) {
            $precoMedioBrl = (float) $prices->avg();
        }

        $this->priceStats = [
            'min' => $prices->min() ?? 0,
            'max' => $prices->max() ?? 0,
            'avg' => $precoMedioBrl, 
        ];
    }

    public function render()
    {
        if (!$this->cardDetails) {
            $this->cardDetails = DB::table('mtg_prints')->where('id', $this->concept->specific_id)->first();
        }

        // =====================================================================
        // CARDS ASSOCIADOS (Cross-selling na mesma coleção da carta ativa)
        // =====================================================================
        
        $currentSetId = null;
        $printAtivo = null;
        $cardsAssociados = collect();
        $totalAssociados = 0;

        if ($this->activePrintId) {
            $printAtivo = $this->allPrints->where('id', $this->activePrintId)->first();
            $currentSetId = $printAtivo ? $printAtivo->set_id : null;
        }

        if ($currentSetId) {
            // 1. A Base: O que tem na loja desta edição
            $estoqueValido = StockItem::select('cp.concept_id')
                ->join('catalog_prints as cp', 'stock_items.catalog_print_id', '=', 'cp.id')
                ->where('stock_items.store_id', $this->loja->id)
                ->where('cp.set_id', $currentSetId)
                ->where('cp.concept_id', '!=', $this->concept->id)
                ->where('stock_items.quantity', '>', 0)
                ->distinct();

            $totalAssociados = DB::table(DB::raw("({$estoqueValido->toSql()}) as sub"))
                ->mergeBindings($estoqueValido->getQuery())
                ->count();

            // 2. Os "Elegitos": Pegar o Print Oficial de cada Concept encontrado
            $printsBase = CatalogPrint::selectRaw('MIN(id) as print_id, concept_id')
                ->where('set_id', $currentSetId)
                ->where('language_code', 'en')
                ->groupBy('concept_id');

            // 3. O Resumo do Estoque
            $estoqueAgrupado = StockItem::select(
                    'cp.concept_id',
                    DB::raw('SUM(stock_items.quantity) as total_estoque'),
                    DB::raw('MIN(stock_items.price) as menor_preco'),
                    DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(stock_items.extras ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_extras"),
                    DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(stock_items.discount_percent ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_desconto")
                )
                ->join('catalog_prints as cp', 'stock_items.catalog_print_id', '=', 'cp.id')
                ->where('stock_items.store_id', $this->loja->id)
                ->where('cp.set_id', $currentSetId)
                ->where('stock_items.quantity', '>', 0)
                ->groupBy('cp.concept_id');

            // ====================================================================
            // 3.5. A MÁGICA DA TRADUÇÃO: Busca global por PT em toda a história
            // ====================================================================
            $nomePtSubquery = DB::table('catalog_prints as cp_pt')
                ->select('cp_pt.printed_name')
                ->whereColumn('cp_pt.concept_id', 'catalog_prints.concept_id')
                ->whereIn('cp_pt.language_code', ['pt', 'pt-br', 'pt-BR'])
                ->whereNotNull('cp_pt.printed_name')
                ->where('cp_pt.printed_name', '!=', '')
                ->limit(1);

            // 4. A Query Final
            $cardsRaw = CatalogPrint::select(
                    'catalog_prints.*',
                    'estoque.total_estoque',
                    'estoque.menor_preco',
                    'estoque.menor_preco_extras',
                    'estoque.menor_preco_desconto'
                )
                ->selectSub($nomePtSubquery, 'nome_pt_banco') // <-- Injeta o nome em PT aqui
                ->joinSub($estoqueValido, 'validos', function($join) {
                     $join->on('catalog_prints.concept_id', '=', 'validos.concept_id');
                })
                ->joinSub($printsBase, 'base', function($join) {
                     $join->on('catalog_prints.id', '=', 'base.print_id');
                })
                ->joinSub($estoqueAgrupado, 'estoque', function ($join) {
                    $join->on('catalog_prints.concept_id', '=', 'estoque.concept_id');
                })
                ->with(['concept'])
                ->inRandomOrder()
                ->limit(10)
                ->get();

            // 5. Formata os dados
            $cardsAssociados = $cardsRaw->map(function ($carta) {
                $precoBase = (float) ($carta->menor_preco ?? 0);
                $percentualDesconto = (float) ($carta->menor_preco_desconto ?? 0);
            
                $carta->preco_final = ($percentualDesconto > 0) 
                    ? $precoBase * (1 - ($percentualDesconto / 100))
                    : $precoBase;
                    
                $carta->is_foil = (bool) str_contains(strtolower($carta->menor_preco_extras ?? ''), 'foil');
                
                // A REGRA APLICADA: Pega a tradução que achou, se não achar, cai pro inglês
                $carta->nome_localizado = $carta->nome_pt_banco ?? $carta->concept->name ?? $carta->printed_name; 
                
                // Mantém sempre o inglês original na segunda linha
                $carta->name = $carta->concept->name ?? $carta->printed_name;
                
                $imagemBruta = $carta->image_url ?? $carta->image_path ?? $carta->concept->image_url ?? $carta->concept->image_path ?? 'https://placehold.co/250x350/eeeeee/999999?text=Sem+Imagem';
                $carta->imagem_final = filter_var($imagemBruta, FILTER_VALIDATE_URL) ? $imagemBruta : asset($imagemBruta);
                
                return $carta;
            });
        }

        return view('livewire.store.template.catalog.product-page', [
            'gameDetails' => $this->cardDetails,
            'cardsAssociados' => $cardsAssociados,
            'totalAssociados' => $totalAssociados,
            'printAtivo' => $printAtivo
        ])->layout('layouts.template', ['loja' => $this->loja]);
    }
}