<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Set;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\Games\OnePiece\OpConcept;
use App\Models\Games\OnePiece\OpPrint;
use App\Services\OnePieceApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IngestOnePieceCards extends Command
{
    protected $signature = 'onepiece:ingest-cards
                            {--set-code= : Código da coleção (ex: OP-01, OP-09, ST-15)}
                            {--force : Sobrescreve imagens locais e ignora checkpoint}
                            {--resume : Força a leitura do checkpoint}';

    protected $description = 'Ingere cartas, textos oficiais traduzidos e imagens multilíngues de One Piece Card Game';

    protected ?Game $game = null;
    protected string $checkpointPath;
    protected array $conceptCache = [];
    protected array $jaCardsData = [];
    protected array $frCardsData = [];
    protected array $zhCardsData = [];
    protected array $koCardsData = [];

    // CDNs e diretórios oficiais por idioma
    protected array $langBaseUrls = [
        'en' => ['url' => 'https://en.onepiece-cardgame.com/images/cardlist/card/', 'ext' => 'png'],
        'ja' => ['url' => 'https://www.onepiece-cardgame.com/images/cardlist/card/', 'ext' => 'png'],
        'zh' => ['url' => 'https://www.onepiece-cardgame.cn/images/cardlist/card/', 'ext' => 'png'],
        'ko' => ['url' => 'https://www.onepiece-cardgame.kr/images/cardlist/card/', 'ext' => 'png'],
        'fr' => ['url' => 'https://fr.onepiece-cardgame.com/images/cardlist/card/', 'ext' => 'webp'],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->checkpointPath = storage_path('app/onepiece_cards_checkpoint.txt');
    }

    public function handle(): int
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $this->info("=== INGESTÃO DE CARTAS E TEXTOS MULTILÍNGUES: ONE PIECE ===");

        $this->game = Game::find(5);
        if (!$this->game) {
            $this->error("Game ID 5 (One Piece Card Game) não encontrado.");
            return self::FAILURE;
        }

        $apiService = new OnePieceApiService(
            (string) ($this->game->api_url ?? 'https://optcgapi.com'),
            (int) ($this->game->rate_limit_ms ?? 100),
            $this->game->id,
            (string) ($this->game->url_slug ?? 'one-piece')
        );

        $setsQuery = Set::where('game_id', $this->game->id)->orderBy('id', 'asc');

        if ($setCode = $this->option('set-code')) {
            $setsQuery->where('code', strtoupper($setCode));
            $this->info("Modo Set Único: [{$setCode}]");
        } else {
            $lastSetId = $this->getCheckpoint();
            if ($lastSetId && !$this->option('force') && $this->option('resume')) {
                $setsQuery->where('id', '>', $lastSetId);
                $this->info("Retomando a partir do Set ID: {$lastSetId}");
            }
        }

        $sets = $setsQuery->get();
        if ($sets->isEmpty()) {
            $this->warn("Nenhum set encontrado.");
            return self::SUCCESS;
        }

        foreach ($sets as $set) {
            $this->processSet($set, $apiService);

            if (!$this->option('set-code')) {
                $this->setCheckpoint($set->id);
            }

            gc_collect_cycles();
        }

        if (!$this->option('set-code')) {
            $this->clearCheckpoint();
        }

        $this->info("\n=== Ingestão Multilíngue de One Piece Concluída ===");
        return self::SUCCESS;
    }

    protected function processSet(Set $set, OnePieceApiService $apiService): void
    {
        $this->output->writeln("\n-------------------------------------------------------");
        $this->output->writeln("Processando Set: [{$set->code}] {$set->name}");

        // Pré-carrega cardlists oficiais dos idiomas suportados
        $this->fetchJapaneseCardsForSet($set->code);
        $this->fetchFrenchCardsForSet($set->code);
        $this->fetchChineseCardsForSet($set->code);
        // zht desativado (nao e idioma fisico)
        $this->fetchKoreanCardsForSet($set->code);

        $endpoint = Str::startsWith($set->code, 'ST')
            ? "/api/decks/{$set->code}/"
            : "/api/sets/{$set->code}/";

        $cards = $apiService->makeRequest($endpoint);

        if (empty($cards) || !is_array($cards)) {
            $this->warn("   -> Nenhuma carta retornada da API base para {$set->code}.");
            return;
        }

        $total = count($cards);
        $processed = 0;
        $imagesCount = 0;

        foreach ($cards as $cardData) {
            $success = $this->ingestCard($cardData, $set, $imagesCount);
            if ($success) {
                $processed++;
            }

            if ($processed % 5 === 0 || $processed === $total) {
                $this->output->write("\r   -> Cartas: {$processed}/{$total} | Imagens baixadas: {$imagesCount}   ");
            }
        }

        $this->output->writeln("\n   -> Concluído: {$processed}/{$total} registros base processados.");
        $set->update(['card_count' => CatalogPrint::where('set_id', $set->id)->count()]);
    }

    protected function ingestCard(array $data, Set $set, int &$imagesCount): bool
    {
        return DB::transaction(function () use ($data, $set, &$imagesCount) {
            try {
                $cardName = trim($data['card_name'] ?? 'Unknown');
                $rawCode = trim((string) ($data['card_set_id'] ?? $data['card_image_id'] ?? ''));
                if (empty($rawCode)) {
                    return false;
                }

                $collectorNumber = (string) ($data['card_image_id'] ?? $data['card_set_id'] ?? $rawCode);
                $rarity = $data['rarity'] ?? 'Common';
                $cardType = $data['card_type'] ?? 'Character';

                $isParallel = str_contains(strtolower($collectorNumber), '_p') ||
                              str_contains(strtolower($data['rarity'] ?? ''), 'parallel') ||
                              (isset($data['is_parallel']) && $data['is_parallel']);

                $variation = $isParallel ? 'parallel' : 'standard';

                // Código base (ex: OP01-077)
                $baseCardCode = explode('_', $rawCode)[0];
                $conceptKey = $baseCardCode;

                $jaData = $this->jaCardsData[$baseCardCode] ?? null;
                $frData = $this->frCardsData[$collectorNumber] ?? $this->frCardsData[$baseCardCode] ?? null;
                $zhData = $this->zhCardsData[$collectorNumber] ?? $this->zhCardsData[$baseCardCode] ?? null;
                
                
                // KO lookup: tenta número completo (com variação) ou base
                $koData = $this->koCardsData[$collectorNumber] ?? 
                          $this->koCardsData[strtoupper($collectorNumber)] ?? 
                          $this->koCardsData[strtolower($collectorNumber)] ?? 
                          $this->koCardsData[$baseCardCode] ?? null;

                // 1. CONCEITO BASE (OpConcept + CatalogConcept)
                $catalogConcept = null;
                if (isset($this->conceptCache[$conceptKey])) {
                    $catalogConcept = CatalogConcept::find($this->conceptCache[$conceptKey]);
                }

                $colors = !empty($data['card_color']) ? array_map('trim', explode('/', $data['card_color'])) : [];
                $subTypes = !empty($data['sub_types']) ? array_map('trim', explode('/', $data['sub_types'])) : [];

                $cost = (isset($data['card_cost']) && is_numeric($data['card_cost'])) ? (int) $data['card_cost'] : null;
                $power = (isset($data['card_power']) && is_numeric($data['card_power'])) ? (int) $data['card_power'] : null;
                $counter = (isset($data['counter_amount']) && is_numeric($data['counter_amount'])) ? (int) $data['counter_amount'] : null;
                $life = (isset($data['life']) && is_numeric($data['life'])) ? (int) $data['life'] : null;

                $opConcept = OpConcept::firstOrCreate(
                    ['card_code' => $baseCardCode],
                    [
                        'name'         => $cardName,
                        'name_ja'      => $jaData['name'] ?? null,
                        'card_type'    => $cardType,
                        'type_line'    => !empty($subTypes) ? implode(' / ', $subTypes) : $cardType,
                        'colors'       => $colors,
                        'attribute'    => $data['attribute'] ?? null,
                        'cost'         => $cost,
                        'life'         => $life,
                        'power'        => $power,
                        'counter'      => $counter,
                        'oracle_text'  => $data['card_text'] ?? null,
                        'trigger_text' => $data['trigger_text'] ?? null,
                        'keywords'     => [],
                        'families'     => $subTypes,
                        'legalities'   => ['standard' => 'legal'],
                    ]
                );

                if ($jaData && empty($opConcept->name_ja)) {
                    $opConcept->update(['name_ja' => $jaData['name']]);
                }

                if (!$catalogConcept) {
                    $conceptSlug = Str::slug($cardName . '-' . $baseCardCode);
                    $catalogConcept = CatalogConcept::firstOrCreate(
                        [
                            'game_id' => $this->game->id,
                            'slug'    => $conceptSlug,
                        ],
                        [
                            'name'          => $cardName,
                            'specific_type' => OpConcept::class,
                            'specific_id'   => $opConcept->id,
                        ]
                    );

                    $this->conceptCache[$conceptKey] = $catalogConcept->id;
                }

                $costVal = $cost !== null ? (float) $cost : 0.00;

                // 2. PROCESSAR PRINTS POR IDIOMA COM TRADUÇÕES E IMAGENS
                foreach ($this->langBaseUrls as $lang => $cfg) {
                    $imageUrl = $cfg['url'] . $collectorNumber . '.' . $cfg['ext'];

                    // Se for ZH e tivermos o link direto de alta definição da API chinesa
                    if ($lang === 'zh' && !empty($zhData['image_url'])) {
                        $imageUrl = $zhData['image_url'];
                    }

                    // Se for KO e tivermos a URL oficial do portal coreano
                    if ($lang === 'ko' && !empty($koData['image_url'])) {
                        $imageUrl = $koData['image_url'];
                    }

                    $downloadResult = $this->downloadImage($imageUrl, $set->code, ($lang === 'zh' ? 'zhs' : $lang), $collectorNumber, $cfg['ext']);

                    if (!$downloadResult && $lang === 'en' && !empty($data['card_image'])) {
                        $downloadResult = $this->downloadImage($data['card_image'], $set->code, ($lang === 'zh' ? 'zhs' : $lang), $collectorNumber, 'png');
                    }

                    if ($downloadResult) {
                        $imagesCount++;
                    }

                    // Se não tiver imagem nem dados oficiais desse idioma e não for 'en', ignora
                    $hasLangData = ($lang === 'ja' && $jaData) ||
                                   ($lang === 'fr' && $frData) ||
                                   ($lang === 'zh' && $zhData) ||
                                   
                                   ($lang === 'ko' && $koData);

                    if (!$downloadResult && $lang !== 'en' && !$hasLangData) {
                        continue;
                    }

                    // Textos traduzidos de acordo com a língua
                    if ($lang === 'ja' && $jaData) {
                        $pName = $jaData['name'];
                        $pTypeLine = $jaData['type_line'] ?: (!empty($subTypes) ? implode(' / ', $subTypes) : $cardType);
                        $pText = $jaData['text'];
                        $pTrigger = $jaData['trigger'];
                    } elseif ($lang === 'fr' && $frData) {
                        $pName = $frData['name'];
                        $pTypeLine = $frData['type_line'] ?: (!empty($subTypes) ? implode(' / ', $subTypes) : $cardType);
                        $pText = $frData['text'];
                        $pTrigger = $frData['trigger'];
                    } elseif ($lang === 'zh' && $zhData) {
                        $pName = $zhData['name'];
                        $pTypeLine = $zhData['type_line'] ?: (!empty($subTypes) ? implode(' / ', $subTypes) : $cardType);
                        $pText = $zhData['text'];
                        $pTrigger = $zhData['trigger'];
                    } elseif ($lang === 'ko' && $koData) {
                        $pName = $koData['name'];
                        $pTypeLine = $koData['type_line'] ?: (!empty($subTypes) ? implode(' / ', $subTypes) : $cardType);
                        $pText = $koData['text'];
                        $pTrigger = $koData['trigger'];

                    } else {
                        $pName = $cardName;
                        $pTypeLine = !empty($subTypes) ? implode(' / ', $subTypes) : $cardType;
                        $pText = $data['card_text'] ?? null;
                        $pTrigger = $data['trigger_text'] ?? null;
                    }

                    $opPrint = OpPrint::firstOrCreate(
                        [
                            'concept_id'       => $opConcept->id,
                            'collector_number' => $collectorNumber,
                            'language_code'    => ($lang === 'zh' ? 'zhs' : $lang),
                        ],
                        [
                            'api_id'               => $rawCode,
                            'rarity'               => $rarity,
                            'artist'               => $data['illustrator'] ?? $data['artist'] ?? null,
                            'flavor_text'          => $data['flavor_text'] ?? null,
                            'full_art'             => $isParallel,
                            'has_foil'             => in_array(strtoupper($rarity), ['SR', 'SEC', 'L', 'SP']),
                            'textured'             => false,
                            'promo'                => Str::startsWith($set->code, 'P-') || $set->set_type === 'promo',
                            'reprint'              => false,
                            'variation'            => $variation,
                            'variation_of'         => $isParallel ? $baseCardCode : null,
                            'printed_name'         => $pName,
                            'printed_type_line'    => $pTypeLine,
                            'printed_text'         => $pText,
                            'printed_trigger_text' => $pTrigger,
                            'image_status'         => $downloadResult ? 'downloaded' : 'missing',
                        ]
                    );

                    $opPrint->update([
                        'printed_name'         => $pName,
                        'printed_type_line'    => $pTypeLine,
                        'printed_text'         => $pText,
                        'printed_trigger_text' => $pTrigger,
                        'image_status'         => $downloadResult ? 'downloaded' : $opPrint->image_status,
                    ]);

                    $catalogPrint = CatalogPrint::where('set_id', $set->id)
                        ->where('collector_number', $collectorNumber)
                        ->where('language_code', ($lang === 'zh' ? 'zhs' : $lang))
                        ->first();

                    if (!$catalogPrint) {
                        CatalogPrint::create([
                            'concept_id'       => $catalogConcept->id,
                            'set_id'           => $set->id,
                            'collector_number' => $collectorNumber,
                            'rarity'           => Str::lower($rarity),
                            'type_line'        => $cardType,
                            'cmc'              => $costVal,
                            'mana_cost'        => $costVal > 0 ? (string) $costVal : null,
                            'image_path'       => $downloadResult,
                            'printed_name'     => $pName,
                            'language_code'    => ($lang === 'zh' ? 'zhs' : $lang),
                            'specific_type'    => OpPrint::class,
                            'specific_id'      => $opPrint->id,
                        ]);
                    } else {
                        $catalogPrint->update([
                            'concept_id'   => $catalogConcept->id,
                            'printed_name' => $pName,
                            'image_path'   => $downloadResult ?: $catalogPrint->image_path,
                        ]);
                    }
                }

                return true;
            } catch (\Throwable $e) {
                Log::channel('ingest')->error("Erro ao salvar Carta One Piece {$collectorNumber}: " . $e->getMessage() . " em " . $e->getFile() . ":" . $e->getLine());
                return false;
            }
        });
    }

    protected function fetchJapaneseCardsForSet(string $setCode): void
    {
        $this->jaCardsData = [];

        $seriesId = null;
        if (preg_match('/OP-(\d+)/i', $setCode, $m)) {
            $seriesId = '5501' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        } elseif (preg_match('/ST-(\d+)/i', $setCode, $m)) {
            $seriesId = '5504' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        } elseif (preg_match('/EB-(\d+)/i', $setCode, $m)) {
            $seriesId = '5502' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }

        if (!$seriesId) {
            return;
        }

        try {
            $response = Http::timeout(20)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ])->withoutVerifying()->get("https://www.onepiece-cardgame.com/cardlist/?series={$seriesId}");

            if (!$response->successful()) {
                return;
            }

            $html = $response->body();
            preg_match_all('/<dl[^>]*class=[\"\'][^\"\']*modalCol[^\"\']*[\"\'][^>]*id=[\"\']([^\"\']+)[\"\'].*?<\/dl>/s', $html, $matches, PREG_SET_ORDER);

            foreach ($matches as $item) {
                $code = trim($item[1]);
                $block = $item[0];

                preg_match('/<div class=\"cardName\">([^<]+)<\/div>/', $block, $mName);
                $name = isset($mName[1]) ? trim(html_entity_decode($mName[1])) : null;

                preg_match('/<div class=\"feature\"><h3>特徴<\/h3>([^<]+)<\/div>/', $block, $mFeature);
                $feature = isset($mFeature[1]) ? trim(html_entity_decode($mFeature[1])) : null;

                preg_match('/<div class=\"text\"><h3>テキスト<\/h3>(.*?)<\/div>/s', $block, $mText);
                $text = isset($mText[1]) ? trim(strip_tags(str_replace('<br>', "\n", $mText[1]))) : null;

                preg_match('/<div class=\"trigger\"><h3>トリガー<\/h3>(.*?)<\/div>/s', $block, $mTrigger);
                $trigger = isset($mTrigger[1]) ? trim(strip_tags(str_replace('<br>', "\n", $mTrigger[1]))) : null;

                $this->jaCardsData[$code] = [
                    'name'      => $name,
                    'type_line' => $feature,
                    'text'      => $text,
                    'trigger'   => $trigger,
                ];
            }

            $totalJa = count($this->jaCardsData);
            if ($totalJa > 0) {
                $this->info("   -> Base japonesa oficial vinculada: {$totalJa} cartas traduzidas.");
            }
        } catch (\Throwable $e) {
            $this->warn("   -> Não foi possível carregar o catálogo em japonês: " . $e->getMessage());
        }
    }

    protected function fetchFrenchCardsForSet(string $setCode): void
    {
        $this->frCardsData = [];

        $seriesId = null;
        if (preg_match('/OP-(\d+)/i', $setCode, $m)) {
            $seriesId = '6221' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        } elseif (preg_match('/ST-(\d+)/i', $setCode, $m)) {
            $seriesId = '6220' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        } elseif (preg_match('/EB-(\d+)/i', $setCode, $m)) {
            $seriesId = '6222' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        } elseif (preg_match('/PRB-(\d+)/i', $setCode, $m)) {
            $seriesId = '6223' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }

        if (!$seriesId) {
            return;
        }

        try {
            $response = Http::timeout(20)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
            ])->withoutVerifying()->get("https://fr.onepiece-cardgame.com/cardlist/?series={$seriesId}");

            if (!$response->successful()) {
                return;
            }

            $html = $response->body();
            preg_match_all('/<dl[^>]*class=[\"\'][^\"\']*modalCol[^\"\']*[\"\'][^>]*id=[\"\']([^\"\']+)[\"\'].*?<\/dl>/s', $html, $matches, PREG_SET_ORDER);

            foreach ($matches as $item) {
                $code = trim($item[1]);
                $block = $item[0];

                preg_match('/<div class=\"cardName\">([^<]+)<\/div>/', $block, $mName);
                $name = isset($mName[1]) ? trim(html_entity_decode($mName[1])) : null;

                preg_match('/<div class=\"feature\">(?:<h3>[^<]*<\/h3>)?(.*?)<\/div>/s', $block, $mFeature);
                $feature = isset($mFeature[1]) ? trim(strip_tags(html_entity_decode($mFeature[1]))) : null;

                preg_match('/<div class=\"text\">(?:<h3>[^<]*<\/h3>)?(.*?)<\/div>/s', $block, $mText);
                $text = isset($mText[1]) ? trim(strip_tags(str_replace('<br>', "\n", html_entity_decode($mText[1])))) : null;

                preg_match('/<div class=\"trigger\">(?:<h3>[^<]*<\/h3>)?(.*?)<\/div>/s', $block, $mTrigger);
                $trigger = isset($mTrigger[1]) ? trim(strip_tags(str_replace('<br>', "\n", html_entity_decode($mTrigger[1])))) : null;

                $this->frCardsData[$code] = [
                    'name'      => $name,
                    'type_line' => $feature,
                    'text'      => $text,
                    'trigger'   => $trigger,
                ];
            }

            $totalFr = count($this->frCardsData);
            if ($totalFr > 0) {
                $this->info("   -> Base francesa oficial vinculada: {$totalFr} cartas traduzidas.");
            }
        } catch (\Throwable $e) {
            $this->warn("   -> Não foi possível carregar o catálogo em francês: " . $e->getMessage());
        }
    }

        protected function fetchTraditionalChineseCardsForSet(string $setCode): void
    {
        $this->zhtCardsData = [];

        $seriesId = null;
        if (preg_match('/OP-(\d+)/i', $setCode, $m)) {
            $seriesId = '5541' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        } elseif (preg_match('/ST-(\d+)/i', $setCode, $m)) {
            $seriesId = '5540' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        } elseif (preg_match('/EB-(\d+)/i', $setCode, $m)) {
            $seriesId = '5542' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        } elseif (preg_match('/PRB-(\d+)/i', $setCode, $m)) {
            $seriesId = '5543' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }

        if (!$seriesId) {
            return;
        }

        try {
            $response = Http::timeout(20)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            ])->withoutVerifying()->get("https://asia-tc.onepiece-cardgame.com/cardlist/?series={$seriesId}");

            if (!$response->successful()) {
                return;
            }

            $html = $response->body();
            preg_match_all('/<dl[^>]*class=[\"\'][^\"\']*modalCol[^\"\']*[\"\'][^>]*id=[\"\']([^\"\']+)[\"\'].*?<\/dl>/s', $html, $matches, PREG_SET_ORDER);

            foreach ($matches as $item) {
                $code = trim($item[1]);
                $block = $item[0];

                preg_match('/<div class=\"cardName\">([^<]+)<\/div>/', $block, $mName);
                $name = isset($mName[1]) ? trim(html_entity_decode($mName[1])) : null;

                preg_match('/<div class=\"feature\">(?:<h3>[^<]*<\/h3>)?(.*?)<\/div>/s', $block, $mFeature);
                $feature = isset($mFeature[1]) ? trim(strip_tags(html_entity_decode($mFeature[1]))) : null;

                preg_match('/<div class=\"text\">(?:<h3>[^<]*<\/h3>)?(.*?)<\/div>/s', $block, $mText);
                $text = isset($mText[1]) ? trim(strip_tags(str_replace('<br>', "\n", html_entity_decode($mText[1])))) : null;

                preg_match('/<div class=\"trigger\">(?:<h3>[^<]*<\/h3>)?(.*?)<\/div>/s', $block, $mTrigger);
                $trigger = isset($mTrigger[1]) ? trim(strip_tags(str_replace('<br>', "\n", html_entity_decode($mTrigger[1])))) : null;

                $this->zhtCardsData[$code] = [
                    'name'      => $name,
                    'type_line' => $feature,
                    'text'      => $text,
                    'trigger'   => $trigger,
                ];
            }

            $totalZht = count($this->zhtCardsData);
            if ($totalZht > 0) {
                $this->info("   -> Base chinesa tradicional (Taiwan) oficial vinculada: {$totalZht} cartas traduzidas.");
            }
        } catch (\Throwable $e) {
            $this->warn("   -> Não foi possível carregar o catálogo em chinês tradicional: " . $e->getMessage());
        }
    }

    protected function fetchChineseCardsForSet(string $setCode): void
    {
        $this->zhCardsData = [];

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Referer'    => 'https://www.onepiece-cardgame.cn/',
            'Accept'     => 'application/json, text/plain, */*',
        ];
        $baseUrl = 'https://webadmin.windoent.com/front/op-public';

        try {
            $cnCode = preg_replace('/^([A-Z]+)-?(\d+)/', '$1C-$2', strtoupper($setCode));

            $resOffers = Http::timeout(20)->withHeaders($headers)
                ->withoutVerifying()
                ->get("{$baseUrl}/cardType/cardofferingtype/cachelist");

            $offerList = $resOffers->json()['list'] ?? [];
            $targetOfferName = null;

            foreach ($offerList as $offer) {
                $offerName = $offer['name'] ?? '';
                if (str_contains($offerName, "【{$cnCode}】") || str_contains($offerName, "【{$setCode}】")) {
                    $targetOfferName = $offerName;
                    break;
                }
            }

            if (!$targetOfferName) {
                return;
            }

            $resp = Http::timeout(25)->withHeaders($headers)
                ->withoutVerifying()
                ->get("{$baseUrl}/cardList/cardlist/weblist", [
                    'cardOfferType' => $targetOfferName,
                    'page'          => 1,
                    'limit'         => 300,
                ]);

            $cardList = $resp->json()['page']['list'] ?? [];
            if (empty($cardList)) {
                return;
            }

            foreach ($cardList as $cardSummary) {
                $cardId = $cardSummary['id'] ?? null;
                if (!$cardId) {
                    continue;
                }

                $resDetail = Http::timeout(10)->withHeaders($headers)
                    ->withoutVerifying()
                    ->get("{$baseUrl}/cardList/cardlist/webInfo/{$cardId}");

                $c = $resDetail->json()['info'] ?? [];
                if (empty($c)) {
                    continue;
                }

                $rawCode = trim($c['cardNumber'] ?? '');
                if (empty($rawCode)) {
                    continue;
                }

                $isParallel = str_ends_with($rawCode, 'P');
                $baseCode = $isParallel ? substr($rawCode, 0, -1) : $rawCode;
                $collectorKey = $isParallel ? "{$baseCode}_p1" : $baseCode;

                $cardEntry = [
                    'name'      => $c['cardName'] ?? null,
                    'type_line' => $c['cardFeatures'] ?? null,
                    'text'      => $c['cardTextDesc'] ?? null,
                    'trigger'   => $c['cardTrigger'] ?? null,
                    'image_url' => $c['cardImg'] ?? null,
                ];

                $this->zhCardsData[$collectorKey] = $cardEntry;
                if (!$isParallel) {
                    $this->zhCardsData[$baseCode] = $cardEntry;
                }
            }

            $totalZh = count($this->zhCardsData);
            if ($totalZh > 0) {
                $this->info("   -> Base chinesa oficial vinculada: {$totalZh} cartas traduzidas.");
            }
        } catch (\Throwable $e) {
            $this->warn("   -> Não foi possível carregar o catálogo em chinês: " . $e->getMessage());
        }
    }

    protected function fetchKoreanCardsForSet(string $setCode): void
    {
        $this->koCardsData = [];

        // Mapeia código para a tag coreana: OP-01 -> [OPK-01], ST-01 -> [STK-01], EB-01 -> [EBK-01]
        $cleanSetCode = str_replace('-', '', strtoupper($setCode)); // Ex: OP01, ST01
        $koSetTag = preg_replace('/^([A-Z]+)-?(\d+)/', '[$1K-$2]', strtoupper($setCode)); // Ex: [OPK-01]

        $baseUrl = 'https://www.onepiece-cardgame.kr';

        try {
            $page = 0;
            $hasMore = true;

            while ($hasMore && $page < 15) {
                $url = "{$baseUrl}/cardlist.do?page={$page}&series=all&freewords={$cleanSetCode}";

                $response = Http::timeout(20)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Referer'    => $baseUrl,
                    'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ])->withoutVerifying()->get($url);

                if (!$response->successful()) {
                    break;
                }

                $html = $response->body();
                preg_match_all('~<button class="item">(.*?)</button>~s', $html, $items);
                $blocks = $items[1] ?? [];

                if (empty($blocks)) {
                    $hasMore = false;
                    break;
                }

                foreach ($blocks as $block) {
                    // FILTRO ESTRITO DE COLEÇÃO: descarta reprints, promos [P], ou cartas de outros sets
                    preg_match('~<p class="cardGet">([^<]+)~i', $block, $mGet);
                    $cardGet = trim($mGet[1] ?? '');

                    if (!str_contains($cardGet, $koSetTag)) {
                        continue; // Pertence a outro produto/set! Pula sumariamente.
                    }

                    preg_match('~<p class="cardNumber">([^<]+)~i', $block, $mCode);
                    preg_match('~<p class="cardName">([^<]+)~i', $block, $mName);
                    preg_match('~<p class="cardPoint">([^<]*)~i', $block, $mTypeLine);
                    preg_match('~<p class="cardText">([^<]*)~i', $block, $mText);
                    preg_match('~<p class="cardTrigger">([^<]*)~i', $block, $mTrigger);
                    preg_match('~<img[^>]*class="image"[^>]*src="([^"]+)"~i', $block, $mImg);

                    $rawCardCode = trim($mCode[1] ?? '');
                    if (empty($rawCardCode)) {
                        continue;
                    }

                    $imgRelUrl = trim($mImg[1] ?? '');
                    $fullImgUrl = $imgRelUrl ? (str_starts_with($imgRelUrl, 'http') ? $imgRelUrl : $baseUrl . $imgRelUrl) : null;

                    $entry = [
                        'name'      => trim($mName[1] ?? ''),
                        'type_line' => trim($mTypeLine[1] ?? ''),
                        'text'      => trim(str_replace(["\r", "\n", "\t"], ' ', $mText[1] ?? '')),
                        'trigger'   => trim(str_replace(["\r", "\n", "\t"], ' ', $mTrigger[1] ?? '')),
                        'image_url' => $fullImgUrl,
                    ];

                    // Indexa unicamente pelo código exato
                    $this->koCardsData[$rawCardCode] = $entry;
                }

                if (count($blocks) < 20) {
                    $hasMore = false;
                } else {
                    $page++;
                }
            }

            $totalKo = count($this->koCardsData);
            if ($totalKo > 0) {
                $this->info("   -> Base coreana oficial vinculada: {$totalKo} cartas estritas do set {$koSetTag}.");
            }
        } catch (\Throwable $e) {
            $this->warn("   -> Não foi possível carregar o catálogo em coreano: " . $e->getMessage());
        }
    }

    protected function downloadImage(string $url, string $setCode, string $lang, string $collectorNum, string $ext = 'png'): ?string
    {
        $safeCollectorNum = Str::slug($collectorNum);
        $fileName = "{$safeCollectorNum}.{$ext}";

        $relativePath = "card_images/OnePiece/{$setCode}/{$lang}/{$fileName}";
        $fullPath = public_path($relativePath);

        if (File::exists($fullPath) && !$this->option('force')) {
            return $relativePath;
        }

        try {
            File::ensureDirectoryExists(dirname($fullPath));

            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'Accept'     => 'image/webp,image/png,image/*,*/*'
            ];

            if ($lang === 'zh') {
                $headers['Referer'] = 'https://www.onepiece-cardgame.cn/';
            } elseif ($lang === 'ko') {
                $headers['Referer'] = 'https://www.onepiece-cardgame.kr/';
            }

            $response = Http::withHeaders($headers)->timeout(20)->sink($fullPath)->withoutVerifying()->get($url);

            if ($response->successful() && File::exists($fullPath) && File::size($fullPath) > 5120) {
                return $relativePath;
            }

            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        } catch (\Throwable $e) {
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }

        return null;
    }

    protected function setCheckpoint(int $id): void { File::put($this->checkpointPath, (string) $id); }
    protected function getCheckpoint(): ?int { return File::exists($this->checkpointPath) ? (int) File::get($this->checkpointPath) : null; }
    protected function clearCheckpoint(): void { if (File::exists($this->checkpointPath)) File::delete($this->checkpointPath); }
}
