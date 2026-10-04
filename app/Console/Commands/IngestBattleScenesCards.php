<?php

namespace App\Console\Commands;

use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\Games\BattleScenes\BsConcept;
use App\Models\Games\BattleScenes\BsPrint;
use App\Models\Game;
use App\Models\Set;
use App\Services\BattleScenesScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IngestBattleScenesCards extends Command
{
    protected $signature = 'battlescenes:ingest-cards 
                            {--set= : ID ou código/api_id do Set específico (opcional)}
                            {--limit= : Limite de sets a processar}';

    protected $description = 'Ingere cartas de Battle Scenes com resolução automática de imagens locais do disco';

    protected int $gameId = 4;

    /**
     * Mapeamento entre o código do set no banco e o diretório físico das imagens.
     */
    protected array $setFolderMap = [
        'AEQ'   => 'aq',
        'AEQD'  => 'aqd',
        'BBCI'  => 'bb1',
        'BBCN'  => 'bb2',
        'BBEDU' => 'bb3',
        'BBGIA' => 'bb4',
        'BR'    => 'br',
        'CA'    => 'ca',
        'CAD'   => 'cad',
        'DS'    => 'ds',
        'DSD'   => 'dsd',
        'ET'    => 'et',
        'ETD'   => 'etd',
        'FE'    => 'fe',
        'FED'   => 'fed',
        'GC'    => 'gc',
        'GCD'   => 'gcd',
        'IC'    => 'ic',
        'ICD'   => 'icd',
        'IV'    => 'iv',
        'IVD'   => 'ivd',
        'MBCA'  => 'mbca',
        'MBFE'  => 'mbfe',
        'MI'    => 'mi',
        'MID'   => 'mid',
        'OS'    => 'os',
        'OSD'   => 'osd',
        'P'     => 'promo',
        'PO'    => 'po',
        'POD'   => 'pod',
        'UM'    => 'um',
    ];

    public function handle(BattleScenesScraper $scraper): int
    {
        $this->info('=== INICIANDO INGESTÃO DE CARTAS: BATTLE SCENES ===');

        $game = Game::find($this->gameId);
        if (!$game) {
            $this->error("Game ID {$this->gameId} (Battle Scenes) não encontrado.");
            return self::FAILURE;
        }

        $query = Set::where('game_id', $this->gameId);

        if ($setFilter = $this->option('set')) {
            $query->where(function ($q) use ($setFilter) {
                $q->where('id', $setFilter)
                  ->orWhere('code', $setFilter)
                  ->orWhere('api_id', $setFilter)
                  ->orWhere('name', $setFilter);
            });
        }

        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $sets = $query->get();

        if ($sets->isEmpty()) {
            $this->error('Nenhum set de Battle Scenes encontrado com os critérios fornecidos.');
            return self::FAILURE;
        }

        $this->info("Sets para processar: {$sets->count()}");

        foreach ($sets as $set) {
            $this->processSet($set, $scraper);
        }

        $this->info('=== INGESTÃO DE BATTLE SCENES CONCLUÍDA COM SUCESSO ===');
        return self::SUCCESS;
    }

    protected function processSet(Set $set, BattleScenesScraper $scraper): void
    {
        $this->line("\n--------------------------------------------------");
        $this->info("Processando Set: [{$set->id}] {$set->name} (API ID: {$set->api_id} | Code: {$set->code})");

        $searchIdentifier = $set->api_id ?: $set->name;

        try {
            $cardsGenerator = $scraper->scrapeCardsForSet($searchIdentifier, $set->name);
        } catch (\Throwable $e) {
            $this->error("Erro ao iniciar raspagem para o set '{$set->name}': " . $e->getMessage());
            Log::error("BS Ingest Error (Set {$set->id}): " . $e->getMessage());
            return;
        }

        $count = 0;

        foreach ($cardsGenerator as $cardData) {
            $name = trim($cardData['name'] ?? '');
            if (empty($name)) {
                continue;
            }

            try {
                DB::transaction(function () use ($cardData, $name, $set) {
                    $this->ingestCard($cardData, $name, $set);
                });
                $count++;
                $this->output->write('.');
            } catch (\Throwable $e) {
                $this->newLine();
                $this->error("Erro ao salvar carta '{$name}': " . $e->getMessage());
                Log::error("BS Ingest Card Error ({$name}): " . $e->getMessage(), ['exception' => $e]);
            }
        }

        $this->newLine();
        $this->info("Set '{$set->name}' finalizado. Total de cartas ingeridas/atualizadas: {$count}");

        $totalPrints = CatalogPrint::where('set_id', $set->id)->count();
        $set->update(['card_count' => $totalPrints]);
    }

    protected function ingestCard(array $data, string $name, Set $set): void
    {
        $collectorNumber = (string) ($data['collection_number'] ?? $data['bs_collection_number'] ?? '');
        $rarity = !empty($data['bs_rarity']) ? trim($data['bs_rarity']) : 'Comum';
        $typeLine = $data['bs_type_line'] ?? 'Personagem';
        $rulesText = $data['bs_rules_text'] ?? null;
        $flavorText = $data['bs_flavor_text'] ?? null;
        $artist = $data['bs_artist'] ?? null;
        $power = isset($data['bs_power']) && is_numeric($data['bs_power']) ? (int) $data['bs_power'] : null;
        $toughness = isset($data['bs_toughness']) && is_numeric($data['bs_toughness']) ? (int) $data['bs_toughness'] : null;
        $cost = isset($data['bs_cost']) && is_numeric($data['bs_cost']) ? (int) $data['bs_cost'] : null;
        $affiliation = $data['bs_affiliation'] ?? null;
        $alterEgo = $data['bs_alter_ego'] ?? null;

        $conceptSlug = Str::slug($name);

        // Resolução da Imagem Local
        $imageRelativePath = $this->resolveLocalImagePath($set, $conceptSlug);

        // Fallback: se não achar localmente e a imagem do scraper não for a logo BattleScenes.png
        if (!$imageRelativePath) {
            $scrapedImage = $data['image_url'] ?? null;
            if ($scrapedImage && !Str::contains($scrapedImage, 'BattleScenes.png')) {
                $imageRelativePath = $scrapedImage;
            }
        }

        $affiliations = $affiliation ? array_map('trim', explode('/', $affiliation)) : [];

        // 1. GRAVAÇÃO ESPECÍFICA: bs_concepts
        $catalogConcept = CatalogConcept::where('game_id', $this->gameId)
            ->where('slug', $conceptSlug)
            ->first();

        if ($catalogConcept && $catalogConcept->specific_id) {
            $bsConcept = BsConcept::find($catalogConcept->specific_id);
            if ($bsConcept) {
                $bsConcept->update(array_filter([
                    'alter_ego'    => $alterEgo,
                    'type_line'    => $typeLine,
                    'affiliation'  => $affiliation,
                    'affiliations' => !empty($affiliations) ? $affiliations : null,
                    'power'        => $power,
                    'toughness'    => $toughness,
                    'cost'         => $cost,
                    'rules_text'   => $rulesText,
                    'flavor_text'  => $flavorText,
                ], fn($v) => !is_null($v)));
            }
        }

        if (empty($bsConcept)) {
            $bsConcept = BsConcept::create([
                'alter_ego'    => $alterEgo,
                'type_line'    => $typeLine,
                'affiliation'  => $affiliation,
                'affiliations' => $affiliations,
                'power'        => $power,
                'toughness'    => $toughness,
                'cost'         => $cost,
                'rules_text'   => $rulesText,
                'flavor_text'  => $flavorText,
                'skills'       => [],
            ]);
        }

        // 2. CONEXÃO POLIMÓRFICA: catalog_concepts
        if (!$catalogConcept) {
            $catalogConcept = CatalogConcept::create([
                'game_id'       => $this->gameId,
                'name'          => $name,
                'slug'          => $conceptSlug,
                'specific_type' => BsConcept::class,
                'specific_id'   => $bsConcept->id,
                'is_valid'      => true,
            ]);
        } else {
            $catalogConcept->update([
                'specific_type' => BsConcept::class,
                'specific_id'   => $bsConcept->id,
                'is_valid'      => true,
            ]);
        }

        // 3. GRAVAÇÃO ESPECÍFICA: bs_prints
        $imagesPayload = $imageRelativePath ? [
            'normal' => $imageRelativePath,
            'small'  => $imageRelativePath,
        ] : [];

        $catalogPrint = CatalogPrint::where('concept_id', $catalogConcept->id)
            ->where('set_id', $set->id)
            ->where('collector_number', $collectorNumber)
            ->first();

        if ($catalogPrint && $catalogPrint->specific_id) {
            $bsPrint = BsPrint::find($catalogPrint->specific_id);
            if ($bsPrint) {
                $bsPrint->update(array_filter([
                    'rarity'        => $rarity,
                    'artist'        => $artist,
                    'number'        => $collectorNumber,
                    'flavor_text'   => $flavorText,
                    'language_code' => 'pt',
                    'images'        => !empty($imagesPayload) ? $imagesPayload : null,
                ], fn($v) => !is_null($v)));
            }
        }

        if (empty($bsPrint)) {
            $bsPrint = BsPrint::create([
                'rarity'        => $rarity,
                'artist'        => $artist,
                'number'        => $collectorNumber,
                'flavor_text'   => $flavorText,
                'language_code' => 'pt',
                'images'        => $imagesPayload,
                'market_prices' => [],
            ]);
        }

        // 4. CONEXÃO POLIMÓRFICA: catalog_prints
        $cmcValue = $cost !== null ? (float) $cost : 0.00;
        $manaCost = $cost !== null && $cost > 0 ? (string) $cost : null;

        if (!$catalogPrint) {
            CatalogPrint::create([
                'concept_id'       => $catalogConcept->id,
                'set_id'           => $set->id,
                'collector_number' => $collectorNumber,
                'rarity'           => Str::lower($rarity),
                'type_line'        => $typeLine,
                'cmc'              => $cmcValue,
                'mana_cost'        => $manaCost,
                'image_path'       => $imageRelativePath,
                'printed_name'     => $name,
                'language_code'    => 'pt',
                'specific_type'    => BsPrint::class,
                'specific_id'      => $bsPrint->id,
                'is_valid'         => true,
            ]);
        } else {
            $catalogPrint->update([
                'rarity'           => Str::lower($rarity),
                'type_line'        => $typeLine,
                'cmc'              => $cmcValue,
                'mana_cost'        => $manaCost,
                'image_path'       => $imageRelativePath ?: $catalogPrint->image_path,
                'printed_name'     => $name,
                'specific_type'    => BsPrint::class,
                'specific_id'      => $bsPrint->id,
                'is_valid'         => true,
            ]);
        }
    }

    protected function resolveLocalImagePath(Set $set, string $slug): ?string
    {
        $folder = $this->setFolderMap[$set->code] ?? strtolower($set->code);

        $possibleExtensions = ['png', 'jpg', 'webp'];

        foreach ($possibleExtensions as $ext) {
            $relativePath = "card_images/BattleScenes/{$folder}/{$slug}.{$ext}";
            $fullPath = public_path($relativePath);

            if (File::exists($fullPath)) {
                return $relativePath;
            }
        }

        return null;
    }
}
