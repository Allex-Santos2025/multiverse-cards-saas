<?php

namespace App\Console\Commands;

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
     * Assinatura do comando. Usa 'scryfall:ingest-rulings' para evitar conflito com 'ingest:rulings'.
     * Adicionado suporte a --force e --id.
     *
     * @var string
     */
    protected $signature = 'scryfall:ingest-rulings 
                            {--id= : Opcional. ID do Oracle para ingestar julgamentos de um Card específico.}
                            {--force : Força a re-importação, ignorando o checkpoint}';

    /**
     * Descrição do comando.
     *
     * @var string
     */
    protected $description = 'Ingests ruling data from Scryfall API, with checkpoint support.';

    protected string $checkpointPath;

    public function __construct()
    {
        parent::__construct();
        // Caminho do arquivo de checkpoint para salvar o último ID processado
        $this->checkpointPath = storage_path('app/scryfall_rulings_checkpoint.txt');
    }

    /**
     * Executa o comando, iniciando a ingestão de julgamentos.
     *
     * @return int
     */
    public function handle(ScryfallApi $scryfallApi)
    {
        // Log inicial para marcar o início da operação
        Log::error("--- [RULINGS-DEBUG] O COMANDO 'HANDLE' COMEÇOU ---");

        // --- Lógica para processamento de um único card (debug) ---
        if ($oracleId = $this->option('id')) {
            $this->info("Iniciando ingestão específica para o Oracle ID: {$oracleId}");
            
            $functionality = CardFunctionality::where('mtg_oracle_id', $oracleId)->first();

            if (!$functionality) {
                $this->error("Funcionalidade com Oracle ID '{$oracleId}' (mtg_oracle_id) não encontrada.");
                return self::FAILURE;
            }

            $rulings = $this->fetchRulingsForFunctionality($functionality, $scryfallApi);
            
            if (empty($rulings)) {
                $this->info("Nenhum julgamento encontrado para {$oracleId}.");
                return self::SUCCESS;
            }

            $this->upsertRulingsBatch($rulings, count($rulings));
            $this->info("Sucesso! Inseridos/atualizados " . count($rulings) . " julgamentos para {$oracleId}.");
            return self::SUCCESS;
        }
        // --- Fim da lógica de processamento único ---

        $this->info('Iniciando ingestão de Julgamentos...');
        
        $lastProcessedId = $this->getCheckpoint();
        
        $query = CardFunctionality::orderBy('id');

        if ($lastProcessedId && !$this->option('force')) {
            $this->warn("Retomando a partir do último checkpoint (CardFunctionality ID: {$lastProcessedId}).");
            $query->where('id', '>', $lastProcessedId);
        } elseif($this->option('force')) {
            $this->warn('Opção --force detectada. Ignorando checkpoint e começando do zero.');
        }
        
        // Uso de cursor para otimizar memória em grandes datasets
        $functionalities = $query->cursor();

        if ($functionalities->count() === 0) {
            $this->info('Nenhuma nova funcionalidade para processar. Tudo já está atualizado!');
            return self::SUCCESS;
        }

        // --- RASTREIO DE DEBUG: Contagem Inicial e Variáveis de Controle ---
        $initialCount = Ruling::count();
        Log::error("[RULINGS-DEBUG] CONTADOR INICIAL: A tabela 'rulings' possui {$initialCount} registros.");
        $totalRulingsProcessed = 0; // Total de julgamentos enviados para upsert

        $batchSize = 20; 
        $rulingsBatch = [];
        
        // withProgressBar exibe o progresso no terminal
        $this->withProgressBar($functionalities, function (CardFunctionality $functionality) use ($scryfallApi, &$rulingsBatch, $batchSize, &$totalRulingsProcessed) {
            // Log do ID do card para facilitar a depuração
            Log::error("[RULINGS-DEBUG] Processando CardFunctionality ID: {$functionality->id}, Nome: {$functionality->mtg_name}");
            
            $rulings = $this->fetchRulingsForFunctionality($functionality, $scryfallApi);
            
            if (!empty($rulings)) {
                $rulingsBatch = array_merge($rulingsBatch, $rulings);
                
                // Quando o balde encher (20 itens), salva no banco
                if (count($rulingsBatch) >= $batchSize) {
                    $totalProcessedInBatch = count($rulingsBatch); 
                    // Chama a função de upsert que contém a lógica de log de contagem
                    $this->upsertRulingsBatch($rulingsBatch, $totalProcessedInBatch); 
                    
                    $totalRulingsProcessed += $totalProcessedInBatch; 
                    $rulingsBatch = [];
                }
            }
            $this->setCheckpoint($functionality->id);
        });

        // Salva o resto que sobrou no balde (lote final)
        if (!empty($rulingsBatch)) {
            $totalProcessedInBatch = count($rulingsBatch);
            $this->upsertRulingsBatch($rulingsBatch, $totalProcessedInBatch);
            $totalRulingsProcessed += $totalProcessedInBatch;
        }
        
        $finalCount = Ruling::count();

        $this->line(''); 
        $this->info("Ingestão de Julgamentos concluída. Total de Julgamentos processados (upsert): {$totalRulingsProcessed}");
        // Log final que compara o estado inicial e o final
        Log::error("[RULINGS-DEBUG] RESULTADO FINAL: A tabela 'rulings' tem {$finalCount} registros. (Aumento total de: " . ($finalCount - $initialCount) . ")");
        
        $this->clearCheckpoint(); 
        return self::SUCCESS;
    }

    /**
     * Busca os julgamentos no Scryfall para a funcionalidade da carta.
     */
    protected function fetchRulingsForFunctionality(CardFunctionality $functionality, ScryfallApi $scryfallApi): array
    {
        if (empty($functionality->mtg_oracle_id)) {
            return [];
        }

        // Busca o Card representativo para pegar o ID do Scryfall
        $representativeCard = Card::where('card_functionality_id', $functionality->id)
            ->whereNotNull('mtg_scryfall_id')
            ->first();

        if (!$representativeCard) {
            return [];
        }
        
        $url = "/cards/{$representativeCard->mtg_scryfall_id}/rulings";
        
        $rulingsData = $scryfallApi->getCardsByUrl($url); 
        // Pequeno delay para não tomar 429 da API
        usleep(50000); 

        if (empty($rulingsData['data'])) {
            return [];
        }

        Log::error("[RULINGS-DEBUG] SUCESSO! Encontrados " . count($rulingsData['data']) . " julgamentos para: {$functionality->mtg_name} (ID: {$functionality->id})");

        $rulingsToInsert = [];
        foreach ($rulingsData['data'] as $ruling) {
            $rulingsToInsert[] = [
                'card_functionality_id' => $functionality->id,
                // Garantir que a source é minúscula e padronizada (ex: 'scryfall' ou 'wizards')
                'source' => strtolower($ruling['source']), 
                'published_at' => $ruling['published_at'],
                'comment' => $ruling['comment'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        return $rulingsToInsert;
    }

    /**
     * Insere ou atualiza os julgamentos no banco de dados e registra o log de debug.
     *
     * @param array $rulingsBatch O array de julgamentos a serem inseridos/atualizados.
     * @param int $batchSize O tamanho do lote sendo processado (apenas para fins de log).
     */
    protected function upsertRulingsBatch(array $rulingsBatch, int $batchSize): void
    {
        // Contagem ANTES do UPSERT
        $initialCountBeforeBatch = Ruling::count(); 
        
        Ruling::upsert(
            $rulingsBatch,
            // Chave Única Composta para garantir unicidade: 
            // qual card, de qual fonte e em qual data foi publicado.
            ['card_functionality_id', 'source', 'published_at'],
            // Campos a serem atualizados se a chave já existir
            ['comment', 'updated_at'] 
        );
        
        // Contagem DEPOIS do UPSERT
        $finalCountAfterBatch = Ruling::count(); 
        $increase = $finalCountAfterBatch - $initialCountBeforeBatch;

        // Log de debug APRIMORADO que mostra o que realmente aconteceu no DB.
        Log::error("[RULINGS-DEBUG] SALVANDO LOTE! Julgamentos no lote: {$batchSize}. Contagem ANTES: {$initialCountBeforeBatch}. Contagem DEPOIS: {$finalCountAfterBatch}. Aumento real: {$increase}.");
    }
    
    // Métodos de Checkpoint (set, get, clear)

    protected function setCheckpoint(int $functionalityId): void
    {
        File::put($this->checkpointPath, $functionalityId);
    }

    protected function getCheckpoint(): ?int
    {
        if (!File::exists($this->checkpointPath)) {
            return null;
        }
        $content = File::get($this->checkpointPath);
        return is_numeric($content) ? (int)$content : null;
    }

    protected function clearCheckpoint(): void
    {
        if (File::exists($this->checkpointPath)) {
            File::delete($this->checkpointPath);
            $this->info('Checkpoint limpo.');
        }
    }
}