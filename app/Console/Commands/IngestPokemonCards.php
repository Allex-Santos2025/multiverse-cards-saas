<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Set;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\Games\Pokemon\PkConcept;
use App\Models\Games\Pokemon\PkPrint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IngestPokemonCards extends Command
{
    protected $signature = 'pokemon:ingest-cards 
                            {--set-id= : ID do Set na API (ex: base1, swsh3) para baixar apenas um}
                            {--locales=en,pt,es,de,fr,it : Idiomas a serem ingeridos}
                            {--force : Sobrescreve imagens locais e ignora checkpoint}
                            {--resume : Forca a leitura do checkpoint}';

    protected $description = 'Ingere cartas de Pokemon TCG com fallback inteligente de imagens e garantia de integridade.';

    protected ?Game $game;
    protected string $baseUrl = 'https://api.tcgdex.net/v2';
    protected string $checkpointPath;
    protected array $conceptIdCache = [];

    public function __construct()
    {
        parent::__construct();
        $this->checkpointPath = storage_path('app/pokemon_cards_checkpoint.txt');
    }

    public function handle()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $this->info("--- INGESTAO POKEMON TCG RESILIENTE (TCGdex) ---");

        $this->game = Game::find(2); 
        if (!$this->game) {
            $this->error("Game ID 2 (Pokemon TCG) nao encontrado na tabela games.");
            return self::FAILURE;
        }

        $localesInput = (string) $this->option('locales');
        $rawLocales = array_filter(array_map('trim', explode(',', $localesInput)));

        if (empty($rawLocales)) {
            $rawLocales = ['en', 'pt'];
        }

        // EN SEMPRE como primeira lingua
        $targetLocales = ['en'];
        foreach ($rawLocales as $loc) {
            if ($loc !== 'en') {
                $targetLocales[] = $loc;
            }
        }

        $this->info("Ordem das linguas: " . implode(' -> ', $targetLocales));

        $setsQuery = Set::where('game_id', $this->game->id)->orderBy('id', 'asc');

        if ($setId = $this->option('set-id')) {
            $setsQuery->where(function ($q) use ($setId) {
                $q->where('api_id', $setId)
                  ->orWhere('code', $setId);
            });
            $this->info("Modo Set Unico: [{$setId}].");
        } else {
            $lastSetId = $this->getCheckpoint();
            if ($lastSetId && !$this->option('force') && $this->option('resume')) {
                $setsQuery->where('id', '>', $lastSetId);
                $this->info("Retomando a partir do Set ID: {$lastSetId}.");
            }
        }

        $sets = $setsQuery->get();

        if ($sets->isEmpty()) {
            $this->info("Nenhum Set encontrado para processar.");
            return self::SUCCESS;
        }

        $this->info("Total de sets: " . $sets->count());

        foreach ($sets as $set) {
            $this->processSet($set, $targetLocales);

            if (!$this->option('set-id')) {
                $this->setCheckpoint($set->id);
            }

            gc_collect_cycles();
        }

        $this->info("\n--- Processo Finalizado com Sucesso ---");

        if (!$this->option('set-id')) {
            $this->clearCheckpoint();
        }

        return self::SUCCESS;
    }

    protected function processSet(Set $set, array $targetLocales): void
    {
        $this->conceptIdCache = [];
        $apiSetCode = strtolower($set->api_id ?: $set->code);

        $this->output->writeln("\n=======================================================");
        $this->output->writeln("Processando Set: [{$set->code}] {$set->name} (API ID: {$apiSetCode})");
        $this->output->writeln("=======================================================");

        foreach ($targetLocales as $locale) {
            $this->processSetForLocale($set, $apiSetCode, $locale);
            usleep(150000);
        }
    }

    protected function fetchWithRetry(string $url, int $maxRetries = 4): ?array
    {
        $attempt = 0;
        $delay = 1000;

        while ($attempt < $maxRetries) {
            $attempt++;
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'multiverse-cards-saas/1.0',
                    'Accept'     => 'application/json',
                ])->timeout(25)->get($url);

                if ($response->status() === 404) {
                    return null;
                }

                if ($response->successful()) {
                    return $response->json();
                }

                if ($response->status() === 429) {
                    usleep($delay * 2000);
                }
            } catch (\Throwable $e) {
                // Falha de rede temporaria, aguarda e tenta novamente
            }

            usleep($delay * 1000);
            $delay *= 2;
        }

        return null;
    }

    protected function processSetForLocale(Set $set, string $apiSetCode, string $locale): void
    {
        $url = "{$this->baseUrl}/{$locale}/sets/{$apiSetCode}";
        $setData = $this->fetchWithRetry($url);
        $cards = [];

        if (is_array($setData) && !empty($setData['cards'])) {
            $cards = $setData['cards'];
        }

        // Se o idioma local nao tiver cartas na API, aplica a Garantia de Matriz
        if (empty($cards)) {
            if ($locale === 'en') {
                $this->warn("   -> [en] Set sem cartas na API TCGdex.");
                return;
            }

            $this->warn("   -> [{$locale}] API sem cartas locais. Aplicando Garantia de Matriz...");
            $this->syncLocaleFromCanonicalMatrix($set, $locale);
            return;
        }

        $total = count($cards);
        $this->info("   -> [{$locale}] Sincronizando {$total} cartas oficiais...");

        $processed = 0;
        $imagesDownloaded = 0;
        $failedCards = [];

        foreach ($cards as $summaryCard) {
            $cardId = $summaryCard['id'];
            $cardUrl = "{$this->baseUrl}/{$locale}/cards/{$cardId}";

            $fullCardData = $this->fetchWithRetry($cardUrl);

            if (!$fullCardData) {
                $failedCards[] = $cardId;
                continue;
            }

            $success = $this->ingestCard($fullCardData, $set, $locale, $imagesDownloaded);
            if ($success) {
                $processed++;
            } else {
                $failedCards[] = $cardId;
            }

            usleep(80000);

            if ($processed % 20 === 0 || $processed === $total) {
                $this->output->write("\r      [{$locale}] Progresso: {$processed}/{$total} | Imagens: {$imagesDownloaded}    ");
            }
        }

        $this->output->writeln("\n      [{$locale}] Concluido: {$processed}/{$total} gravadas.");

        if (!empty($failedCards)) {
            $this->warn("      [{$locale}] Atencao: " . count($failedCards) . " cartas nao responderam apos retries: " . implode(', ', array_slice($failedCards, 0, 5)) . "...");
        }

        // Se foi um idioma secundario e ele teve menos cartas que a matriz EN, complementa os que faltaram
        if ($locale !== 'en') {
            $this->syncLocaleFromCanonicalMatrix($set, $locale);
        }
    }

    protected function syncLocaleFromCanonicalMatrix(Set $set, string $locale): void
    {
        $canonicalPrints = CatalogPrint::where('set_id', $set->id)
            ->where('language_code', 'en')
            ->with(['specific', 'concept'])
            ->get();

        if ($canonicalPrints->isEmpty()) {
            return;
        }

        $count = 0;
        foreach ($canonicalPrints as $enPrint) {
            $existing = CatalogPrint::where('set_id', $set->id)
                ->where('collector_number', $enPrint->collector_number)
                ->where('language_code', $locale)
                ->first();

            if (!$existing) {
                $enSpecific = $enPrint->specific;

                $pkPrint = PkPrint::create([
                    'rarity'        => $enSpecific->rarity ?? $enPrint->rarity,
                    'artist'        => $enSpecific->artist ?? null,
                    'number'        => $enPrint->collector_number,
                    'flavor_text'   => $enSpecific->flavor_text ?? null,
                    'level'         => $enSpecific->level ?? null,
                    'language_code' => $locale,
                    'tcgplayer'     => $enSpecific->tcgplayer ?? [],
                    'cardmarket'    => $enSpecific->cardmarket ?? [],
                    'images'        => $enSpecific->images ?? [],
                ]);

                CatalogPrint::create([
                    'concept_id'       => $enPrint->concept_id,
                    'set_id'           => $set->id,
                    'image_path'       => $enPrint->image_path, // Fallback direto para a imagem do EN
                    'specific_type'    => PkPrint::class,
                    'specific_id'      => $pkPrint->id,
                    'printed_name'     => $enPrint->printed_name,
                    'language_code'    => $locale,
                    'collector_number' => $enPrint->collector_number,
                    'rarity'           => $enPrint->rarity,
                    'type_line'        => $enPrint->type_line,
                ]);

                $count++;
            }
        }

        if ($count > 0) {
            $this->output->writeln("      [{$locale}] Garantia de Matriz: {$count} cartas sincronizadas via fallback.");
        }
    }

    protected function ingestCard(array $data, Set $set, string $locale, int &$imagesDownloaded): bool
    {
        return DB::transaction(function () use ($data, $set, $locale, &$imagesDownloaded) {
            try {
                $collectorNumber = (string) ($data['localId'] ?? $data['id']);
                $cardName = $data['name'] ?? 'Unknown';

                // 1. CONCEITO BASE
                $catalogConcept = null;

                if (isset($this->conceptIdCache[$cardName])) {
                    $catalogConcept = CatalogConcept::find($this->conceptIdCache[$cardName]);
                }

                if (!$catalogConcept) {
                    $catalogConcept = CatalogConcept::where('game_id', $this->game->id)
                        ->where('name', $cardName)
                        ->first();
                }

                if (!$catalogConcept) {
                    $types = $data['types'] ?? [];
                    if (is_string($types)) {
                        $types = [$types];
                    }

                    $subtypes = [];
                    if (isset($data['stage'])) $subtypes[] = $data['stage'];
                    if (isset($data['suffix'])) $subtypes[] = $data['suffix'];

                    $pkConcept = PkConcept::create([
                        'supertype'                => $data['category'] ?? 'Pokemon',
                        'hp'                       => isset($data['hp']) ? (string) $data['hp'] : null,
                        'level'                    => $data['level'] ?? null,
                        'types'                    => $types,
                        'subtypes'                 => $subtypes,
                        'attacks'                  => $data['attacks'] ?? [],
                        'abilities'                => $data['abilities'] ?? [],
                        'weaknesses'               => $data['weaknesses'] ?? [],
                        'resistances'              => $data['resistances'] ?? [],
                        'retreat_cost'             => isset($data['retreat']) ? array_fill(0, (int)$data['retreat'], 'Colorless') : [],
                        'evolves_from'             => $data['evolveFrom'] ?? null,
                        'evolves_to'               => [],
                        'rules_text'               => $data['description'] ?? null,
                        'national_pokedex_numbers' => $data['dexId'] ?? [],
                        'legalities'               => $data['legal'] ?? [],
                        'regulation_mark'          => $data['regulationMark'] ?? null,
                        'ancient_trait'            => null,
                    ]);

                    $catalogConcept = CatalogConcept::create([
                        'game_id'       => $this->game->id,
                        'name'          => $cardName,
                        'slug'          => Str::slug($cardName . '-' . $data['id']),
                        'specific_type' => PkConcept::class,
                        'specific_id'   => $pkConcept->id,
                    ]);
                }

                $this->conceptIdCache[$cardName] = $catalogConcept->id;

                // 2. IMAGEM COM FALLBACK AUTOMATICO PARA MATRIZ EN
                $rawImageUrl = $data['image'] ?? null;
                $localPath = null;

                if ($rawImageUrl) {
                    $imageUrl = Str::endsWith($rawImageUrl, '/high.webp') ? $rawImageUrl : "{$rawImageUrl}/high.webp";
                    $localPath = $this->downloadImage($imageUrl, $set->code, "{$data['id']}-{$locale}");
                    if ($localPath) {
                        $imagesDownloaded++;
                    }
                }

                // Se o idioma local nao tem imagem, herda da matriz em ingles
                if (!$localPath && $locale !== 'en') {
                    $canonicalEnPrint = CatalogPrint::where('set_id', $set->id)
                        ->where('collector_number', $collectorNumber)
                        ->where('language_code', 'en')
                        ->first();
                    $localPath = $canonicalEnPrint?->image_path;
                }

                // 3. PRECOS
                $pricing = $data['pricing'] ?? [];
                $tcgplayerPrices = $pricing['tcgplayer'] ?? $data['tcgplayer'] ?? [];
                $cardmarketPrices = $pricing['cardmarket'] ?? $data['cardmarket'] ?? [];

                // 4. IMPRESSAO FISICA (PRINT)
                $catalogPrint = CatalogPrint::where('set_id', $set->id)
                    ->where('collector_number', $collectorNumber)
                    ->where('language_code', $locale)
                    ->first();

                $rarity = $data['rarity'] ?? 'Common';
                $artist = $data['illustrator'] ?? null;

                if (!$catalogPrint) {
                    $pkPrint = PkPrint::create([
                        'rarity'        => $rarity,
                        'artist'        => $artist,
                        'number'        => $collectorNumber,
                        'flavor_text'   => $data['description'] ?? null,
                        'level'         => $data['level'] ?? null,
                        'language_code' => $locale,
                        'tcgplayer'     => $tcgplayerPrices,
                        'cardmarket'    => $cardmarketPrices,
                        'images'        => [
                            'normal' => $rawImageUrl ? "{$rawImageUrl}/high.webp" : null,
                            'small'  => $rawImageUrl ? "{$rawImageUrl}/low.webp" : null,
                        ],
                    ]);

                    CatalogPrint::create([
                        'concept_id'       => $catalogConcept->id,
                        'set_id'           => $set->id,
                        'image_path'       => $localPath,
                        'specific_type'    => PkPrint::class,
                        'specific_id'      => $pkPrint->id,
                        'printed_name'     => $cardName,
                        'language_code'    => $locale,
                        'collector_number' => $collectorNumber,
                        'rarity'           => Str::lower($rarity),
                        'type_line'        => $data['category'] ?? 'Pokemon',
                    ]);
                } else {
                    $catalogPrint->update([
                        'printed_name' => $cardName,
                        'image_path'   => $localPath ?? $catalogPrint->image_path,
                    ]);

                    if ($catalogPrint->specific) {
                        $catalogPrint->specific->update([
                            'tcgplayer'  => !empty($tcgplayerPrices) ? $tcgplayerPrices : $catalogPrint->specific->tcgplayer,
                            'cardmarket' => !empty($cardmarketPrices) ? $cardmarketPrices : $catalogPrint->specific->cardmarket,
                        ]);
                    }
                }

                return true;

            } catch (\Throwable $e) {
                Log::channel('single')->error("Erro ao salvar Carta Pokemon {$data['id']} [{$locale}]: " . $e->getMessage());
                return false;
            }
        });
    }

    protected function downloadImage(?string $url, string $setCode, string $fileIdentifier): ?string
    {
        if (empty($url)) return null;

        $safeName = Str::slug($fileIdentifier);
        $fileName = "{$safeName}.webp";
        $relativePath = "card_images/Pokemon/{$setCode}/{$fileName}";
        $fullPath = public_path($relativePath);

        if (File::exists($fullPath) && !$this->option('force')) {
            return $relativePath;
        }

        try {
            if (!File::exists(dirname($fullPath))) {
                File::ensureDirectoryExists(dirname($fullPath));
            }

            $response = Http::timeout(25)
                ->retry(3, 1000)
                ->sink($fullPath)
                ->get($url);

            if ($response->successful() && File::exists($fullPath) && File::size($fullPath) > 0) {
                return $relativePath;
            } else {
                if (File::exists($fullPath)) File::delete($fullPath);
            }
        } catch (\Throwable $e) {
            if (File::exists($fullPath)) File::delete($fullPath);
        }

        return File::exists($fullPath) ? $relativePath : null;
    }

    protected function setCheckpoint(int $id) { File::put($this->checkpointPath, (string)$id); }
    protected function getCheckpoint(): ?int { return File::exists($this->checkpointPath) ? (int)File::get($this->checkpointPath) : null; }
    protected function clearCheckpoint() { if (File::exists($this->checkpointPath)) File::delete($this->checkpointPath); }
}
