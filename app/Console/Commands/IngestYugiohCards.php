<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Set;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\Catalog\Games\Yugioh\YgoConcept;
use App\Models\Catalog\Games\Yugioh\YgoPrint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IngestYugiohCards extends Command
{
    protected $signature = 'yugioh:ingest-cards
                            {--set-code= : O código da coleção específica para importar (ex: SDK ou YS15-2)}
                            {--only-affected : Processa apenas sets novos, pendentes ou que tiveram colisão de código}
                            {--force : Ignora o checkpoint e começa do zero}
                            {--resume : Força a leitura do checkpoint}';

    protected $description = 'Ingere cartas multilíngues da YGOPRODeck com preços (Cardmarket/TCGplayer) e imagens locais.';

    protected ?Game $game = null;
    protected string $checkpointPath;
    protected string $baseUrl = 'https://db.ygoprodeck.com/api/v7';

    // Idiomas oficiais TCG suportados pela YGOPRODeck
    protected array $supportedLanguages = [
        'en' => null,
        'pt' => 'pt',
        'fr' => 'fr',
        'de' => 'de',
        'it' => 'it',
    ];

    // Sufixo impresso oficial na carta para cada língua
    protected array $langCodeMap = [
        'en' => 'EN',
        'pt' => 'PT',
        'fr' => 'FR',
        'de' => 'DE',
        'it' => 'IT',
    ];

    // Cache em memória do bulk download indexado por set_name normalizado
    protected array $cardsBySetName = [];
    protected array $localizedById = [];
    protected bool $bulkLoaded = false;

    public function __construct()
    {
        parent::__construct();
        $this->checkpointPath = storage_path('app/yugioh_cards_checkpoint.txt');
    }

    public function handle(): int
    {
        ini_set('memory_limit', '2G');

        $this->game = Game::where('id', 3)->orWhere('name', 'like', '%Yu-Gi-Oh%')->first();

        if (!$this->game) {
            $this->error("Game 'Yu-Gi-Oh!' não encontrado na tabela de games.");
            return self::FAILURE;
        }

        $setsQuery = Set::where('game_id', $this->game->id)->orderBy('id', 'asc');

        if ($setCode = $this->option('set-code')) {
            $setsQuery->where('code', strtoupper($setCode));
            $this->info("Modo Set Único: [{$setCode}].");
        } elseif ($this->option('only-affected')) {
            $affectedIds = $this->getAffectedAndMissingSetIds();
            $setsQuery->whereIn('id', $affectedIds);
            $this->info("Modo Only-Affected: filtrando " . count($affectedIds) . " sets (colididos, incompletos ou sem cartas).");

            $lastSetId = $this->getCheckpoint();
            if ($lastSetId && !$this->option('force')) {
                $setsQuery->where('id', '>', $lastSetId);
                $this->info("Retomando do Set ID: {$lastSetId}.");
            }
        } else {
            $lastSetId = $this->getCheckpoint();
            if ($lastSetId && !$this->option('force')) {
                $setsQuery->where('id', '>', $lastSetId);
                $this->info("Retomando do Set ID: {$lastSetId}.");
            }
        }

        $sets = $setsQuery->get();
        if ($sets->isEmpty()) {
            $this->info("Nenhum set para processar.");
            return self::SUCCESS;
        }

        // Carrega o catálogo completo da YGOPRODeck em memória (5 requisições no total)
        $this->loadBulkCatalog();

        $this->info("Iniciando processamento multilíngue de " . $sets->count() . " set(s)...");

        foreach ($sets as $set) {
            $this->processSetCards($set);

            if (!$this->option('set-code')) {
                $this->setCheckpoint($set->id);
            }
        }

        if (!$this->option('set-code')) {
            $this->clearCheckpoint();
        }

        $this->info("Processo de ingestão concluído com sucesso!");
        return self::SUCCESS;
    }

    protected function loadBulkCatalog(): void
    {
        if ($this->bulkLoaded) {
            return;
        }

        $this->info("Baixando catálogo completo da YGOPRODeck em memória (Bulk Mode)...");

        // 1. Catálogo principal em Inglês
        $this->output->write("   -> Carregando base canônica [EN]... ");
        $resEn = Http::timeout(180)->get("{$this->baseUrl}/cardinfo.php");
        if (!$resEn->successful()) {
            throw new \RuntimeException("Falha ao baixar catálogo EN da YGOPRODeck: HTTP " . $resEn->status());
        }

        $allCardsEn = $resEn->json()['data'] ?? [];
        $this->output->writeln(count($allCardsEn) . " cartas carregadas.");

        foreach ($allCardsEn as $card) {
            foreach ($card['card_sets'] ?? [] as $cs) {
                $normName = $this->normalizeSetName($cs['set_name'] ?? '');
                if ($normName !== '') {
                    $this->cardsBySetName[$normName][$card['id']] = $card;
                }
            }
        }
        unset($allCardsEn);

        // 2. Catálogos traduzidos (PT, FR, DE, IT)
        foreach ($this->supportedLanguages as $lang => $apiParam) {
            if ($lang === 'en') continue;

            $this->output->write("   -> Carregando traduções [" . strtoupper($lang) . "]... ");
            $resLang = Http::timeout(180)->get("{$this->baseUrl}/cardinfo.php", ['language' => $apiParam]);

            if ($resLang->successful()) {
                $langCards = $resLang->json()['data'] ?? [];
                foreach ($langCards as $lc) {
                    $this->localizedById[$lang][$lc['id']] = [
                        'name' => $lc['name'] ?? null,
                        'desc' => $lc['desc'] ?? null,
                        'type' => $lc['type'] ?? null,
                    ];
                }
                $this->output->writeln(count($langCards) . " cartas traduzidas.");
                unset($langCards);
            } else {
                $this->output->writeln("Falha HTTP " . $resLang->status());
            }
        }

        $this->bulkLoaded = true;
    }

    protected function normalizeSetName(string $name): string
    {
        $decoded = html_entity_decode(trim($name), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return strtolower(preg_replace('/\s+/', ' ', $decoded));
    }

    protected function getAffectedAndMissingSetIds(): array
    {
        $allSets = Set::where('game_id', $this->game->id)->get();

        // Identifica prefixos que pertencem a múltiplos sets (ex: YS15, CT10)
        $groupedByBase = $allSets->groupBy(fn($s) => explode('-', $s->code)[0]);
        $collidedBaseCodes = $groupedByBase->filter(fn($g) => $g->count() > 1)->keys()->toArray();

        $ids = [];
        foreach ($allSets as $s) {
            $base = explode('-', $s->code)[0];
            $printsCount = CatalogPrint::where('set_id', $s->id)->count();

            // Inclui se: faz parte de código colidido, ou tem 0 prints, ou tem menos prints que card_count
            if (in_array($base, $collidedBaseCodes) || $printsCount === 0 || ($s->card_count > 0 && $printsCount < $s->card_count)) {
                $ids[] = $s->id;
            }
        }

        return array_values(array_unique($ids));
    }

    protected function processSetCards(Set $set): void
    {
        $setCode = $set->code;
        $normSetName = $this->normalizeSetName($set->name);
        $this->output->writeln("\nProcessando Set ID {$set->id}: [{$setCode}] {$set->name}");

        $cardsEn = isset($this->cardsBySetName[$normSetName])
            ? array_values($this->cardsBySetName[$normSetName])
            : [];

        if (empty($cardsEn)) {
            $this->warn("   -> Nenhuma carta encontrada no índice da YGOPRODeck para o set [{$setCode}] {$set->name}.");
            return;
        }

        // Busca sets irmãos de mesmo código base (ex: YS15, YS15-2, YS15-3) para limpar/mover prints que estavam misturados
        $basePrefix = explode('-', $setCode)[0];
        $siblingSetIds = Set::where('game_id', $this->game->id)
            ->where(function ($q) use ($basePrefix) {
                $q->where('code', $basePrefix)
                  ->orWhere('code', 'like', "{$basePrefix}-%");
            })
            ->pluck('id')
            ->toArray();

        $cardsProcessed = 0;
        $imagesDownloaded = 0;

        foreach ($cardsEn as $cardData) {
            try {
                $this->processSingleCard($cardData, $set, $siblingSetIds, $imagesDownloaded, $cardsProcessed);
                $this->output->write("\r   -> Prints processados: {$cardsProcessed} | Novas imagens baixadas: {$imagesDownloaded}", false);
            } catch (\Throwable $e) {
                $this->error("\nErro no Card {$cardData['id']}: " . $e->getMessage());
                Log::channel('ingest')->error("Erro Ingest YGO Card {$cardData['id']}: " . $e->getMessage());
            }
        }

        $this->output->writeln("");
    }

    protected function processSingleCard(array $cardData, Set $set, array $siblingSetIds, int &$imagesDownloaded, int &$cardsProcessed): void
    {
        $cardId = $cardData['id'];
        $normTargetSetName = $this->normalizeSetName($set->name);

        // Cotações de preços de mercado sugeridos
        $prices = [];
        if (!empty($cardData['card_prices'][0])) {
            $p = $cardData['card_prices'][0];
            $prices = [
                'cardmarket' => $p['cardmarket_price'] ?? null,
                'tcgplayer'  => $p['tcgplayer_price'] ?? null,
                'ebay'       => $p['ebay_price'] ?? null,
                'amazon'     => $p['amazon_price'] ?? null,
            ];
        }

        // 1. Conceito Específico (YgoConcept)
        $ygoConcept = YgoConcept::firstOrCreate(
            ['rules_text' => $cardData['desc'] ?? null, 'atk' => isset($cardData['atk']) ? (string)$cardData['atk'] : null],
            [
                'attribute'     => $cardData['attribute'] ?? null,
                'race'          => $cardData['race'] ?? null,
                'type_line'     => $cardData['type'] ?? null,
                'level'         => $cardData['level'] ?? null,
                'scale'         => $cardData['scale'] ?? null,
                'def'           => isset($cardData['def']) ? (string)$cardData['def'] : null,
                'pendulum_text' => null,
            ]
        );

        // 2. Conceito Universal (CatalogConcept)
        $catalogConcept = CatalogConcept::updateOrCreate(
            [
                'game_id' => $this->game->id,
                'name'    => $cardData['name'],
            ],
            [
                'slug'          => Str::slug($cardData['name']),
                'specific_type' => YgoConcept::class,
                'specific_id'   => $ygoConcept->id,
            ]
        );

        // 3. Itera sobre os prints físicos que pertencem especificamente a este Set (por set_name)
        $cardSets = $cardData['card_sets'] ?? [];
        $rawImageUrl = $cardData['card_images'][0]['image_url'] ?? null;

        foreach ($cardSets as $setInfo) {
            $infoSetName = $this->normalizeSetName($setInfo['set_name'] ?? '');
            if ($infoSetName !== $normTargetSetName) {
                continue;
            }

            $baseSetCode = $setInfo['set_code'] ?? '';
            $rarityName = $setInfo['set_rarity'] ?? 'Common';
            $rarityCode = trim(str_replace(['(', ')'], '', $setInfo['set_rarity_code'] ?? 'C'));

            // Gera as variações de idioma disponíveis
            foreach ($this->supportedLanguages as $lang => $apiParam) {
                $loc = ($lang === 'en') ? $cardData : ($this->localizedById[$lang][$cardId] ?? null);

                // Se não há dados cadastrados nesse idioma para a carta, pula
                if (!$loc && $lang !== 'en') {
                    continue;
                }

                $printedName = $loc['name'] ?? $cardData['name'];
                $printedText = $loc['desc'] ?? $cardData['desc'] ?? null;
                $printedType = $loc['type'] ?? $cardData['type'] ?? null;

                // Converte o collector_number (ex: YSKR-EN011 -> YSKR-PT011 e YS15-ENY01 -> YS15-PTY01)
                $localizedCollectorNum = $this->buildLocalizedCollectorNumber($baseSetCode, $lang);

                // Download ou reaproveitamento da imagem local
                $localImagePath = null;
                if ($rawImageUrl) {
                    $localImagePath = $this->downloadImage($rawImageUrl, $set->code, $localizedCollectorNum, $lang, $isNewDownload);
                    if ($isNewDownload) {
                        $imagesDownloaded++;
                    }
                }

                // Salva YgoPrint
                $ygoPrint = YgoPrint::updateOrCreate(
                    [
                        'api_id'           => (string)$cardId,
                        'collector_number' => $localizedCollectorNum,
                        'language_code'    => $lang,
                    ],
                    [
                        'rarity'            => $rarityName,
                        'rarity_code'       => $rarityCode,
                        'printed_name'      => $printedName,
                        'frame_type'        => $cardData['frameType'] ?? null,
                        'printed_text'      => $printedText,
                        'printed_type_line' => $printedType,
                        'flavor_text'       => null,
                        'prices'            => $prices,
                    ]
                );

                // Salva CatalogPrint
                $catalogPrintData = [
                    'set_id'           => $set->id,
                    'printed_name'     => $printedName,
                    'collector_number' => $localizedCollectorNum,
                    'rarity'           => $rarityName,
                    'type_line'        => $cardData['type'] ?? null,
                    'language_code'    => $lang,
                    'specific_type'    => YgoPrint::class,
                    'specific_id'      => $ygoPrint->id,
                ];

                if ($localImagePath) {
                    $catalogPrintData['image_path'] = $localImagePath;
                }

                // Se esse exato print já existia em um set irmão (ex: YS15 quando era de YS15-2), realoca para este Set
                $existingPrint = CatalogPrint::whereIn('set_id', $siblingSetIds)
                    ->where('concept_id', $catalogConcept->id)
                    ->where('collector_number', $localizedCollectorNum)
                    ->first();

                if ($existingPrint) {
                    $existingPrint->update($catalogPrintData);
                } else {
                    CatalogPrint::create(array_merge(
                        ['concept_id' => $catalogConcept->id],
                        $catalogPrintData
                    ));
                }

                $cardsProcessed++;
            }
        }
    }

    protected function buildLocalizedCollectorNumber(string $baseCode, string $targetLang): string
    {
        $targetSuffix = $this->langCodeMap[$targetLang] ?? 'EN';

        $parts = explode('-', $baseCode, 2);
        if (count($parts) < 2) {
            return $baseCode;
        }

        $prefix = $parts[0];
        $rest = $parts[1];

        // 1. Caso padrão com código de idioma EN/E explícito + sub-prefixo opcional (ex: EN011, ENY37, END01, ENF00, E001)
        if (preg_match('/^(?:EN|E)([A-Z]?\d+[A-Z]?)$/i', $rest, $matches)) {
            return "{$prefix}-{$targetSuffix}{$matches[1]}";
        }

        // 2. Outros códigos de 2 letras + dígitos (ex: NA001, AE001)
        if (preg_match('/^[A-Z]{1,2}([A-Z]?\d+[A-Z]?)$/i', $rest, $matches)) {
            return "{$prefix}-{$targetSuffix}{$matches[1]}";
        }

        // 3. Puro número ou letra+número antigo (ex: SDK-001, LOB-001,JMP-001)
        if (preg_match('/^\d+[A-Z]?$/i', $rest)) {
            return ($targetLang === 'en') ? $baseCode : "{$prefix}-{$targetSuffix}{$rest}";
        }

        return ($targetLang === 'en') ? $baseCode : "{$prefix}-{$targetSuffix}{$rest}";
    }

    protected function downloadImage(string $url, string $setCode, string $collectorNum, string $lang, ?bool &$isNewDownload = false): ?string
    {
        $isNewDownload = false;
        $safeCollectorNum = Str::slug($collectorNum);

        // Verifica primeiro se já existe na pasta do código base (ex: YS15 quando o set é YS15-2)
        $basePrefix = explode('-', $setCode)[0];
        if ($basePrefix !== $setCode) {
            $baseFileName = "{$basePrefix}_{$lang}_{$safeCollectorNum}.jpg";
            $baseRelPath = "card_images/Yugioh/{$basePrefix}/{$lang}/{$baseFileName}";
            if (File::exists(public_path($baseRelPath))) {
                return $baseRelPath;
            }
        }

        $fileName = "{$setCode}_{$lang}_{$safeCollectorNum}.jpg";
        $relativePath = "card_images/Yugioh/{$setCode}/{$lang}/{$fileName}";
        $fullPath = public_path($relativePath);

        if (File::exists($fullPath)) {
            return $relativePath;
        }

        // Se a imagem EN já existe localmente no mesmo set ou no basePrefix, podemos reaproveitá-la sem gastar rede
        // (já que a YGOPRODeck envia a mesma URL de arte para todas as línguas)
        $enCollectorNum = Str::slug($this->buildLocalizedCollectorNumber($collectorNum, 'en'));
        foreach ([$setCode, $basePrefix] as $candidateSet) {
            $enRelPath = "card_images/Yugioh/{$candidateSet}/en/{$candidateSet}_en_{$enCollectorNum}.jpg";
            $enFullPath = public_path($enRelPath);
            if (File::exists($enFullPath)) {
                File::ensureDirectoryExists(dirname($fullPath));
                File::copy($enFullPath, $fullPath);
                return $relativePath;
            }
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'VersusTCG/1.0 (https://versustcg.com.br; contato@versustcg.com.br)',
                'Accept'     => 'image/jpeg,image/*,*/*'
            ])->timeout(30)->get($url);

            if ($response->successful()) {
                File::ensureDirectoryExists(dirname($fullPath));
                File::put($fullPath, $response->body());
                $isNewDownload = true;
                usleep(100000);
                return $relativePath;
            } else {
                Log::channel('ingest')->warning("Falha HTTP {$response->status()} ao baixar imagem YGO: {$url}");
            }
        } catch (\Throwable $e) {
            Log::channel('ingest')->error("Erro ao baixar imagem YGO {$fileName}: " . $e->getMessage());
        }

        return null;
    }

    protected function setCheckpoint(int $id): void { File::put($this->checkpointPath, $id); }
    protected function getCheckpoint(): ?int { return File::exists($this->checkpointPath) ? (int)File::get($this->checkpointPath) : null; }
    protected function clearCheckpoint(): void { if (File::exists($this->checkpointPath)) File::delete($this->checkpointPath); }
}