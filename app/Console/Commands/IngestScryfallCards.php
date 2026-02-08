<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Set;
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

    protected $description = 'Ingere cartas da API Scryfall para a estrutura V5, salvando metadados universais no catálogo.';

    protected ?Game $game = null;
    protected string $checkpointPath;

    public function __construct()
    {
        parent::__construct();
        $this->checkpointPath = storage_path('app/scryfall_cards_checkpoint.txt');
    }

    public function handle()
    {
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
            $this->error("Erro ao iniciar API: " . $e->getMessage());
            return self::FAILURE;
        }

        $setsQuery = Set::where('game_id', $this->game->id)->orderBy('released_at', 'asc');

        if ($setCode = $this->option('set-code')) {
            $setsQuery->where('code', $setCode);
            $this->info("Modo Set Único: [{$setCode}].");
        } else {
            $lastSetId = $this->getCheckpoint();
            if ($lastSetId && !$this->option('force')) {
                $setsQuery->where('id', '>', $lastSetId);
                $this->info("Retomando do Set ID: {$lastSetId}.");
            }
        }

        $sets = $setsQuery->get();
        if ($sets->isEmpty()) {
            $this->info("Nada para processar.");
            return self::SUCCESS;
        }

        foreach ($sets as $set) {
             $this->processSetCards($set, $scryfallApi);
             if (!$this->option('set-code')) {
                 $this->setCheckpoint($set->id);
             }
        }

        if (!$this->option('set-code')) $this->clearCheckpoint();

        return self::SUCCESS;
    }

    protected function processSetCards(Set $set, ScryfallApi $scryfallApi): void
    {
        $setCode = $set->code;
        $this->output->writeln("\nProcessando Set: [{$setCode}] {$set->name}");

        $baseUrl = rtrim((string)$this->game->api_url, '/');
        $url = "{$baseUrl}/cards/search?q=set:{$setCode}&unique=prints&include_extras=true&include_multilingual=true&order=collector&dir=asc";

        $cardsProcessed = 0;
        $imagesDownloaded = 0;
        $pageNumber = 1;

        do {
            usleep(100000);
            $cardsData = $scryfallApi->getCardsByUrl($url);
            if (empty($cardsData['data'])) break;

            $this->processPageChunk($cardsData['data'], $set, $imagesDownloaded, $cardsProcessed);

            $this->output->write("\r   -> Pág {$pageNumber} | Cards: {$cardsProcessed} | Imagens: {$imagesDownloaded}", false);
            $url = $cardsData['has_more'] ? $cardsData['next_page'] : null;
            $pageNumber++;
        } while ($url !== null);

        $this->output->writeln("");
    }

    protected function processPageChunk(array $cardsPage, Set $set, int &$imagesDownloaded, int &$cardsProcessed): void
    {
        foreach ($cardsPage as $cardData) {
            if (!isset($cardData['id'])) continue;

            try {
                $catalogConceptId = null;

                // 1. Processar CONCEITO
                if (isset($cardData['oracle_id'])) {
                    $conceptData = $this->mapMtgConcept($cardData);
                    $mtgConcept = MtgConcept::updateOrCreate(['oracle_id' => $conceptData['oracle_id']], $conceptData);

                    $catalogConcept = CatalogConcept::firstOrCreate(
                        ['specific_type' => MtgConcept::class, 'specific_id' => $mtgConcept->id],
                        [
                            'game_id' => $this->game->id,
                            'name' => $cardData['name'],
                            'slug' => Str::slug($cardData['name']) . '-' . substr($cardData['oracle_id'], 0, 4),
                        ]
                    );
                    $catalogConceptId = $catalogConcept->id;
                }

                // 2. Processar PRINT
                $printData = $this->mapMtgPrint($cardData, $set, $imagesDownloaded);

                // Variáveis extraídas para a tabela de catálogo geral
                $imagePath = $printData['_local_image_path'] ?? null;
                $printedName = $printData['_printed_name'] ?? $cardData['name'];
                $collectorNum = $cardData['collector_number'] ?? '0';
                $rarity = $cardData['rarity'] ?? 'common';
                
                // Lógica para pegar Type e Mana (mesmo se for carta de duas faces)
                $typeLine = $cardData['type_line'] ?? ($cardData['card_faces'][0]['type_line'] ?? null);
                $manaCost = $cardData['mana_cost'] ?? ($cardData['card_faces'][0]['mana_cost'] ?? null);
                $cmc = $cardData['cmc'] ?? 0;

                unset($printData['_local_image_path'], $printData['_printed_name']);

                // Salva na tabela específica do Magic
                $mtgPrint = MtgPrint::updateOrCreate(['api_id' => $printData['api_id']], $printData);

                // Salva na tabela de catálogo unificado (O que o sistema exibe)
                $catalogPrintData = [
                    'concept_id'       => $catalogConceptId,
                    'set_id'           => $set->id,
                    'printed_name'     => $printedName,
                    'language_code'    => $printData['language_code'] ?? 'en',
                    'collector_number' => $collectorNum,
                    'rarity'           => $rarity,
                    'type_line'        => $typeLine,
                    'mana_cost'        => $manaCost,
                    'cmc'              => $cmc,
                ];

                if ($imagePath) {
                    $catalogPrintData['image_path'] = $imagePath;
                }

                CatalogPrint::updateOrCreate(
                    ['specific_type' => MtgPrint::class, 'specific_id' => $mtgPrint->id],
                    $catalogPrintData
                );

                $cardsProcessed++;

            } catch (\Exception $e) {
                $this->error("\nErro no Card {$cardData['id']}: " . $e->getMessage());
                Log::channel('ingest')->error("Erro Ingest Card {$cardData['id']}: " . $e->getMessage());
            }
        }
    }

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

    protected function mapMtgPrint(array $cardData, Set $set, int &$imagesDownloaded): array
    {
        $nullIfEmpty = fn($v) => $v === "" ? null : $v;
        $setCode = $set->code;

        $printedName = $cardData['printed_name'] ?? $cardData['name'] ?? 'Unknown';
        $printedTypeLine = $cardData['printed_type_line'] ?? $cardData['type_line'] ?? null;
        $printedText = $cardData['printed_text'] ?? $cardData['oracle_text'] ?? null;
        $imageUris = $cardData['image_uris'] ?? null;

        if (isset($cardData['card_faces'][0])) {
            $face = $cardData['card_faces'][0];
            $printedName = $face['printed_name'] ?? $face['name'] ?? $printedName;
            $printedTypeLine = $face['printed_type_line'] ?? $face['type_line'] ?? $printedTypeLine;
            $imageUris = $face['image_uris'] ?? $imageUris;
        }

        $localPathLarge = null;
        if ($imageUris && isset($imageUris['large'])) {
            $localPathLarge = $this->downloadImage(
                $imageUris['large'],
                $setCode,
                $cardData['collector_number'] ?? '0',
                $cardData['lang'] ?? 'en'
            );
            if ($localPathLarge) $imagesDownloaded++;
        }

        return [
            'api_id'           => $cardData['id'],
            'language_code'    => $cardData['lang'] ?? 'en',
            'collector_number' => $cardData['collector_number'] ?? '0',
            'rarity'           => $cardData['rarity'] ?? 'common',
            'printed_name'      => $nullIfEmpty($printedName),
            'printed_type_line' => $nullIfEmpty($printedTypeLine),
            'artist'           => $nullIfEmpty($cardData['artist'] ?? null),
            'released_at'      => $cardData['released_at'] ?? null,
            'finishes'         => $cardData['finishes'] ?? [],
            'prices'           => $cardData['prices'] ?? [],
            '_local_image_path' => $localPathLarge,
            '_printed_name'     => $nullIfEmpty($printedName),
        ];
    }

    protected function downloadImage(string $url, string $setCode, string $collectorNum, string $lang): ?string
    {
        $fileName = "{$setCode}_{$lang}_{$collectorNum}.jpg";
        $relativePath = "card_images/Magic/{$setCode}/{$lang}/{$fileName}";
        $fullPath = public_path($relativePath);

        if (File::exists($fullPath)) return $relativePath;

        try {
            $response = Http::timeout(30)->get($url);
            if ($response->successful()) {
                File::ensureDirectoryExists(dirname($fullPath));
                File::put($fullPath, $response->body());
                usleep(150000); 
                return $relativePath;
            }
        } catch (\Exception $e) {}
        return null;
    }

    protected function setCheckpoint(int $id) { File::put($this->checkpointPath, $id); }
    protected function getCheckpoint() { return File::exists($this->checkpointPath) ? (int)File::get($this->checkpointPath) : null; }
    protected function clearCheckpoint() { if(File::exists($this->checkpointPath)) File::delete($this->checkpointPath); }
}