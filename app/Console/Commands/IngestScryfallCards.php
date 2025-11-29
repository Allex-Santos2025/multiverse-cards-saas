<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Set;
use App\Models\Card;
use App\Models\CardFunctionality;
use App\Services\ScryfallApi;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class IngestScryfallCards extends Command
{
    protected $signature = 'scryfall:ingest-cards 
                            {--set-code= : O código de uma coleção específica para importar (ex: mkm)}
                            {--force : Ignora o checkpoint e começa do zero}
                            {--resume : Força a leitura do checkpoint (padrão se não houver force)}';

    protected $description = 'Ingere cartas da API Scryfall para todos os Sets do Magic, com checkpoint e download de imagens.';

    protected ?Game $game = null;
    protected string $checkpointPath;
    protected array $functionalityIdCache = [];

    public function __construct()
    {
        parent::__construct();
        $this->checkpointPath = storage_path('app/scryfall_cards_checkpoint.txt');
    }

    public function handle()
    {
        // 1. Configuração Manual da API (Sem injeção automática para evitar erro)
        $this->game = Game::where('name', 'Magic: The Gathering')->first();
        
        if (!$this->game || empty($this->game->api_url)) {
            $this->error("Game 'Magic: The Gathering' não encontrado ou sem URL. Verifique a tabela games.");
            return self::FAILURE;
        }

        try {
            $scryfallApi = new ScryfallApi(
                (string)$this->game->api_url,
                (int)($this->game->rate_limit_ms ?? 100),
                (int)$this->game->id
            );
        } catch (\Throwable $e) {
            $this->error("Erro ao iniciar API: " . $e->getMessage());
            return self::FAILURE;
        }

        // 2. Query de Sets
        $setsQuery = Set::where('game_id', $this->game->id)
            ->orderBy('id', 'asc'); // Ordenação por ID é essencial para o checkpoint funcionar

        // 3. Lógica de Set Específico (Ignora checkpoint)
        if ($setCode = $this->option('set-code')) {
            $setsQuery->where('mtg_code', $setCode);
            $this->info("Modo Set Único: Processando apenas [{$setCode}].");
        } 
        // 4. Lógica de Checkpoint (Retomada)
        else {
            $lastSetId = $this->getCheckpoint();
            
            // Se existe checkpoint e NÃO estamos forçando o reinício
            if ($lastSetId && !$this->option('force')) {
                // Pega apenas os sets com ID MAIOR que o último processado
                $setsQuery->where('id', '>', $lastSetId);
                $this->info("Retomando a ingestão a partir do Set ID: {$lastSetId} (Checkpoint encontrado).");
            } else {
                $this->info("Iniciando ingestão do zero.");
            }
        }

        $sets = $setsQuery->get();

        if ($sets->isEmpty()) {
            $this->info("Nenhum Set encontrado para processar.");
            return self::SUCCESS;
        }

        $this->info("Iniciando processamento de " . $sets->count() . " Sets...");

        foreach ($sets as $set) {
             $this->processSetCards($set, $scryfallApi);
             
             // Salva o checkpoint APÓS o sucesso do set inteiro
             if (!$this->option('set-code')) {
                 $this->setCheckpoint($set->id);
             }
        }

        $this->info("\nIngestão Finalizada!");
        
        // Limpa o checkpoint apenas se rodou tudo com sucesso sem filtro de set
        if (!$this->option('set-code')) {
            $this->clearCheckpoint();
        }
        
        return self::SUCCESS;
    }

    protected function processSetCards(Set $set, ScryfallApi $scryfallApi): void
    {
        // Limpa cache de IDs para economizar memória entre sets
        $this->functionalityIdCache = []; 
        $setCode = $set->mtg_code ?? $set->code;

        $this->output->writeln("\nProcessando Set: [{$setCode}] {$set->name} (Total estimado: {$set->card_count})");

        $baseUrl = rtrim((string)$this->game->api_url, '/');
        
        // Query: unique=prints (todas as versões) + extras (tokens/terrenos) + multilingual
        $url = "{$baseUrl}/cards/search?q=set:{$setCode}&unique=prints&include_extras=true&include_multilingual=true&order=collector&dir=asc";
        
        $cardsProcessed = 0;
        $imagesDownloaded = 0;
        $pageNumber = 1;

        do {
            // Rate Limit antes da requisição da página
            usleep(100000); 

            $cardsData = $scryfallApi->getCardsByUrl($url);

            if (empty($cardsData['data'])) {
                break;
            }

            // Processa o Chunk de cartas
            $this->processPageChunk($cardsData['data'], $set, $imagesDownloaded, $cardsProcessed);
            
            // Feedback visual
            $this->output->write("\r   -> Pág {$pageNumber} | Cards: {$cardsProcessed} | Imagens Novas: {$imagesDownloaded}   ", false);

            $url = $cardsData['has_more'] ? $cardsData['next_page'] : null;
            $pageNumber++;

        } while ($url !== null);

        $this->output->writeln(""); 
        $this->info("   > Set [{$setCode}] CONCLUÍDO. Total: {$cardsProcessed} cartas.");
    }

    protected function processPageChunk(array $cardsPage, Set $set, int &$imagesDownloaded, int &$cardsProcessed): void
    {
        $functionalitiesToInsert = [];
        $cardsToSaveIndividually = [];

        foreach ($cardsPage as $cardData) {
            if (!isset($cardData['id'])) continue;

            // 1. Lógica de CardFunctionality (Conceito)
            $hasOracleId = isset($cardData['oracle_id']);
            $functionalityId = null;
            
            if ($hasOracleId) {
                $oracleId = $cardData['oracle_id'];
                if (!isset($functionalitiesToInsert[$oracleId])) {
                     $functionalitiesToInsert[$oracleId] = $this->mapCardFunctionality($cardData);
                }
                $functionalityId = $this->getFunctionalityId($oracleId, $functionalitiesToInsert[$oracleId]);
            }

            // 2. Mapeia o Card (Print)
            $mappedCard = $this->mapCard($cardData, $set, $functionalityId, $imagesDownloaded);
            
            if ($mappedCard) {
                $cardsToSaveIndividually[] = $mappedCard;
                $cardsProcessed++;
            }
        }

        // Upsert Funcionalidades
        if (!empty($functionalitiesToInsert)) {
             $validFuncs = array_filter($functionalitiesToInsert, fn($f) => !is_null($f['mtg_oracle_id']));
             if (!empty($validFuncs)) {
                try {
                    CardFunctionality::upsert(
                        array_values($validFuncs),
                        ['mtg_oracle_id'], 
                        [ 
                            'mtg_name', 'mtg_mana_cost', 'mtg_cmc', 'mtg_type_line', 
                            'mtg_rules_text', 'mtg_power', 'mtg_toughness', 'mtg_loyalty', 
                            'mtg_colors', 'mtg_color_identity', 'mtg_keywords', 
                            'mtg_legalities', 'mtg_produced_mana', 'mtg_edhrec_rank', 
                            'mtg_penny_rank', 'updated_at'
                        ]
                    );
                } catch (QueryException $e) {
                     Log::channel('ingest')->error("Erro Upsert Funcionalidades: " . $e->getMessage());
                }
             }
        }

        // Upsert Cards
        if (!empty($cardsToSaveIndividually)) {
            foreach ($cardsToSaveIndividually as $cardData) {
                try {
                    Card::updateOrCreate(
                        ['mtg_scryfall_id' => $cardData['mtg_scryfall_id']],
                        $cardData 
                    );
                } catch (QueryException $e) {
                    Log::channel('ingest')->error("Erro Upsert Card {$cardData['mtg_scryfall_id']}: " . $e->getMessage());
                }
            }
        }
    }

    protected function getFunctionalityId(?string $oracleId, ?array $mappedFunctionality): int|false|null
    {
        if (is_null($oracleId)) return null;
        if (isset($this->functionalityIdCache[$oracleId])) return $this->functionalityIdCache[$oracleId];
        
        $functionality = CardFunctionality::where('mtg_oracle_id', $oracleId)->first(['id']);
        if ($functionality) { 
            $this->functionalityIdCache[$oracleId] = $functionality->id; 
            return $functionality->id; 
        }

        if (is_null($mappedFunctionality)) return false;

        try {
            $tempFunctionality = CardFunctionality::create($mappedFunctionality);
            $this->functionalityIdCache[$oracleId] = $tempFunctionality->id;
            return $tempFunctionality->id;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function mapCardFunctionality(array $cardData): array
    {
        $nullIfEmpty = fn($v) => $v === "" ? null : $v;
        $floatValid = fn($v) => is_numeric($v) ? (float)$v : null;

        return [
            'game_id' => $this->game->id, 
            'mtg_oracle_id' => $cardData['oracle_id'] ?? null,
            'mtg_name' => $cardData['name'] ?? 'Unknown',
            'mtg_mana_cost' => $nullIfEmpty($cardData['mana_cost'] ?? null),
            'mtg_cmc' => $cardData['cmc'] ?? 0,
            'mtg_type_line' => $nullIfEmpty($cardData['type_line'] ?? null),
            'mtg_rules_text' => $nullIfEmpty($cardData['oracle_text'] ?? null),
            'mtg_power' => $nullIfEmpty($cardData['power'] ?? null),
            'mtg_toughness' => $nullIfEmpty($cardData['toughness'] ?? null),
            'mtg_loyalty' => $nullIfEmpty($cardData['loyalty'] ?? null),
            'mtg_colors' => json_encode($cardData['colors'] ?? []),
            'mtg_color_identity' => json_encode($cardData['color_identity'] ?? []),
            'mtg_keywords' => json_encode($cardData['keywords'] ?? []),
            'mtg_legalities' => json_encode($cardData['legalities'] ?? []),
            'mtg_produced_mana' => json_encode($cardData['produced_mana'] ?? []),
            'mtg_edhrec_rank' => $floatValid($cardData['edhrec_rank'] ?? null),
            'mtg_penny_rank' => $floatValid($cardData['penny_rank'] ?? null),
        ];
    }

    protected function mapCard(array $cardData, Set $set, ?int $cardFunctionalityId, int &$imagesDownloaded): ?array
    {
        $nullIfEmpty = fn($v) => $v === "" ? null : $v;
        $setCode = $set->mtg_code ?? $set->code;

        $printedName = $cardData['name'] ?? 'Unknown';
        $printedText = null;
        $printedTypeLine = $cardData['type_line'] ?? null;
        $imageUris = $cardData['image_uris'] ?? null;
        $artist = $cardData['artist'] ?? null;
        $flavorText = $cardData['flavor_text'] ?? null;

        if (isset($cardData['card_faces'][0])) {
            $face = $cardData['card_faces'][0];
            $printedName = $face['printed_name'] ?? $face['name'] ?? $printedName;
            $printedText = $face['oracle_text'] ?? null;
            $printedTypeLine = $face['type_line'] ?? null;
            $imageUris = $face['image_uris'] ?? $imageUris;
            $artist = $face['artist'] ?? $artist;
            $flavorText = $face['flavor_text'] ?? $flavorText;
        } else {
            $printedText = $cardData['oracle_text'] ?? null;
        }

        // Download Imagem (Lógica original restaurada com caminho físico correto)
        $localPathLarge = null;
        if ($imageUris && isset($imageUris['large'])) {
            $lang = $cardData['lang'] ?? 'en';
            $collectorNum = $cardData['collector_number'] ?? '0';
            $idPart = substr($cardData['id'], 0, 8);
            
            // Caminho Relativo (banco): card_images/Magic/SET/LANG/NUM_ID.jpg
            $relativePath = "card_images/Magic/{$setCode}/{$lang}/{$collectorNum}_{$idPart}.jpg";
            
            // Caminho Físico (disco): public/card_images/Magic/...
            $absolutePath = public_path($relativePath);
            
            if ($this->downloadImage($imageUris['large'], $absolutePath)) {
                $localPathLarge = $relativePath;
                $imagesDownloaded++;
            } elseif (File::exists($absolutePath)) {
                $localPathLarge = $relativePath;
            }
        }

        $priceData = $cardData['prices'] ?? [];

        return [
            'card_functionality_id' => $cardFunctionalityId,
            'set_id' => $set->id,
            // 'game_id' => $this->game->id, // Descomente se a coluna existir
            
            'mtg_scryfall_id' => $cardData['id'],
            'mtg_language_code' => $cardData['lang'] ?? 'en',
            'mtg_collection_number' => $cardData['collector_number'] ?? 'N/A',
            'mtg_collection_code' => $setCode,
            'mtg_printed_name' => $nullIfEmpty($printedName),
            'mtg_printed_text' => $nullIfEmpty($printedText),
            'mtg_printed_type_line' => $nullIfEmpty($printedTypeLine),
            'mtg_rarity' => $cardData['rarity'] ?? 'common',
            'mtg_artist' => $nullIfEmpty($artist),
            'mtg_flavor_text' => $nullIfEmpty($flavorText),
            'mtg_frame' => $cardData['frame'] ?? null,
            'mtg_border_color' => $cardData['border_color'] ?? null,
            'mtg_full_art' => $cardData['full_art'] ?? false,
            'mtg_textless' => $cardData['textless'] ?? false,
            'mtg_promo' => $cardData['promo'] ?? false,
            'mtg_reprint' => $cardData['reprint'] ?? false,
            'mtg_variation' => $cardData['variation'] ?? false,
            'mtg_image_uris' => json_encode($imageUris ?? []),
            'mtg_prices' => json_encode($priceData),
            'local_image_path_large' => $localPathLarge,
        ];
    }

    protected function downloadImage(string $url, string $absolutePath): bool
    {
        if (File::exists($absolutePath)) return false;

        try {
            $response = Http::timeout(20)->get($url);
            if ($response->successful()) {
                File::ensureDirectoryExists(dirname($absolutePath)); 
                File::put($absolutePath, $response->body()); 
                usleep(150000); // Rate Limit Imagem
                return true;
            }
        } catch (\Exception $e) {
            // Log::warning("Falha imagem: " . $e->getMessage());
        }
        return false;
    }

    protected function setCheckpoint(int $id) { File::put($this->checkpointPath, $id); }
    protected function getCheckpoint() { return File::exists($this->checkpointPath) ? (int)File::get($this->checkpointPath) : null; }
    protected function clearCheckpoint() { if(File::exists($this->checkpointPath)) File::delete($this->checkpointPath); }
}