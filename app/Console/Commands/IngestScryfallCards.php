<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Set;
// Models V5 (Novos)
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\Games\Magic\MtgConcept;
use App\Models\Games\Magic\MtgPrint;
use App\Services\ScryfallApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IngestScryfallCards extends Command
{
    protected $signature = 'scryfall:ingest-cards
                            {--set-code= : O código de uma coleção específica para importar (ex: mkm)}
                            {--force : Ignora o checkpoint e começa do zero}
                            {--resume : Força a leitura do checkpoint (padrão se não houver force)}';

    protected $description = 'Ingere cartas da API Scryfall para a estrutura V5 (MtgConcept/MtgPrint), com checkpoint e imagens.';

    protected ?Game $game = null;
    protected string $checkpointPath;

    public function __construct()
    {
        parent::__construct();
        $this->checkpointPath = storage_path('app/scryfall_cards_checkpoint.txt');
    }

    public function handle()
    {
        // 1. Configuração Manual da API
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
            ->orderBy('released_at', 'asc');

        // 3. Lógica de Set Específico
        if ($setCode = $this->option('set-code')) {
            $setsQuery->where('code', $setCode);
            $this->info("Modo Set Único: Processando apenas [{$setCode}].");
        }
        // 4. Lógica de Checkpoint (CORRIGIDA E OTIMIZADA)
        else {
            $lastSetId = $this->getCheckpoint();

            if ($lastSetId && !$this->option('force')) {
                // AQUI ESTÁ A MÁGICA: Filtramos no banco para pegar SÓ o que vem depois
                $setsQuery->where('id', '>', $lastSetId);
                $this->info("Retomando a ingestão a partir do Set ID: {$lastSetId} (Checkpoint encontrado).");
            } else {
                $this->info("Iniciando ingestão do zero.");
            }
        }

        $sets = $setsQuery->get();

        if ($sets->isEmpty()) {
            $this->info("Nenhum Set novo encontrado para processar.");
            return self::SUCCESS;
        }

        $this->info("Iniciando processamento de " . $sets->count() . " Sets...");

        foreach ($sets as $set) {
             $this->processSetCards($set, $scryfallApi);

             // Atualiza o checkpoint a cada set finalizado com sucesso
             if (!$this->option('set-code')) {
                 $this->setCheckpoint($set->id);
             }
        }

        $this->info("\nIngestão Finalizada!");

        // Só limpa o checkpoint se rodou tudo até o fim sem set específico
        if (!$this->option('set-code') && $sets->count() > 0) {
            $this->clearCheckpoint();
        }

        return self::SUCCESS;
    }

    protected function processSetCards(Set $set, ScryfallApi $scryfallApi): void
    {
        $setCode = $set->code;
        $this->output->writeln("\nProcessando Set: [{$setCode}] {$set->name} (Total estimado: {$set->card_count})");

        $baseUrl = rtrim((string)$this->game->api_url, '/');
        // Adicionei include_multilingual=true explicitamente para garantir que venha PT
        $url = "{$baseUrl}/cards/search?q=set:{$setCode}&unique=prints&include_extras=true&include_multilingual=true&order=collector&dir=asc";

        $cardsProcessed = 0;
        $imagesDownloaded = 0;
        $pageNumber = 1;

        do {
            // Pequena pausa para não floodar
            usleep(100000);

            $cardsData = $scryfallApi->getCardsByUrl($url);

            if (empty($cardsData['data'])) {
                break;
            }

            // Processa o lote
            $this->processPageChunk($cardsData['data'], $set, $imagesDownloaded, $cardsProcessed);

            // Feedback visual
            $this->output->write("\r   -> Pág {$pageNumber} | Cards V5: {$cardsProcessed} | Imagens: {$imagesDownloaded}   ", false);

            $url = $cardsData['has_more'] ? $cardsData['next_page'] : null;
            $pageNumber++;

        } while ($url !== null);

        $this->output->writeln("");
        $this->info("   > Set [{$setCode}] CONCLUÍDO. Total processado: {$cardsProcessed}.");
    }

    protected function processPageChunk(array $cardsPage, Set $set, int &$imagesDownloaded, int &$cardsProcessed): void
    {
        foreach ($cardsPage as $cardData) {
            if (!isset($cardData['id'])) continue;

            try {
                $catalogConceptId = null;

                // 1. Processar CONCEITO (MtgConcept + CatalogConcept)
                // Só processa conceito se tiver oracle_id (tokens as vezes não têm)
                if (isset($cardData['oracle_id'])) {
                    $conceptData = $this->mapMtgConcept($cardData);

                    // Salva MtgConcept
                    $mtgConcept = MtgConcept::updateOrCreate(
                        ['oracle_id' => $conceptData['oracle_id']],
                        $conceptData
                    );

                    // Salva CatalogConcept (Pai)
                    $catalogConcept = CatalogConcept::firstOrCreate(
                        [
                            'specific_type' => MtgConcept::class,
                            'specific_id' => $mtgConcept->id
                        ],
                        [
                            'game_id' => $this->game->id,
                            'name' => $cardData['name'], // Nome em inglês sempre no conceito
                            'slug' => Str::slug($cardData['name']) . '-' . substr($cardData['oracle_id'], 0, 4),
                        ]
                    );
                    $catalogConceptId = $catalogConcept->id;
                }

                // 2. Processar PRINT (MtgPrint + CatalogPrint)
                $printData = $this->mapMtgPrint($cardData, $set, $imagesDownloaded);

                // Extrai campos auxiliares
                $imagePath = $printData['_local_image_path'] ?? null;
                $printedName = $printData['_printed_name'] ?? $cardData['name'];

                // Remove chaves auxiliares para não dar erro no SQL
                unset($printData['_local_image_path'], $printData['_printed_name']);

                // Salva MtgPrint (Tabela Específica)
                $mtgPrint = MtgPrint::updateOrCreate(
                    ['api_id' => $printData['api_id']], 
                    $printData
                );

                // Prepara dados do CatalogPrint (Tabela Pai)
                $catalogPrintData = [
                    'concept_id' => $catalogConceptId,
                    'set_id' => $set->id,
                    'printed_name' => $printedName, // Aqui vai o nome em PT se existir
                    'language_code' => $printData['language_code'] ?? 'en',
                ];

                // Só atualiza o path da imagem se tivermos um novo
                if ($imagePath) {
                    $catalogPrintData['image_path'] = $imagePath;
                }

                // Salva CatalogPrint
                CatalogPrint::updateOrCreate(
                    [
                        'specific_type' => MtgPrint::class,
                        'specific_id' => $mtgPrint->id
                    ],
                    $catalogPrintData
                );

                $cardsProcessed++;

            } catch (\Exception $e) {
                // Mostra o erro no console para debug rápido
                $this->error("\nErro no Card {$cardData['id']}: " . $e->getMessage());
                Log::channel('ingest')->error("Erro V5 Card {$cardData['id']}: " . $e->getMessage());
            }
        }
    }

    /**
     * Mapeia dados do Scryfall para a tabela mtg_concepts
     */
    protected function mapMtgConcept(array $cardData): array
    {
        $nullIfEmpty = fn($v) => $v === "" ? null : $v;
        $floatValid = fn($v) => is_numeric($v) ? (float)$v : null;

        return [
            'oracle_id'      => $cardData['oracle_id'],
            'mana_cost'      => $nullIfEmpty($cardData['mana_cost'] ?? null),
            'cmc'            => $cardData['cmc'] ?? 0,
            'type_line'      => $nullIfEmpty($cardData['type_line'] ?? null),
            'oracle_text'    => $nullIfEmpty($cardData['oracle_text'] ?? null),
            'power'          => $nullIfEmpty($cardData['power'] ?? null),
            'toughness'      => $nullIfEmpty($cardData['toughness'] ?? null),
            'loyalty'        => $nullIfEmpty($cardData['loyalty'] ?? null),
            'colors'         => $cardData['colors'] ?? [],
            'color_identity' => $cardData['color_identity'] ?? [],
            'keywords'       => $cardData['keywords'] ?? [],
            'legalities'     => $cardData['legalities'] ?? [],
            'produced_mana'  => $cardData['produced_mana'] ?? [],
            'edhrec_rank'    => $floatValid($cardData['edhrec_rank'] ?? null),
            'penny_rank'     => $floatValid($cardData['penny_rank'] ?? null),
        ];
    }

    /**
     * Mapeia dados do Scryfall para a tabela mtg_prints
     */
    protected function mapMtgPrint(array $cardData, Set $set, int &$imagesDownloaded): array
    {
        $nullIfEmpty = fn($v) => $v === "" ? null : $v;
        $setCode = $set->code;

        // Lógica de Faces e Localização
        $printedName = $cardData['printed_name'] ?? $cardData['name'] ?? 'Unknown';
        $printedTypeLine = $cardData['printed_type_line'] ?? $cardData['type_line'] ?? null;
        $printedText = $cardData['printed_text'] ?? $cardData['oracle_text'] ?? null;

        $imageUris = $cardData['image_uris'] ?? null;
        $artist = $cardData['artist'] ?? null;
        $flavorText = $cardData['flavor_text'] ?? null;
        $frame = $cardData['frame'] ?? null;
        $borderColor = $cardData['border_color'] ?? null;

        // Se for carta dupla (transform, etc), tenta pegar da primeira face
        if (isset($cardData['card_faces'][0])) {
            $face = $cardData['card_faces'][0];
            $printedName = $face['printed_name'] ?? $face['name'] ?? $printedName;
            $printedTypeLine = $face['printed_type_line'] ?? $face['type_line'] ?? $printedTypeLine;
            $printedText = $face['printed_text'] ?? $face['oracle_text'] ?? $printedText;

            $imageUris = $face['image_uris'] ?? $imageUris;
            $artist = $face['artist'] ?? $artist;
            $flavorText = $face['flavor_text'] ?? $flavorText;
        }

        // Lógica de Download de Imagem
        $localPathLarge = null;
        if ($imageUris && isset($imageUris['large'])) {
            $lang = $cardData['lang'] ?? 'en';
            $collectorNum = $cardData['collector_number'] ?? '0';

            $localPathLarge = $this->downloadImage(
                $imageUris['large'],
                $setCode,
                $collectorNum,
                $lang
            );

            if ($localPathLarge) {
                 $imagesDownloaded++;
            }
        }

        return [
            'api_id'           => $cardData['id'],
            'language_code'    => $cardData['lang'] ?? 'en',
            'collector_number' => $cardData['collector_number'] ?? '0',
            'rarity'           => $cardData['rarity'] ?? 'common',

            // Campos Localizados (Adicionados agora)
            'printed_name'      => $nullIfEmpty($printedName),
            'printed_type_line' => $nullIfEmpty($printedTypeLine),
            'printed_text'      => $nullIfEmpty($printedText),
            'flavor_name'       => $nullIfEmpty($cardData['flavor_name'] ?? null),
            'variation_of'      => $nullIfEmpty($cardData['variation_of'] ?? null),

            'artist'           => $nullIfEmpty($artist),
            'flavor_text'      => $nullIfEmpty($flavorText),
            'frame'            => $frame,
            'border_color'     => $borderColor,

            'full_art'         => $cardData['full_art'] ?? false,
            'textless'         => $cardData['textless'] ?? false,
            'promo'            => $cardData['promo'] ?? false,
            'reprint'          => $cardData['reprint'] ?? false,
            'digital'          => $cardData['digital'] ?? false,
            'variation'        => $cardData['variation'] ?? false,

            'finishes'         => $cardData['finishes'] ?? [],
            'prices'           => $cardData['prices'] ?? [],
            'multiverse_ids'   => $cardData['multiverse_ids'] ?? [],
            'related_uris'     => $cardData['related_uris'] ?? [],
            'purchase_uris'    => $cardData['purchase_uris'] ?? [],

            'released_at'      => $cardData['released_at'] ?? null,
            'image_status'     => $cardData['image_status'] ?? null,

            // Campos auxiliares (removidos antes do save)
            '_local_image_path' => $localPathLarge,
            '_printed_name'     => $nullIfEmpty($printedName),
        ];
    }

    protected function downloadImage(string $url, string $setCode, string $collectorNum, string $lang): ?string
    {
        $fileName = "{$setCode}_{$lang}_{$collectorNum}.jpg";
        $relativePath = "card_images/Magic/{$setCode}/{$lang}/{$fileName}";
        $fullAbsolutePath = public_path($relativePath);

        if (File::exists($fullAbsolutePath)) {
            return $relativePath;
        }

        try {
            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                File::ensureDirectoryExists(dirname($fullAbsolutePath));
                File::put($fullAbsolutePath, $response->body());

                usleep(150000); 
                return $relativePath;
            }
        } catch (\Exception $e) {
            // Silencioso
        }

        return null;
    }

    protected function setCheckpoint(int $id) { File::put($this->checkpointPath, $id); }
    protected function getCheckpoint() { return File::exists($this->checkpointPath) ? (int)File::get($this->checkpointPath) : null; }
    protected function clearCheckpoint() { if(File::exists($this->checkpointPath)) File::delete($this->checkpointPath); }
}
