<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Store;
use App\Models\StockItem;
use Illuminate\Support\Facades\DB;

class SyncStoreCatalogSummaries extends Command
{
    protected $signature = 'catalog:sync-summaries';
    protected $description = 'Sincroniza resumos de cartas e produtos para todas as lojas';

    public function handle()
    {
        $this->info('Iniciando sincronização de resumos (Cartas e Produtos)...');

        $lojas = Store::all();

        foreach ($lojas as $loja) {
            $this->info("------------------------------------------------------------");
            $this->info("Processando Loja: {$loja->name} (ID: {$loja->id})");

            // --- 1. RESUMO POR PRINT (CARTAS) ---
            $this->comment('Atualizando cartas (Prints)...');
            $this->processByGroup($loja->id, 'catalog_print_id', 'App\Models\Catalog\CatalogPrint');

            // --- 2. RESUMO POR CONCEITO (CARTAS AGRUPADAS) ---
            $this->comment('Atualizando cartas agrupadas (Concepts)...');
            $this->processConcepts($loja->id);

            // --- 3. RESUMO POR PRODUTO (SELADOS/ACESSÓRIOS) ---
            $this->comment('Atualizando produtos selados e acessórios...');
            // Aqui usamos o product_id que você mencionou
            $this->processByGroup($loja->id, 'catalog_product_id', 'App\Models\CatalogProduct'); 
        }

        $this->info('------------------------------------------------------------');
        $this->info('Sincronização concluída com sucesso!');
    }

    /**
     * Lógica genérica para processar itens de estoque (Prints ou Produtos)
     */
    private function processByGroup($storeId, $foreignKey, $modelType)
    {
        $items = StockItem::select(
            $foreignKey . ' as target_id',
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('MIN(CASE WHEN quantity > 0 THEN price END) as lowest_price'),
            DB::raw('MAX(discount_percent) as max_discount'),
            DB::raw("GROUP_CONCAT(extras) as all_extras")
        )
        ->where('store_id', $storeId)
        ->whereNotNull($foreignKey) // Ignora se o ID estiver vazio
        ->whereNull('deleted_at') // GARANTIA: Ignora os itens apagados (Soft Deletes)
        ->groupBy($foreignKey)
        ->get();

        foreach ($items as $item) {
            $precoBase = $item->lowest_price ?? 0;
            $desconto = $item->max_discount ?? 0;
            $precoFinal = $precoBase * (1 - ($desconto / 100));

            DB::table('store_catalog_summaries')->updateOrInsert(
                [
                    'store_id'     => $storeId,
                    'catalog_type' => $modelType,
                    'catalog_id'   => $item->target_id,
                ],
                [
                    'total_qty'    => $item->total_qty,
                    'lowest_price' => $precoBase,
                    'final_price'  => $precoFinal,
                    'max_discount' => $desconto,
                    'has_foil'     => str_contains(strtolower($item->all_extras ?? ''), 'foil'),
                    'updated_at'   => now(),
                ]
            );
        }
    }

    /**
     * Lógica específica para conceitos (necessita de JOIN com catalog_prints)
     */
    private function processConcepts($storeId)
    {
        $concepts = DB::table('stock_items')
            ->join('catalog_prints', 'stock_items.catalog_print_id', '=', 'catalog_prints.id')
            ->select(
                'catalog_prints.concept_id',
                DB::raw('SUM(stock_items.quantity) as total_qty'),
                DB::raw('MIN(CASE WHEN stock_items.quantity > 0 THEN stock_items.price END) as lowest_price'),
                DB::raw('MAX(stock_items.discount_percent) as max_discount'),
                DB::raw("GROUP_CONCAT(stock_items.extras) as all_extras")
            )
            ->where('stock_items.store_id', $storeId)
            ->whereNotNull('catalog_prints.concept_id')
            ->whereNull('stock_items.deleted_at') // <--- A CORREÇÃO DE OURO AQUI
            ->groupBy('catalog_prints.concept_id')
            ->get();

        foreach ($concepts as $resumo) {
            $precoBase = $resumo->lowest_price ?? 0;
            $desconto = $resumo->max_discount ?? 0;
            $precoFinal = $precoBase * (1 - ($desconto / 100));

            DB::table('store_catalog_summaries')->updateOrInsert(
                [
                    'store_id'     => $storeId,
                    'catalog_type' => 'App\Models\Catalog\CatalogConcept',
                    'catalog_id'   => $resumo->concept_id,
                ],
                [
                    'total_qty'    => $resumo->total_qty,
                    'lowest_price' => $precoBase,
                    'final_price'  => $precoFinal,
                    'max_discount' => $desconto,
                    'has_foil'     => str_contains(strtolower($resumo->all_extras ?? ''), 'foil'),
                    'updated_at'   => now(),
                ]
            );
        }
    }
}