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
                        'name' => $name,
                        'original_code' => $code,
                        'db_code' => $normalizedCode,
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
        $urlEncodedSetCode = urlencode($originalSetCode);

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

            foreach ($linksInPage as $domElement) {
                $linkNode = new Crawler($domElement);

                try {
                    $cardName = trim($linkNode->text());
                    $detailHref = $linkNode->attr('href');
                    
                    if (empty($cardName) || !$detailHref) continue;

                    $fullDetailUrl = $this->detailUrlBase . $detailHref;

                    $detailData = $this->fetchCardDetailData($fullDetailUrl);
                    
                    if ($detailData && !empty($detailData['text_blob'])) {
                        $parsedData = $this->parseTextData($detailData['text_blob']);
                        
                        $imageUrl = $detailData['image_url'];
                        $finalImageUrl = null;
                        
                        if ($imageUrl) {
                            $finalImageUrl = Str::startsWith($imageUrl, 'images/') 
                                ? 'http://www.magicjebb.com.br/site/' . $imageUrl 
                                : $imageUrl;
                        }

                        yield array_merge([
                            'name' => trim($cardName),
                            'image_url' => $finalImageUrl,
                            'bs_collection_number' => $parsedData['collection_number'], 
                        ], $parsedData);
                    }

                } catch (\Exception $e) {
                    // Log silencioso
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
            
            $images = $crawler->filter('img');
            $bestImage = null;
            $maxScore = -1000; // Score inicial baixo

            foreach ($images as $img) {
                $src = $img->getAttribute('src');
                $width = $img->getAttribute('width');
                $srcLower = strtolower($src);
                $currentScore = 0;

                // 1. Blacklist (Elimina lixo óbvio)
                if (Str::contains($srcLower, ['banner', 'titulo', 'spacer', 'transparente', 'shim', 'pixel', 'ponto', 'dot', 'blank', 'seta', 'linha'])) {
                    continue; 
                }

                // 2. SISTEMA DE PONTUAÇÃO (Foco no PNG)
                
                // EXTENSÃO: Se for PNG, ganha MUITOS pontos (conforme sua observação)
                if (Str::endsWith($srcLower, '.png')) {
                    $currentScore += 200; 
                }
                
                // LOCALIZAÇÃO: Se estiver nas pastas de cards
                if (Str::contains($srcLower, ['bs_cards/', 'scan/', 'cards/', 'scans/'])) {
                    $currentScore += 100;
                }

                // PENALIDADES
                // Ícones de layout/poderes costumam ser GIF ou JPG pequenos, ou ter nomes suspeitos
                if (Str::contains($srcLower, ['icone', 'icon', 'simbolo', 'poder', 'habilidade', 'mini', 'botoes'])) {
                    $currentScore -= 100;
                }
                if (Str::endsWith($srcLower, '.gif')) {
                    $currentScore -= 50; 
                }
                // Se for muito pequeno e a largura estiver definida
                if (is_numeric($width) && (int)$width < 100) {
                    $currentScore -= 100;
                }

                // Escolhe o vencedor
                if ($currentScore > $maxScore) {
                    $maxScore = $currentScore;
                    $bestImage = $src;
                }
            }

            // Fallback: Se não achou nada bom, tenta a primeira imagem da tabela
            if (!$bestImage) {
                $tableImg = $crawler->filter('table[width="550"] img')->first();
                if ($tableImg->count() > 0) {
                    $bestImage = $tableImg->attr('src');
                }
            }

            // Texto
            $html = $crawler->filter('body')->html();
            $replacements = [
                '<br>' => "\n", '<br/>' => "\n", '<br />' => "\n",
                '</td>' => " \n", 
                '</tr>' => "\n",
                '</div>' => "\n",
                '</p>' => "\n",
                '</b>' => " ", 
                '</strong>' => " ",
            ];
            $structuredHtml = strtr($html, $replacements);
            $textBlob = strip_tags($structuredHtml);

            return [
                'text_blob' => $textBlob,
                'image_url' => $bestImage
            ];

        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseTextData(string $text): array
    {
        $data = [
            'bs_type_line' => null,
            'bs_rarity' => 'common',
            'collection_number' => null,
            'bs_artist' => null,
            'bs_rules_text' => null,
            'bs_flavor_text' => null,
            'bs_power' => null,
            'bs_toughness' => null,
            'bs_cost' => null, 
            'bs_affiliation' => null,
            'bs_alter_ego' => null,
        ];

        if (preg_match('/Alter Ego:[\s]*(.*?)(?:\n|$)/iu', $text, $m)) $data['bs_alter_ego'] = trim($m[1]);
        if (preg_match('/Tipo:[\s]*(.*?)(?:\n|$)/iu', $text, $m)) $data['bs_type_line'] = trim($m[1]);
        if (preg_match('/Raridade:[\s]*(.*?)(?:\n|$)/iu', $text, $m)) $data['bs_rarity'] = trim($m[1]);
        if (preg_match('/(?:Ilustrador|Artista|Ilustradores):[\s]*(.*?)(?:\n|$)/iu', $text, $m)) $data['bs_artist'] = trim($m[1]);
        if (preg_match('/(?:Energia|Poder):[\s]*(\d+)/iu', $text, $m)) $data['bs_power'] = $m[1];
        if (preg_match('/Escudo:[\s]*(\d+)/iu', $text, $m)) $data['bs_toughness'] = $m[1];
        if (preg_match('/Afilia..o:[\s]*(.*?)(?:\n|$)/iu', $text, $m)) $data['bs_affiliation'] = trim($m[1]);

        if (preg_match('/Card:[\s]*(\d+)/iu', $text, $m)) {
             $data['collection_number'] = $m[1];
        } elseif (preg_match('/N.mero:[\s]*(.*?)(?:\n|$)/iu', $text, $m)) {
             $data['collection_number'] = trim($m[1]);
        }

        if (preg_match('/Texto:\s*(.+?)(?=\n\s*(?:S.rie|Outras|Raridade|Card|Ilustrador|$))/is', $text, $m)) {
            $rules = trim($m[1]);
            $rules = str_replace(['Formato Batalha Sitiada', 'Formato Batalha Infinita', 'Errata:', 'Voltar'], '', $rules);
            $rules = preg_replace('/Formato .*?:.*?(?:\n|$)/i', '', $rules);
            $data['bs_rules_text'] = trim($rules);
        }

        return $data;
    }
}