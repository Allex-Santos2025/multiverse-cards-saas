<?php

namespace App\Services;

use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BattleScenesScraper
{
    protected string $searchPageUrl = 'https://www.magicjebb.com.br/site/busca_cards_bs.php';
    protected string $resultsUrl = 'https://www.magicjebb.com.br/site/busca_avancada_bs.php';
    protected string $detailUrlBase = 'https://www.magicjebb.com.br/site/';

    protected HttpBrowser $client;

    public function __construct()
    {
        $defaultOptions = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.0.0 Safari/537.36',
            ],
            'timeout' => 30,
        ];

        $this->client = new HttpBrowser(HttpClient::create($defaultOptions));
    }

    public function getSetsList(): array
    {
        Log::channel('ingest')->info('Iniciando raspagem de Sets (MagicJebb).');

        try {
            $crawler = $this->client->request('GET', $this->searchPageUrl);
            $sets = [];

            $selector = 'select[name="serie"] option';

            $crawler->filter($selector)->each(function (Crawler $node) use (&$sets) {
                $name = trim($node->text() ?? '');
                $code = trim($node->attr('value') ?? '');

                if (!empty($code)) {
                    $normalizedCode = Str::slug($name);
                    $sets[$code] = [
                        'name'          => $name,
                        'original_code' => $code,
                        'db_code'       => $normalizedCode,
                    ];
                }
            });

            return array_values($sets);

        } catch (\Exception $e) {
            Log::channel('ingest')->error('Erro ao buscar sets: ' . $e->getMessage());
            return [];
        }
    }

    public function scrapeCardsForSet(string $originalSetCode, string $setName): \Generator
    {
        Log::channel('ingest')->info("Iniciando raspagem de cards para o Set: {$setName}");
        $page = 1;

        $isoEncodedSet = mb_convert_encoding($originalSetCode, 'ISO-8859-1', 'UTF-8');
        $urlEncodedSetCode = urlencode($isoEncodedSet);

        do {
            $url = $this->resultsUrl . "?serie={$urlEncodedSetCode}&formato=detalhes&pag={$page}&exibicaobs=lista&enviar=Buscar";

            try {
                $crawler = $this->client->request('GET', $url);
            } catch (\Exception $e) {
                Log::channel('ingest')->error("Erro ao acessar {$url}: " . $e->getMessage());
                break;
            }

            $linksInPage = $crawler->filter('a[href*="detalhes_bs.php"], a[href*="bs_card.php"]');

            if ($linksInPage->count() === 0) {
                if ($page === 1) {
                    Log::channel('ingest')->warning("Nenhum link encontrado na pág 1. URL: {$url}");
                }
                break;
            }

            $seenLinksInPage = [];

            foreach ($linksInPage as $domElement) {
                $linkNode = new Crawler($domElement);

                try {
                    $detailHref = $linkNode->attr('href');
                    if (!$detailHref) {
                        continue;
                    }

                    // Se este URL exato já foi processado nesta página (ex: alter ego ou thumbnail), salta
                    if (isset($seenLinksInPage[$detailHref])) {
                        continue;
                    }
                    $seenLinksInPage[$detailHref] = true;

                    $linkText = trim($linkNode->text());

                    // Normaliza e codifica a query string para ISO-8859-1
                    $urlParts = parse_url($detailHref);
                    $path = $urlParts['path'] ?? 'detalhes_bs.php';
                    parse_str($urlParts['query'] ?? '', $queryParams);

                    $isoQuery = http_build_query(
                        array_map(fn($v) => mb_convert_encoding($v, 'ISO-8859-1', 'UTF-8'), $queryParams)
                    );

                    $fullDetailUrl = $this->detailUrlBase . $path . '?' . $isoQuery;

                    $detailData = $this->fetchCardDetailData($fullDetailUrl);

                    if ($detailData) {
                        $canonicalName = !empty($detailData['card_name']) 
                            ? $detailData['card_name'] 
                            : (!empty($queryParams['serie']) ? trim($queryParams['serie']) : $linkText);

                        if (empty($canonicalName)) {
                            continue;
                        }

                        yield array_merge([
                            'name'                 => $canonicalName,
                            'image_url'            => $detailData['image_url'],
                            'bs_collection_number' => $detailData['collection_number'],
                        ], $detailData);
                    }

                } catch (\Exception $e) {
                    // Silencioso
                }
            }

            $page++;
            usleep(250000);

        } while (true);
    }

    protected function fetchCardDetailData(string $detailUrl): ?array
    {
        try {
            $crawler = $this->client->request('GET', $detailUrl);

            $cardImg = $crawler->filter('table.cardDetalhes img.PopBoxImageLink')->first();
            if (!$cardImg->count()) {
                $cardImg = $crawler->filter('table.cardDetalhes img')->first();
            }

            $bestImage = null;
            if ($cardImg->count()) {
                $src = $cardImg->attr('src');
                if (!str_contains($src, 'BattleScenes.png')) {
                    $bestImage = $src;
                }
            }

            $data = [
                'bs_type_line'      => null,
                'bs_rarity'         => 'Comum',
                'collection_number' => null,
                'bs_artist'         => null,
                'bs_rules_text'     => null,
                'bs_flavor_text'    => null,
                'bs_power'          => null,
                'bs_toughness'      => null,
                'bs_cost'           => null,
                'bs_affiliation'    => null,
                'bs_alter_ego'      => null,
                'image_url'         => $bestImage,
            ];

            $crawler->filter('table.cardDetalhes tr')->each(function (Crawler $tr) use (&$data) {
                $tds = $tr->filter('td');
                if ($tds->count() >= 2) {
                    $label = trim($tds->eq(0)->text());
                    $val   = trim($tds->eq(1)->text());

                    if (str_starts_with($label, 'Nome:')) {
                        $data['card_name'] = $val;
                    } elseif (str_starts_with($label, 'Alter Ego:')) {
                        $data['bs_alter_ego'] = $val;
                    } elseif (str_starts_with($label, 'Tipo:')) {
                        $data['bs_type_line'] = $val;
                    } elseif (str_starts_with($label, 'Energia:')) {
                        $data['bs_power'] = preg_replace('/[^\d]/', '', $val);
                    } elseif (str_starts_with($label, 'Escudo:')) {
                        $data['bs_toughness'] = preg_replace('/[^\d]/', '', $val);
                    } elseif (str_starts_with($label, 'Afiliação:')) {
                        $data['bs_affiliation'] = $val;
                    } elseif (str_starts_with($label, 'Texto:')) {
                        $data['bs_rules_text'] = $val;
                    } elseif (str_starts_with($label, 'Raridade:')) {
                        $data['bs_rarity'] = $val;
                    } elseif (str_starts_with($label, 'Card:') || str_starts_with($label, 'Número:')) {
                        $data['collection_number'] = preg_replace('/[^\d]/', '', $val);
                    } elseif (str_starts_with($label, 'Ilustrador') || str_starts_with($label, 'Artista:')) {
                        $data['bs_artist'] = $val;
                    }
                }
            });

            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseTextData(string $text): array
    {
        return [];
    }
}
