<?php

namespace App\Livewire\Store\Template\Catalog;

use Livewire\Component;
use App\Models\Store;
use App\Models\Game;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\StockItem; 
use Illuminate\Support\Facades\DB;

class ProductPage extends Component
{
    public $slug, $gameSlug, $conceptSlug;
    public $loja, $game, $concept, $nomeLocalizado;
    public $allPrints, $priceStats, $activePrintId, $activeImage;
    public $stockByPrint;
    public $displayList = []; // Nova lista para desmembrar o estoque

    public $cardDetails;

    public function mount($slug, $gameSlug, $conceptSlug)
    {
        $this->slug = $slug;
        $this->gameSlug = $gameSlug;
        $this->conceptSlug = $conceptSlug;

        $this->loja = Store::where('url_slug', $slug)->firstOrFail();
        
        // Busca do conceito flexível (com ou sem hash no final da URL)
        $this->concept = CatalogConcept::where(function($q) use ($conceptSlug) {
            $q->where('slug', $conceptSlug)
              ->orWhere('slug', 'like', $conceptSlug . '-%');
        })->firstOrFail();

        // BUSCA DO NOME EM PORTUGUÊS 
        $printPt = CatalogPrint::where('concept_id', $this->concept->id)
            ->whereIn('language_code', ['pt', 'pt-br', 'pt-BR'])
            ->whereNotNull('printed_name')
            ->where('printed_name', '!=', '')
            ->first();
        $this->nomeLocalizado = $printPt ? $printPt->printed_name : null;

        // Carrega o estoque total da loja blindado contra o erro de Coleção do Livewire
        $this->stockByPrint = StockItem::where('store_id', $this->loja->id)
            ->whereHas('catalogPrint', fn($q) => $q->where('concept_id', $this->concept->id))
            ->get()
            ->groupBy('catalog_print_id')
            ->map(fn($group) => collect($group->all()))
            ->toBase();

        // 1. Identifica os IDs de edições que a loja realmente tem (ou teve) em estoque
        $idsComEstoque = array_keys($this->stockByPrint->toArray());

        // 2. Busca as edições: Todas em inglês (esqueleto) + Qualquer outra língua que tenha estoque
        $this->allPrints = CatalogPrint::with('set')
            ->where('concept_id', $this->concept->id)
            ->where(function($q) use ($idsComEstoque) {
                $q->where('language_code', 'en')
                  ->orWhereIn('id', $idsComEstoque);
            })
            ->get();
            $list = collect();

        // DESMEMBRA CADA ESTOQUE EM UMA LINHA SEPARADA
        foreach ($this->allPrints as $print) {
            $stocks = $this->stockByPrint->get($print->id);

            if ($stocks && $stocks->isNotEmpty()) {
                foreach ($stocks as $stock) {
                    $list->push([
                        'print_id' => $print->id,
                        'stock_id' => $stock->id,
                        'has_stock' => $stock->quantity > 0 ? 1 : 0,
                        'had_stock' => 1,
                        'stock_qty' => $stock->quantity ?? 0,
                        'stock_price' => $stock->final_price ?? $stock->price ?? 999999,
                        'release_date' => $print->set->released_at ?? '1900-01-01'
                    ]);
                }
            } else {
                $list->push([
                    'print_id' => $print->id,
                    'stock_id' => null,
                    'has_stock' => 0,
                    'had_stock' => 0,
                    'stock_qty' => 0,
                    'stock_price' => 999999,
                    'release_date' => $print->set->released_at ?? '1900-01-01'
                ]);
            }
        }

        // ORDENA AS LINHAS 
        $this->displayList = $list->sort(function ($a, $b) {
            if ($a['has_stock'] !== $b['has_stock']) return $b['has_stock'] <=> $a['has_stock'];
            if ($a['has_stock']) {
                if ($a['stock_qty'] !== $b['stock_qty']) return $b['stock_qty'] <=> $a['stock_qty'];
                if ($a['stock_price'] !== $b['stock_price']) return $a['stock_price'] <=> $b['stock_price'];
            } else {
                if ($a['had_stock'] !== $b['had_stock']) return $b['had_stock'] <=> $a['had_stock'];
            }
            return $b['release_date'] <=> $a['release_date'];
        })->values()->toArray();

        // Define o Estado Inicial
        if (!empty($this->displayList)) {
            $firstPrintId = $this->displayList[0]['print_id'];
            $this->updateStats($firstPrintId);
        }
    }

    public function updateStats($printId)
    {
        $this->activePrintId = $printId;
        $cotacaoDolar = 5.50; // Você pode tornar isso dinâmico depois

        $currentPrint = $this->allPrints->where('id', $printId)->first();
        if (!$currentPrint) return;

        $this->activeImage = asset($currentPrint->image_path);

        $this->cardDetails = DB::table('mtg_prints')->where('id', $currentPrint->specific_id)->first();

        // ============================================================
        // LÓGICA DE HERANÇA DE PREÇO (EN -> TRADUÇÕES)
        // ============================================================
        
        // Começamos tentando o ID da própria carta
        $pricePrintId = $currentPrint->specific_id;

        // Se a carta NÃO for inglês, buscamos o ID da versão em inglês para garantir o preço médio
        if ($currentPrint->language_code !== 'en') {
            $englishEquivalent = CatalogPrint::where('concept_id', $currentPrint->concept_id)
                ->where('set_id', $currentPrint->set_id)
                ->where('language_code', 'en')
                ->first();
            
            if ($englishEquivalent) {
                $pricePrintId = $englishEquivalent->specific_id;
            }
        }

        // PREÇO MÉDIO MUNDIAL (SCRYFALL)
        $precoMedioBrl = 0;
        $mtgData = DB::table('mtg_prints')->where('id', $pricePrintId)->first();
        
        if ($mtgData && !empty($mtgData->prices)) {
            $pricesArray = is_string($mtgData->prices) ? json_decode($mtgData->prices, true) : (array)$mtgData->prices;
            
            if (is_array($pricesArray)) {
                // Pega o valor em USD e converte
                $usd = $pricesArray['usd'] ?? $pricesArray['usd_foil'] ?? $pricesArray['usd_etched'] ?? 0;
                $precoMedioBrl = (float)$usd * $cotacaoDolar;
            }
        }

        // MÍNIMO E MÁXIMO DA LOJA (ESTOQUE PARA ESTA EDIÇÃO ESPECÍFICA)
        $stockItems = StockItem::where('catalog_print_id', $printId)
            ->where('store_id', $this->loja->id)
            ->where('quantity', '>', 0)
            ->get();

        $this->priceStats = [
            'min' => $stockItems->min('final_price') ?? $stockItems->min('price') ?? 0,
            'max' => $stockItems->max('final_price') ?? $stockItems->max('price') ?? 0,
            'avg' => $precoMedioBrl, // Agora preenchido mesmo em traduções
        ];
    }

    public function render()
    {
        // Se ainda não temos detalhes (primeiro carregamento), buscamos o padrão do conceito
        if (!$this->cardDetails) {
            $this->cardDetails = DB::table('mtg_prints')
                ->where('id', $this->concept->specific_id)
                ->first();
        }

        return view('livewire.store.template.catalog.product-page', [
            'gameDetails' => $this->cardDetails
        ])->layout('layouts.template', ['loja' => $this->loja]);
    }
}