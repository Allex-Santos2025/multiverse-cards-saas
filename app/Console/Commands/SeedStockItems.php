<?php

namespace App\Console\Commands;

use App\Models\Card;
use App\Models\Store;
use App\Models\StockItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedStockItems extends Command
{
    protected $signature = 'multiverse:seed-stock {--store= : ID da loja para seed}';
    protected $description = 'Cria entradas iniciais na tabela stock_items para todos os cards e condições.';

    // Definições de condições e idiomas que a loja suporta
    protected $conditions = ['NM', 'SP', 'MP', 'HP', 'DM'];
    protected $languages = ['en', 'pt', 'es', 'fr', 'de', 'it', 'ja', 'ko', 'ru', 'zhs', 'zht'];
    protected $foilStates = [true, false];

    public function handle()
    {
        $storeId = $this->option('store');

        if (!$storeId) {
            $this->error('O ID da loja é obrigatório. Use --store={id}');
            return;
        }

        $store = Store::find($storeId);

        if (!$store) {
            $this->error("Loja com ID {$storeId} não encontrada.");
            return;
        }

        $this->info("Iniciando seed de Stock Items para Loja: {$store->name} (ID: {$storeId})");

        // Buscar todos os IDs de Cards que já foram ingeridos pela Scryfall
        $cardIds = Card::pluck('id')->toArray();
        $totalCards = count($cardIds);

        if ($totalCards === 0) {
            $this->error('Nenhum Card encontrado no catálogo. Execute a ingestão da Scryfall primeiro.');
            return;
        }

        $itemsToInsert = [];
        $insertedCount = 0;

        // O progresso aqui é baseado no número total de combinações, não apenas nos cards
        $totalCombinations = $totalCards * count($this->conditions) * count($this->languages) * count($this->foilStates);
        $this->output->progressStart($totalCards);

        foreach ($cardIds as $cardId) {
            foreach ($this->conditions as $condition) {
                foreach ($this->languages as $language) {
                    foreach ($this->foilStates as $isFoil) {
                        $itemsToInsert[] = [
                            'store_id' => $storeId,
                            'card_id' => $cardId,
                            'condition' => $condition,
                            'language' => $language,
                            'is_foil' => $isFoil,
                            'quantity' => 0, // Estoque inicial ZERO
                            'price' => 0.00, // Preço inicial ZERO
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        
                        // Inserção em lotes (batching) para performance
                        if (count($itemsToInsert) >= 1000) {
                             $this->upsertStockBatch($itemsToInsert);
                             $insertedCount += count($itemsToInsert);
                             $itemsToInsert = [];
                        }
                    }
                }
            }
            $this->output->progressAdvance();
        }

        // Inserir lote final
        if (!empty($itemsToInsert)) {
            $this->upsertStockBatch($itemsToInsert);
            $insertedCount += count($itemsToInsert);
        }

        $this->output->progressFinish();
        $this->info("Seed concluído. Total de combinações de estoque inseridas: {$insertedCount}.");
    }

    protected function upsertStockBatch(array $batch)
    {
        // Usa upsert para evitar duplicatas e apenas atualizar (caso já exista no estoque)
        StockItem::upsert(
            $batch,
            ['store_id', 'card_id', 'condition', 'language', 'is_foil'], // Chave de unicidade
            ['quantity', 'price', 'updated_at'] // Campos a serem atualizados
        );
    }
}