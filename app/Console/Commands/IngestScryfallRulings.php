<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Ruling; // <--- USANDO O MODEL EXISTENTE
use App\Models\Games\Magic\MtgConcept; // <--- USANDO A FONTE DE DADOS NOVA
use App\Services\ScryfallApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class IngestScryfallRulings extends Command
{
    protected $signature = 'scryfall:ingest-rulings 
                            {--id= : Opcional. ID do Oracle para ingestar julgamentos de um Card específico.}
                            {--force : Força a re-importação, ignorando o checkpoint}
                            {--resume : Retoma do checkpoint}';

    protected $description = 'Ingests ruling data from Scryfall API linked to MtgConcepts.';

    protected ?Game $game = null;
    protected string $checkpointPath;

    public function __construct()
    {
        parent::__construct();
        $this->checkpointPath = storage_path('app/scryfall_rulings_checkpoint.txt');
    }

    public function handle()
    {
        Log::error("--- [RULINGS-DEBUG] INÍCIO DO COMANDO (MODEL EXISTENTE) ---");

        // 1. Configuração da API
        $this->game = Game::where('name', 'Magic: The Gathering')->first();
        if (!$this->game || empty($this->game->api_url)) {
            $this->error("Game 'Magic: The Gathering' não encontrado.");
            return self::FAILURE;
        }

        try {
            $scryfallApi = new ScryfallApi(
                (string)$this->game->api_url,
                (int)($this->game->rate_limit_ms ?? 100),
                (int)$this->game->id
            );
        } catch (\Throwable $e) {
            $this->error("Erro API: " . $e->getMessage());
            return self::FAILURE;
        }

        // 2. Query nos Conceitos (MtgConcept)
        $query = MtgConcept::query()->orderBy('id');

        if ($oracleId = $this->option('id')) {
            $query->where('oracle_id', $oracleId);
            $this->info("Modo Single: Oracle ID {$oracleId}");
        } elseif (!$this->option('force') && $lastId = $this->getCheckpoint()) {
            $query->where('id', '>', $lastId);
            $this->info("Retomando do ID: {$lastId}");
        }

        $totalToProcess = $query->count();
        if ($totalToProcess === 0) {
            $this->info("Nada a processar.");
            return self::SUCCESS;
        }

        $this->info("Processando {$totalToProcess} conceitos...");

        // CORREÇÃO: Usando Ruling::count() em vez de MtgRuling
        $initialCount = Ruling::count(); 

        $bar = $this->output->createProgressBar($totalToProcess);
        $rulingsBatch = [];
        $batchSize = 20;
        $processedCount = 0;

        // Chunk para memória
        $query->chunk(100, function ($concepts) use ($scryfallApi, &$rulingsBatch, $batchSize, $bar, &$processedCount) {
            foreach ($concepts as $concept) {
                $newRulings = $this->fetchRulingsForConcept($concept, $scryfallApi);

                foreach ($newRulings as $ruling) {
                    $rulingsBatch[] = $ruling;
                }

                if (count($rulingsBatch) >= $batchSize) {
                    $this->upsertRulingsBatch($rulingsBatch);
                    $rulingsBatch = [];
                }

                $this->setCheckpoint($concept->id);
                $bar->advance();
                $processedCount++;
            }
        });

        // Salva o resto
        if (!empty($rulingsBatch)) {
            $this->upsertRulingsBatch($rulingsBatch);
        }

        $bar->finish();

        $finalCount = Ruling::count();
        $this->line('');
        $this->info("Finalizado. Total processado: {$processedCount}.");
        $this->info("Registros na tabela Ruling: Antes: {$initialCount} -> Depois: {$finalCount}");

        $this->clearCheckpoint();
        return self::SUCCESS;
    }

    protected function fetchRulingsForConcept(MtgConcept $concept, ScryfallApi $api): array
    {
        if (empty($concept->oracle_id)) return [];

        $url = rtrim($this->game->api_url, '/') . "/cards/{$concept->oracle_id}/rulings";

        try {
            usleep(100000); 
            $data = $api->getCardsByUrl($url);

            if (empty($data['data'])) return [];

            $results = [];
            foreach ($data['data'] as $r) {
                $results[] = [
                    // AQUI O PULO DO GATO: Mapeamos para o campo novo, mas salvamos via Model antigo
                    'mtg_concept_id' => $concept->id, 
                    'source'         => strtolower($r['source'] ?? 'wotc'),
                    'published_at'   => $r['published_at'],
                    'comment'        => $r['comment'],
                    'oracle_id'      => $concept->oracle_id,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }
            return $results;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function upsertRulingsBatch(array $batch): void
    {
        // CORREÇÃO: Usando o Model Ruling existente
        Ruling::upsert(
            $batch,
            ['mtg_concept_id', 'source', 'published_at'], 
            ['comment', 'updated_at']
        );
    }

    protected function setCheckpoint(int $id) { File::put($this->checkpointPath, $id); }
    protected function getCheckpoint() { return File::exists($this->checkpointPath) ? (int)File::get($this->checkpointPath) : null; }
    protected function clearCheckpoint() { if(File::exists($this->checkpointPath)) File::delete($this->checkpointPath); }
}
