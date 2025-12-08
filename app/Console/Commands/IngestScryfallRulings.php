<?php

namespace App\Console\Commands;

use App\Models\Game; // Adicionado
use App\Models\Card;
use App\Models\CardFunctionality;
use App\Models\Ruling;
use App\Services\ScryfallApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class IngestScryfallRulings extends Command
{
    /**
     * Assinatura do comando.
     */
    protected $signature = 'scryfall:ingest-rulings 
                            {--id= : Opcional. ID do Oracle para ingestar julgamentos de um Card específico.}
                            {--force : Força a re-importação, ignorando o checkpoint}';

    /**
     * Descrição do comando.
     */
    protected $description = 'Ingests ruling data from Scryfall API, with checkpoint support.';

    protected string $checkpointPath;
    
    // Propriedade para contexto do game
    protected ?Game $game = null;

    public function __construct()
    {
        parent::__construct();
        $this->checkpointPath = storage_path('app/scryfall_rulings_checkpoint.txt');
    }

    /**
     * Executa o comando.
     */
    public function handle() // Removida a injeção automática para instanciar manualmente
    {
        Log::error("--- [RULINGS-DEBUG] INÍCIO DO COMANDO ---");

        // ---------------------------------------------------------
        // 1. CONFIGURAÇÃO DA API (BASEADA NO INGEST CARDS)
        // ---------------------------------------------------------
        $this->game = Game::where('name', 'Magic: The Gathering')->first();

        if (!$this->game || empty($this->game->api_url)) {
            $this->error("Game 'Magic: The Gathering' não encontrado ou sem URL. Verifique a tabela games.");
            return self::FAILURE;
        }

        try {
            // Instancia a API manualmente com os dados do banco
            $scryfallApi = new ScryfallApi(
                (string)$this->game->api_url,
                (int)($this->game->rate_limit_ms ?? 100),
                (int)$this->game->id
            );
        } catch (\Throwable $e) {
            $this->error("Erro ao iniciar API: " . $e->getMessage());
            return self::FAILURE;
        }
        // ---------------------------------------------------------

        // --- Lógica para processamento de um único card (debug) ---
        if ($oracleId = $this->option('id')) {
            $this->info("Iniciando ingestão específica para o Oracle ID: {$oracleId}");
            
            $functionality = CardFunctionality::where('mtg_oracle_id', $oracleId)->first();

            if (!$functionality) {
                $this->error("Funcionalidade com Oracle ID '{$oracleId}' não encontrada.");
                return self::FAILURE;
            }

            $rulings = $this->fetchRulingsForFunctionality($functionality, $scryfallApi);
            
            if (empty($rulings)) {
                $this->info("Nenhum julgamento encontrado para {$oracleId}.");
                return self::SUCCESS;
            }

            $this->upsertRulingsBatch($rulings, count($rulings));
            $this->info("Processo finalizado para o ID único.");
            return self::SUCCESS;
        }

        // --- Ingestão Geral ---
        $this->info('Iniciando ingestão de Julgamentos...');
        
        $lastProcessedId = $this->getCheckpoint();
        
        $query = CardFunctionality::orderBy('id');

        if ($lastProcessedId && !$this->option('force')) {
            $this->warn("Retomando a partir do último checkpoint (ID: {$lastProcessedId}).");
            $query->where('id', '>', $lastProcessedId);
        } elseif($this->option('force')) {
            $this->warn('Opção --force detectada. Ignorando checkpoint.');
        }
        
        $functionalities = $query->cursor();

        if ($functionalities->count() === 0) {
            $this->info('Nenhuma nova funcionalidade para processar.');
            return self::SUCCESS;
        }

        // Setup de contadores e batch
        $initialCount = Ruling::count();
        Log::error("[RULINGS-DEBUG] CONTADOR INICIAL: {$initialCount} registros.");
        $totalRulingsProcessed = 0;
        $batchSize = 20; 
        $rulingsBatch = [];
        
        // Loop com barra de progresso (Lógica original mantida)
        $this->withProgressBar($functionalities, function (CardFunctionality $functionality) use ($scryfallApi, &$rulingsBatch, $batchSize, &$totalRulingsProcessed) {
            
            // [RESTAURADO] Log detalhado de qual carta está sendo processada
            Log::error("[RULINGS-DEBUG] Processando CardFunctionality ID: {$functionality->id}, Nome: {$functionality->mtg_name}");
            
            $rulings = $this->fetchRulingsForFunctionality($functionality, $scryfallApi);
            
            if (!empty($rulings)) {
                $rulingsBatch = array_merge($rulingsBatch, $rulings);
                
                if (count($rulingsBatch) >= $batchSize) {
                    $totalProcessedInBatch = count($rulingsBatch); 
                    
                    // Chama o método que calcula o "Aumento Real"
                    $this->upsertRulingsBatch($rulingsBatch, $totalProcessedInBatch); 
                    
                    $totalRulingsProcessed += $totalProcessedInBatch; 
                    $rulingsBatch = [];
                }
            }
            $this->setCheckpoint($functionality->id);
        });

        // Salva o lote final
        if (!empty($rulingsBatch)) {
            $totalProcessedInBatch = count($rulingsBatch);
            $this->upsertRulingsBatch($rulingsBatch, $totalProcessedInBatch);
            $totalRulingsProcessed += $totalProcessedInBatch;
        }
        
        $finalCount = Ruling::count();
        $this->line(''); 
        $this->info("Ingestão concluída.");
        Log::error("[RULINGS-DEBUG] RESULTADO FINAL: {$finalCount} registros. (Aumento Total: " . ($finalCount - $initialCount) . ")");
        
        $this->clearCheckpoint(); 
        return self::SUCCESS;
    }

    /**
     * Busca os julgamentos no Scryfall.
     */
    protected function fetchRulingsForFunctionality(CardFunctionality $functionality, ScryfallApi $scryfallApi): array
    {
        if (empty($functionality->mtg_oracle_id)) {
            return [];
        }

        $representativeCard = Card::where('card_functionality_id', $functionality->id)
            ->whereNotNull('mtg_scryfall_id')
            ->first();

        if (!$representativeCard) {
            return [];
        }
        
        // 2. CORREÇÃO DA URL: Usando a URL base do Game model (igual ao IngestCards)
        $baseUrl = rtrim((string)$this->game->api_url, '/');
        $url = "{$baseUrl}/cards/{$representativeCard->mtg_scryfall_id}/rulings";
        
        try {
            // Delay para Rate Limit (importante para não tomar 429)
            usleep(100000); 

            $rulingsData = $scryfallApi->getCardsByUrl($url); 

            if (empty($rulingsData['data'])) {
                return [];
            }

            // [RESTAURADO] Log de sucesso quando encontra julgamentos
            Log::error("[RULINGS-DEBUG] SUCESSO! Encontrados " . count($rulingsData['data']) . " julgamentos para: {$functionality->mtg_name} (ID: {$functionality->id})");

            $rulingsToInsert = [];
            foreach ($rulingsData['data'] as $ruling) {
                $rulingsToInsert[] = [
                    'card_functionality_id' => $functionality->id,
                    'source' => strtolower($ruling['source']), 
                    'published_at' => $ruling['published_at'],
                    'comment' => $ruling['comment'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            return $rulingsToInsert;

        } catch (\Exception $e) {
            Log::channel('ingest')->warning("Falha ao buscar rulings para {$functionality->mtg_name}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Insere e CALCULA O AUMENTO REAL (A funcionalidade que você queria)
     */
    protected function upsertRulingsBatch(array $rulingsBatch, int $batchSize): void
    {
        // 1. Conta antes
        $initialCountBeforeBatch = Ruling::count(); 
        
        Ruling::upsert(
            $rulingsBatch,
            ['card_functionality_id', 'source', 'published_at'],
            ['comment', 'updated_at'] 
        );
        
        // 2. Conta depois
        $finalCountAfterBatch = Ruling::count(); 
        
        // 3. Calcula a diferença (Novos registros de verdade)
        $increase = $finalCountAfterBatch - $initialCountBeforeBatch;

        $msg = "[RULINGS-DEBUG] SALVANDO LOTE! Julgamentos no lote: {$batchSize}. Contagem ANTES: {$initialCountBeforeBatch}. Contagem DEPOIS: {$finalCountAfterBatch}. Aumento real: {$increase}.";
        
        // Loga no arquivo
        Log::error($msg);
        
        // MOSTRA NO TERMINAL (Isso fará aparecer enquanto roda)
        $this->info("\n" . $msg);
    }
    
    // Métodos de Checkpoint
    protected function setCheckpoint(int $functionalityId) { File::put($this->checkpointPath, $functionalityId); }
    protected function getCheckpoint() { return File::exists($this->checkpointPath) ? (int)File::get($this->checkpointPath) : null; }
    protected function clearCheckpoint() { if(File::exists($this->checkpointPath)) File::delete($this->checkpointPath); }
}