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
        $this->slug       = $slug;
        $this->gameSlug   = $gameSlug;
        $this->conceptSlug = $conceptSlug;

        $this->loja = Store::where('url_slug', $slug)->firstOrFail();
        $this->game = Game::where('url_slug', $gameSlug)->firstOrFail();

        // ---------------------------------------------------------
        // 1) DETECTA SE O SLUG É DE TERRENO BÁSICO: plains-292 etc.
        // ---------------------------------------------------------
        $isBasicLandSlug = preg_match(
            '/^(plains|island|swamp|mountain|forest)-(\d+)$/',
            $conceptSlug,
            $matches
        );

        $basicLandCollectorNumber = $isBasicLandSlug ? $matches[2] : null;

        if ($isBasicLandSlug) {
            // =====================================================
            // CASO 1: TERRENO BÁSICO COM NÚMERO
            // =====================================================
            $basicTypeSlug        = $matches[1];
            $collectorNumber      = $matches[2];
            $basicTypeMap = [
                'plains'   => 'Plains',
                'island'   => 'Island',
                'swamp'    => 'Swamp',
                'mountain' => 'Mountain',
                'forest'   => 'Forest',
            ];
            $englishBasicTypeName = $basicTypeMap[$basicTypeSlug] ?? null;

            if (!$englishBasicTypeName) {
                abort(404, 'Tipo de terreno básico inválido no slug.');
            }

            $prints = CatalogPrint::query()
                ->where('collector_number', $collectorNumber)
                ->where('type_line', 'LIKE', '%Basic Land%')
                ->where('type_line', 'LIKE', '%' . $englishBasicTypeName . '%')
                ->whereHas('set', function ($q) {
                    $q->where('game_id', $this->game->id);
                })
                ->with(['concept', 'set', 'concept.prints'])
                ->get();

            if ($prints->isEmpty()) {
                abort(404, 'Terreno básico não encontrado com este número.');
            }

            $this->prints = $prints;
            $baseConcept = $prints->first()->concept;
            $displayEnglishName = sprintf('%s (#%s)', $englishBasicTypeName, $collectorNumber);

            $globalPtName = $prints->first()->concept->prints
                ->firstWhere(fn ($print) => in_array($print->language_code, ['pt', 'PT', 'pt-br', 'pt-BR']))
                ?->printed_name;

            $displayPtName = $globalPtName
                ? sprintf('%s (#%s)', $globalPtName, $collectorNumber)
                : $displayEnglishName;

            $this->concept = (object)[
                'id'        => $baseConcept->id,
                'name'      => $displayEnglishName,
                'slug'      => $conceptSlug,
                'game'      => $this->game,
                'specific'  => $baseConcept->specific,
                'type_line' => $prints->first()->type_line,
            ];

            $activePrint = $prints->firstWhere('language_code', 'en') ?? $prints->first();
            $this->activePrintId = $activePrint->id;
            $this->activeImage   = asset($activePrint->image_path);
            $this->cardDetails   = DB::table('mtg_prints')->where('id', $activePrint->specific_id)->first();
            $this->nomeLocalizado = $displayPtName;

        } else {
            // =====================================================
            // CASO 2: CARTA NORMAL & VARIANTES DE ARTE (BLINDADO)
            // =====================================================
            
            // 1. TENTA CORRESPONDÊNCIA EXATA (Ignorando o sufixo de 4 caracteres gerado pelo sistema)
            // Usamos os 4 underlines '____' para garantir que pegamos exatamente o sufixo
            $conceptFound = CatalogConcept::where('game_id', $this->game->id)
                ->where('slug', 'like', $conceptSlug . '-____')
                ->with(['specific', 'prints', 'prints.set'])
                ->first();

            // 2. BUSCA PROGRESSIVA (Para variantes de arte tipo: combat-medic-artista)
            if (!$conceptFound) {
                $parts = explode('-', $conceptSlug);
                array_pop($parts); 
                
                while (count($parts) > 0) {
                    $testSlug = implode('-', $parts);
                    
                    $conceptFound = CatalogConcept::where('game_id', $this->game->id)
                        ->where('slug', 'like', $testSlug . '-____')
                        ->with(['specific', 'prints', 'prints.set'])
                        ->first();
                        
                    if ($conceptFound) break;
                    
                    array_pop($parts);
                }
            }

            if (!$conceptFound) {
                abort(404, 'Carta não encontrada no catálogo.');
            }

            $this->concept = $conceptFound;
            $allConceptPrints = $this->concept->prints;

            // 1. Puxando os artistas da matriz mtg_prints
            $specificIds = $allConceptPrints->pluck('specific_id')->filter()->unique();
            $mtgPrintsData = DB::table('mtg_prints')->whereIn('id', $specificIds)->get()->keyBy('id');

            // 2. Classificando cada print e gerando o seu slug virtual
            $variantCounts = [];
            foreach ($allConceptPrints as $print) {
                $printMtgData = $mtgPrintsData->get($print->specific_id);
                $art = $printMtgData->artist ?? 'Artista Desconhecido';
                $setCode = strtoupper($print->set->code ?? '');
                
                if (in_array($setCode, ['FEM', 'ALL', 'HML']) && preg_match('/[a-zA-Z]/', $print->collector_number)) {
                    if (!isset($variantCounts[$art])) $variantCounts[$art] = [];
                    $variantCounts[$art][] = $print->collector_number;
                }
            }
            
            foreach($variantCounts as $art => $nums) {
                sort($variantCounts[$art]);
            }

            foreach ($allConceptPrints as $print) {
                $printMtgData = $mtgPrintsData->get($print->specific_id);
                $rawArtist = $printMtgData->artist ?? 'Artista Desconhecido';
                $nomeArtista = $rawArtist;

                $englishName = $this->concept->name;
                $setCode = strtoupper($print->set->code ?? '');
                $isVariantSet = in_array($setCode, ['FEM', 'ALL', 'HML']);
                $hasLetterInNumber = preg_match('/[a-zA-Z]/', $print->collector_number);
                $isBasicLand = str_contains($print->type_line ?? '', 'Basic Land');

                if ($isVariantSet && $hasLetterInNumber && !$isBasicLand) {
                    if (isset($variantCounts[$rawArtist]) && count($variantCounts[$rawArtist]) > 1) {
                        $idx = array_search($print->collector_number, $variantCounts[$rawArtist]);
                        if ($idx !== false) {
                            $nomeArtista .= ' ' . ($idx + 1);
                        }
                    }
                    
                    $print->artist = $nomeArtista; 
                    $virtualSlug = Str::slug($englishName . '-' . $nomeArtista);
                    $print->is_art_variant = true;
                } else {
                    $print->artist = $rawArtist;
                    $virtualSlug = $this->conceptSlug; // Mantém a URL amigável que o usuário clicou
                    $print->is_art_variant = false;
                }
                
                $print->virtual_slug = $virtualSlug;
            }

            // 3. ISOLANDO O PRODUTO
            $matchingPrints = $allConceptPrints->filter(fn($p) => $p->virtual_slug === $conceptSlug);

            // 4. REDIRECIONAMENTO AUTOMÁTICO (Variantes de arte)
            if ($matchingPrints->isEmpty() && $allConceptPrints->isNotEmpty()) {
                $primeiroSlugValido = $allConceptPrints->first()->virtual_slug;
                
                $newUrl = preg_replace('/' . preg_quote($conceptSlug, '/') . '$/', $primeiroSlugValido, request()->url());
                return redirect()->to($newUrl);
            }

            $this->prints = $matchingPrints;

            // 5. Configurando o Título na Tela (PT/EN)
            $printPt = $this->prints->firstWhere(fn($p) => in_array($p->language_code, ['pt', 'pt-br', 'pt-BR']) && !empty($p->printed_name));
            $baseName = $printPt ? $printPt->printed_name : $this->concept->name;

            $primeiroPrint = $this->prints->first();
            if ($primeiroPrint && $primeiroPrint->is_art_variant) {
                $this->nomeLocalizado = $baseName . ' (' . $primeiroPrint->artist . ')';
            } else {
                $this->nomeLocalizado = $baseName;
            }

            // Define a print ativa (preferência EN)
            $activePrint = $this->prints->firstWhere('language_code', 'en') ?? $this->prints->first();
            $this->activePrintId = $activePrint->id;
            $this->activeImage   = asset($activePrint->image_path);
            $this->cardDetails   = $mtgPrintsData->get($activePrint->specific_id);
        }

        // ============================================================
        // 2) ESTOQUE E LISTAGEM (COMUM PARA TERRENOS E CARTAS NORMAIS)
        // ============================================================

        $estoqueBruto = StockItem::with(['catalogPrint', 'catalogPrint.set'])
            ->where('store_id', $this->loja->id)
            ->whereIn('catalog_print_id', $this->prints->pluck('id'))
            ->get();

        $this->stockByPrint = $estoqueBruto
            ->groupBy('catalog_print_id')
            ->map(fn($group) => collect($group->all()))
            ->toBase();

        $list          = collect();
        $printsToLoad  = [];
        $setsWithStock = [];

        if ($estoqueBruto->isNotEmpty()) {
            foreach ($estoqueBruto as $stock) {
                $print         = $stock->catalogPrint;
                $printsToLoad[] = $print->id;
                $setsWithStock[] = $print->set_id;

                $rawLang   = strtolower($print->language_code ?? 'en');
                $langFixed = (str_contains($rawLang, 'pt')) ? 'pt' : $rawLang;

                $list->push([
                    'print_id'     => $print->id,
                    'stock_id'     => $stock->id,
                    'has_stock'    => $stock->quantity > 0 ? 1 : 0,
                    'had_stock'    => 1,
                    'stock_qty'    => $stock->quantity ?? 0,
                    'stock_price'  => $stock->final_price ?? $stock->price ?? 999999,
                    'release_date' => $print->set->released_at ?? '1900-01-01',
                    'language'     => $langFixed, 
                    'condition'    => $stock->condition,
                    'extras'       => $stock->extras, 
                ]);
            }
        }

        $queryFantasmas = CatalogPrint::with('set')
            ->whereIn('id', $this->prints->pluck('id')); 

        if (!empty($setsWithStock)) {
            $queryFantasmas->whereNotIn('set_id', array_unique($setsWithStock));
        }

        $fantasmas = $queryFantasmas->get()->groupBy('set_id');

        foreach ($fantasmas as $setId => $printsDoSet) {
            $printBase = $printsDoSet->firstWhere('language_code', 'en') ?? $printsDoSet->first();

            if ($printBase) {
                $printsToLoad[] = $printBase->id;
                $list->push([
                    'print_id'     => $printBase->id,
                    'stock_id'     => null,
                    'has_stock'    => 0,
                    'had_stock'    => 0,
                    'stock_qty'    => 0,
                    'stock_price'  => 999999,
                    'release_date' => $printBase->set->released_at ?? '1900-01-01',
                    'language'     => 'en',
                    'condition'    => null,
                    'extras'       => null,
                ]);
            }
        }

        $this->allPrints = CatalogPrint::with('set')
            ->whereIn('id', array_unique($printsToLoad))
            ->get()
            ->keyBy('id');

        $this->displayList = $list->sort(function ($a, $b) {
            $statusA = $a['has_stock'] ? 3 : ($a['had_stock'] ? 2 : 1);
            $statusB = $b['has_stock'] ? 3 : ($b['had_stock'] ? 2 : 1);

            if ($statusA !== $statusB) return $statusB <=> $statusA;
            if ($a['stock_price'] !== $b['stock_price']) return $b['stock_price'] <=> $a['stock_price']; 
            if ($a['release_date'] !== $b['release_date']) return $b['release_date'] <=> $a['release_date'];
            return $b['stock_qty'] <=> $a['stock_qty'];
        })->values()->toArray();

        if (!empty($this->displayList)) {
            $firstPrintId = $this->displayList[0]['print_id'];
            $firstStockId = $this->displayList[0]['stock_id'];
            $this->updateStats($firstPrintId, $firstStockId);
        } else {
            $this->priceStats = ['min' => 0, 'max' => 0, 'avg' => 0];
        }
    }

    public function updateStats($printId, $stockId = null)
    {
        $this->activePrintId = $printId;
        $this->activeStockId = $stockId;
        $cotacaoDolar = 5.50;

        $currentPrint = $this->allPrints->get($printId);
        if (!$currentPrint) {
            $this->activeImage = 'https://placehold.co/250x350/eeeeee/999999?text=Erro+Print';
            $this->cardDetails = null;
            $this->priceStats  = ['min' => 0, 'max' => 0, 'avg' => 0];
            return;
        }

        $this->activeImage = asset($currentPrint->image_path);
        $this->cardDetails = DB::table('mtg_prints')->where('id', $currentPrint->specific_id)->first();

        $isFoil   = false;
        $isEtched = false;

        if ($stockId) {
            $stockItemsForPrint = $this->stockByPrint?->get($printId);
            if ($stockItemsForPrint) {
                $stockItem = $stockItemsForPrint->where('id', $stockId)->first();

                if ($stockItem && !empty($stockItem->extras)) {
                    $extrasStr = is_array($stockItem->extras)
                        ? implode(' ', $stockItem->extras)
                        : (string) $stockItem->extras;
                    $extrasStr = strtolower($extrasStr);

                    $isEtched = str_contains($extrasStr, 'etched');
                    $isFoil   = !$isEtched && str_contains($extrasStr, 'foil'); 
                }
            }
        }

        $siblingPrintIds = CatalogPrint::where('set_id', $currentPrint->set_id)
            ->where('collector_number', $currentPrint->collector_number)
            ->pluck('id');

        $allStockForThisPrint = StockItem::whereIn('catalog_print_id', $siblingPrintIds)
            ->where('store_id', $this->loja->id)
            ->get();

        $prices = $allStockForThisPrint->filter(function($item) use ($isFoil, $isEtched) {
                $extrasStr = !empty($item->extras)
                    ? (is_array($item->extras) ? implode(' ', $item->extras) : (string) $item->extras)
                    : '';
                $extrasStr = strtolower($extrasStr);

                $itemIsEtched = str_contains($extrasStr, 'etched');
                $itemIsFoil   = !$itemIsEtched && str_contains($extrasStr, 'foil');

                return ($itemIsEtched === $isEtched) && ($itemIsFoil === $isFoil);
            })
            ->map(fn($item) => $item->final_price ?? $item->price)
            ->filter();

        $pricePrintId = $currentPrint->specific_id;

        if ($currentPrint->language_code !== 'en') {
            $englishEquivalent = CatalogPrint::where('concept_id', $currentPrint->concept_id)
                ->where('set_id', $currentPrint->set_id)
                ->where('language_code', 'en')
                ->first();
            if ($englishEquivalent) {
                $pricePrintId = $englishEquivalent->specific_id;
            }
        }

        $precoMedioBrl = 0;
        $mtgData = DB::table('mtg_prints')->where('id', $pricePrintId)->first();

        if ($mtgData && !empty($mtgData->prices)) {
            $pricesArray = is_string($mtgData->prices)
                ? json_decode($mtgData->prices, true)
                : (array)$mtgData->prices;

            if (is_array($pricesArray)) {
                if ($isEtched) {
                    $usd = $pricesArray['usd_etched'] ?? null;
                } elseif ($isFoil) {
                    $usd = $pricesArray['usd_foil'] ?? null;
                } else {
                    $usd = $pricesArray['usd'] ?? null;
                }

                if ($usd > 0) {
                    $precoMedioBrl = (float) $usd * $cotacaoDolar;
                }
            }
        }

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
        if (!$this->cardDetails && $this->concept?->specific?->id) {
            $this->cardDetails = DB::table('mtg_prints')->where('id', $this->concept->specific->id)->first();
        }

        $currentSetId    = null;
        $setCodeForTransform = null;
        $printAtivo      = null;
        $cardsAssociados = collect();
        $totalAssociados = 0;

        if ($this->activePrintId) {
            $printAtivo    = $this->allPrints?->get($this->activePrintId);
            $currentSetId  = $printAtivo?->set_id;
        }

        if ($currentSetId) {
            $setRecord = \App\Models\Set::find($currentSetId);
            $setCodeForTransform = strtoupper($setRecord->code ?? '');

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

            $printsBase = CatalogPrint::selectRaw('MIN(id) as print_id, concept_id')
                ->where('set_id', $currentSetId)
                ->where('language_code', 'en')
                ->groupBy('concept_id');

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

            $nomePtSubquery = DB::table('catalog_prints as cp_pt')
                ->select('cp_pt.printed_name')
                ->whereColumn('cp_pt.concept_id', 'catalog_prints.concept_id')
                ->whereIn('cp_pt.language_code', ['pt', 'pt-br', 'pt-BR'])
                ->whereNotNull('cp_pt.printed_name')
                ->where('cp_pt.printed_name', '!=', '')
                ->limit(1);

            $cardsRaw = CatalogPrint::select(
                    'catalog_prints.*',
                    'estoque.total_estoque',
                    'estoque.menor_preco',
                    'estoque.menor_preco_extras',
                    'estoque.menor_preco_desconto',
                    'mtg.artist'
                )
                ->selectSub($nomePtSubquery, 'nome_pt_banco')
                ->joinSub($estoqueValido, 'validos', function($join) {
                     $join->on('catalog_prints.concept_id', '=', 'validos.concept_id');
                })
                ->joinSub($printsBase, 'base', function($join) {
                     $join->on('catalog_prints.id', '=', 'base.print_id');
                })
                ->joinSub($estoqueAgrupado, 'estoque', function ($join) {
                    $join->on('catalog_prints.concept_id', '=', 'estoque.concept_id');
                })
                ->leftJoin('mtg_prints as mtg', 'catalog_prints.specific_id', '=', 'mtg.id')
                ->with(['concept'])
                ->inRandomOrder()
                ->limit(10)
                ->get();

            $cardsAssociados = $cardsRaw->map(function ($carta) use ($setCodeForTransform) {
                $precoBase          = (float) ($carta->menor_preco ?? 0);
                $percentualDesconto = (float) ($carta->menor_preco_desconto ?? 0);

                $carta->preco_final = ($percentualDesconto > 0) 
                    ? $precoBase * (1 - ($percentualDesconto / 100))
                    : $precoBase;

                $carta->is_foil = (bool) str_contains(strtolower($carta->menor_preco_extras ?? ''), 'foil');

                $englishName = $carta->concept->name ?? $carta->printed_name;
                $basePtName = $carta->nome_pt_banco ?? $englishName;

                $isVariantSet = in_array($setCodeForTransform, ['FEM', 'ALL', 'HML']);
                $hasLetterInNumber = preg_match('/[a-zA-Z]/', $carta->collector_number);
                $isBasicLand = str_contains($carta->type_line, 'Basic Land');
                
                $isArtVariant = $isVariantSet && $hasLetterInNumber && !$isBasicLand;

                if ($isArtVariant && !empty($carta->artist)) {
                    $carta->nome_localizado = $basePtName . ' (' . $carta->artist . ')';
                    $carta->name = sprintf('%s (%s) • #%s', $englishName, $carta->artist, $carta->collector_number);
                    $carta->concept_slug = Str::slug($englishName . '-' . $carta->artist);
                } else {
                    $carta->nome_localizado = $basePtName;
                    $carta->name = $englishName;
                    
                    if ($isBasicLand) {
                        $tiposBasicos = ['Plains', 'Island', 'Swamp', 'Mountain', 'Forest'];
                        $tipoEncontrado = collect($tiposBasicos)->first(fn($t) => str_contains($carta->type_line, $t));
                        
                        $carta->concept_slug = $tipoEncontrado 
                            ? Str::slug($tipoEncontrado) . '-' . $carta->collector_number 
                            : ($carta->concept->slug ?? Str::slug($englishName));
                    } else {
                        $carta->concept_slug = $carta->concept->slug ?? Str::slug($englishName);
                    }
                }

                $imagemBruta = $carta->image_url
                    ?? $carta->image_path
                    ?? $carta->concept->image_url
                    ?? $carta->concept->image_path
                    ?? 'https://placehold.co/250x350/eeeeee/999999?text=Sem+Imagem';

                $carta->imagem_final = filter_var($imagemBruta, FILTER_VALIDATE_URL)
                    ? $imagemBruta
                    : asset($imagemBruta);

                return $carta;
            });
        }

        return view('livewire.store.template.catalog.product-page', [
            'gameDetails'      => $this->cardDetails,
            'cardsAssociados'  => $cardsAssociados,
            'totalAssociados'  => $totalAssociados,
            'printAtivo'       => $this->allPrints?->get($this->activePrintId),
        ])->layout('layouts.template', ['loja' => $this->loja]);
    }
}