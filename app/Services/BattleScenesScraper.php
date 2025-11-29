<?php

namespace App\Services;

// Usando o HttpBrowser (rápido), que é o correto para este site
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 

class BattleScenesScraper
{
    // 1. A página do formulário de busca (onde estão os sets)
    protected string $searchPageUrl = 'https://www.magicjebb.com.br/site/busca_cards_bs.php';
    // 2. A página de resultados (que vamos usar para raspar os cards)
    protected string $resultsUrl = 'https://www.magicjebb.com.br/site/busca.php';

    protected HttpBrowser $client;

    public function __construct()
    {
        $defaultOptions = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.0.0 Safari/537.36',
            ],
            'timeout' => 15, // 15 segundos de timeout
        ];

        $this->client = new HttpBrowser(HttpClient::create($defaultOptions));
    }

    /**
     * Raspa a lista de Sets (Coleções) OFICIAIS a partir do MagicJebb.
     */
    public function getSetsList(): array
    {
        Log::info('Iniciando raspagem de Sets (MagicJebb).');
        
        try {
            // Acessa a página do formulário de busca
            $crawler = $this->client->request('GET', $this->searchPageUrl);
            
            $sets = [];
            
            // Este é o seletor correto para o <select> de "Séries" no magicjebb
            $selector = 'select[name="serie"] option'; 

            $crawler->filter($selector)->each(function (Crawler $node) use (&$sets) {
                
                $name = trim($node->text() ?? '');
                $code = trim($node->attr('value') ?? ''); // O 'value' é o próprio nome

                // Ignora o <option> inicial ("")
                if (!empty($code)) {
                    $sets[$code] = [ // Usando o code como chave para evitar duplicatas
                        'name' => $name,
                        'code' => $code, // Usando o nome como 'code' (ex: "Universo Marvel")
                        
                    ];
                }
            });

            Log::info('Raspagem de Sets (MagicJebb) concluída.', ['count' => count($sets)]);
            return array_values($sets);
            
        } catch (\Exception $e) {
            // Se a requisição falhar, salva o erro
            $errorMessage = $e->getMessage();
            Storage::disk('local')->put('debug_magicjebb_FAILURE.txt', $errorMessage);
            Log::error('Erro CRÍTICO na raspagem de Sets (MagicJebb). Erro salvo em storage/app/debug_magicjebb_FAILURE.txt');
            return [];
        }
    }

    /**
     * Raspa todos os cards de um Set específico (usando o nome do set).
     */
    public function scrapeCardsForSet(string $setName): \Generator
    {
        Log::info("Iniciando raspagem de cards para o Set: {$setName}");
        $page = 1;

        do {
            // Monta a URL da página de resultados
            $urlEncodedSetName = urlencode($setName);
            $url = $this->resultsUrl . "?serie={$urlEncodedSetName}&formato=detalhes&pag={$page}";
            Log::debug("Processando URL: {$url}");

            try {
                $crawler = $this->client->request('GET', $url);
            } catch (\Exception $e) {
                Log::error("Erro ao acessar {$url}: " . $e->getMessage());
                break; // Pula para o próximo set
            }

            // ***** SELETOR CORRIGIDO: Procurando pelas células de dados dos cards *****
            // Cada card (imagem + dados) está dentro de uma tabela aninhada
            $cardsInPage = $crawler->filter('td[width="550"] > table[width="550"]'); 

            if ($cardsInPage->count() === 0) {
                Log::info(" -> Página {$page} vazia. Terminando o set '{$setName}'.");
                break; // Sai do loop 'do-while'
            }

            foreach ($cardsInPage as $domElement) {
                $cardNode = new Crawler($domElement);

                try {
                    // Extrai o link da imagem e o nome da imagem
                    $imageElement = $cardNode->filter('img[src*="bs_cards/"]')->first();
                    $imageUrl = $imageElement?->attr('src');
                    $cardName = $imageElement?->attr('alt'); // O nome real está no ALT/Title
                    
                    // Se não achou imagem ou nome, pula este bloco (provavelmente é lixo do HTML)
                    if (!$imageUrl || !$cardName) {
                        continue;
                    }

                    // O bloco de dados é a próxima célula <td> da tabela
                    $dataBlock = $cardNode->filter('td[valign="top"]')->last();
                    $html = $dataBlock?->html() ?? '';

                    yield [
                        'name' => trim($cardName),
                        'image_url' => 'http://www.magicjebb.com.br/site/' . $imageUrl, // Corrigindo para URL absoluta
                        'rules_text_html' => $html,
                    ];

                } catch (\Exception $e) {
                    Log::warning("Erro ao raspar um card individual: " . $e->getMessage(), ['url' => $url]);
                }
            }
            $page++;
            usleep(250000); // Pausa de 0.25 seg

        } while (true);
    }
}

