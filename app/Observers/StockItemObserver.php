<?php

namespace App\Observers;

use App\Models\StockItem;
use Illuminate\Support\Facades\DB;
use App\Models\Catalog\CatalogPrint;

class StockItemObserver
{
    /**
     * Executado após criar ou atualizar um item de estoque
     */
    public function saved(StockItem $stockItem)
    {
        $this->syncSummary($stockItem);
    }

    /**
     * Executado após deletar um item de estoque
     */
    public function deleted(StockItem $stockItem)
    {
        $this->syncSummary($stockItem);
    }

    /**
     * Lógica central de recalculo cirúrgico
     */
    private function syncSummary(StockItem $stockItem)
    {
        $storeId = $stockItem->store_id;

        // 1. Identifica se é Carta ou Produto
        $catalogId = $stockItem->catalog_print_id ?? $stockItem->product_id;
        $catalogType = $stockItem->catalog_print_id ? 'App\Models\Catalog\CatalogPrint' : 'App\Models\Product';

        if (!$catalogId) return;

        // 2. Recalcula os dados apenas para este item nesta loja
        $resumo = StockItem::select(
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('MIN(CASE WHEN quantity > 0 THEN price END) as lowest_price'),
            DB::raw('MAX(discount_percent) as max_discount'),
            DB::raw("GROUP_CONCAT(extras) as all_extras")
        )
        ->where('store_id', $storeId)
        ->where($stockItem->catalog_print_id ? 'catalog_print_id' : 'product_id', $catalogId)
        ->first();

        if ($resumo && ($resumo->total_qty > 0 || $resumo->lowest_price > 0)) {
            $precoBase = $resumo->lowest_price ?? 0;
            $desconto = $resumo->max_discount ?? 0;
            $precoFinal = $precoBase * (1 - ($desconto / 100));

            DB::table('store_catalog_summaries')->updateOrInsert(
                ['store_id' => $storeId, 'catalog_type' => $catalogType, 'catalog_id' => $catalogId],
                [
                    'total_qty' => $resumo->total_qty,
                    'lowest_price' => $precoBase,
                    'final_price' => $precoFinal,
                    'max_discount' => $desconto,
                    'has_foil' => str_contains(strtolower($resumo->all_extras ?? ''), 'foil'),
                    'updated_at' => now(),
                ]
            );

            // Se for uma carta, atualiza também o resumo do "Conceito" (Agrupado)
            if ($stockItem->catalog_print_id) {
                $this->syncConceptSummary($storeId, $stockItem->catalog_print_id);
            }
        } else {
            // Se não sobrou nada no estoque, remove do resumo
            DB::table('store_catalog_summaries')
                ->where('store_id', $storeId)
                ->where('catalog_type', $catalogType)
                ->where('catalog_id', $catalogId)
                ->delete();
        }
    }

    private function syncConceptSummary($storeId, $printId)
    {
        $print = DB::table('catalog_prints')->where('id', $printId)->first();
        if (!$print || !$print->concept_id) return;

        $resumo = DB::table('stock_items')
            ->join('catalog_prints', 'stock_items.catalog_print_id', '=', 'catalog_prints.id')
            ->select(
                DB::raw('SUM(stock_items.quantity) as total_qty'),
                DB::raw('MIN(CASE WHEN stock_items.quantity > 0 THEN stock_items.price END) as lowest_price'),
                DB::raw('MAX(stock_items.discount_percent) as max_discount'),
                DB::raw("GROUP_CONCAT(stock_items.extras) as all_extras")
            )
            ->where('stock_items.store_id', $storeId)
            ->where('catalog_prints.concept_id', $print->concept_id)
            ->first();

        $precoBase = $resumo->lowest_price ?? 0;
        $desconto = $resumo->max_discount ?? 0;
        $precoFinal = $precoBase * (1 - ($desconto / 100));

        DB::table('store_catalog_summaries')->updateOrInsert(
            ['store_id' => $storeId, 'catalog_type' => 'App\Models\Catalog\CatalogConcept', 'catalog_id' => $print->concept_id],
            [
                'total_qty' => $resumo->total_qty,
                'lowest_price' => $precoBase,
                'final_price' => $precoFinal,
                'max_discount' => $desconto,
                'has_foil' => str_contains(strtolower($resumo->all_extras ?? ''), 'foil'),
                'updated_at' => now(),
            ]
        );
    }
}