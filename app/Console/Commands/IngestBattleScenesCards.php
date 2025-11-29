<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Set;
use App\Models\Card;
use App\Models\CardFunctionality;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;

class IngestBattleScenesCards extends Command
{
    protected $signature = 'battlescenes:ingest-cards 
                            {--set= : O NOME de um set específico para importar (ex: "Universo Marvel")}
                            {--force : Força a re-importação, ignorando o checkpoint}';
                            
    protected $description = 'Ingests card data by scraping the Battle Scenes website (com checkpoint).';

    protected string $checkpointPath;
    
    protected HttpBrowser $client;
    protected string $resultsUrl = 'https://www.magicjebb.com.br/site/busca_avancada_bs.php';
    protected string $detailUrlBase = 'https://www.magicjebb.com.br/site/';

    public function __construct()
    {
        parent::__construct();
        $this->checkpointPath = storage_path('app/battlescenes_cards_checkpoint.txt');
        
        $defaultOptions = [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.0.0 Safari/537.36',
            ],
            'timeout' => 20, 
        ];
        $this->client = new HttpBrowser(HttpClient::create($defaultOptions));
    }
    
    public function handle()
    {
        $this->info('Iniciando Ingestão por Raspagem de Cards Battle Scenes...');

        $game = Game::where('name', 'Battle Scenes')->first(); 
        if (!$game) {
            $this->error("Game 'Battle Scenes' não encontrado. Cadastre-o primeiro.");
            return self::FAILURE;
        }

        // --- LÓGICA DE --SET (Inalterada) ---
        if ($setName = $this->option('set')) {
            $set = Set::where('game_id', $game->id)
                      ->where('name', $setName)
                      ->first();
            if (!$set) {
                $this->error("Set com nome '{$setName}' não encontrado no banco de dados.");
                return self::FAILURE;
            }
            $this->info("Iniciando importação específica para o Set: [{$set->code}] {$set->name}");
            $this->processSetCards($set, $game); 
            $this->info("Importação para o set [{$set->code}] concluída!");
            return self::SUCCESS;
        }
        
        // --- LÓGICA DE CHECKPOINT (Inalterada) ---
        $this->info('Iniciando importação completa de cards (com checkpoint)...');
        $lastProcessedSetId = $this->getCheckpoint();
        $setsQuery = Set::where('game_id', $game->id)->orderBy('id');
        if ($lastProcessedSetId && !$this->option('force')) {
            $this->warn("Retomando a partir do último checkpoint (Set ID: {$lastProcessedSetId}).");
            $setsQuery->where('id', '>', $lastProcessedSetId);
        } elseif ($this->option('force')) {
            $this->warn('Opção --force detectada. Ignorando checkpoint e reprocessando tudo.');
        }
        $setsToProcess = $setsQuery->cursor();
        if ($setsToProcess->count() === 0) {
            $this->info('Nenhum novo set para processar.');
            return self::SUCCESS;
        }

        // 3. Loop Principal (com checkpoint)
        $this->withProgressBar($setsToProcess, function (Set $set) use ($game) { 
            $this->line(''); 
            $this->info("Processando Set: {$set->name} (Código: {$set->code})");
            Log::channel('ingest')->info("Iniciando Set Battle Scenes: [{$set->code}] {$set->name}");
            
            $this->processSetCards($set, $game); 
            
            Log::channel('ingest')->info("Finalizado Set Battle Scenes: [{$set->code}] {$set->name}");
            $this->setCheckpoint($set->id); 
        });
        
        $this->line('');
        $this->info('Ingestão de Cards Battle Scenes concluída com sucesso!');
        $this->clearCheckpoint(); 
        return self::SUCCESS;
    }

    /**
     * Função Helper para processar os cards de UM set.
     */
    protected function processSetCards(Set $set, Game $game): void
    {
        $cardsCount = 0;
        $imagesDownloaded = 0;
        
        // 4. CHAMA O SCRAPER (AGORA DENTRO DESTE ARQUIVO)
        foreach ($this->scrapeCardLinksForSet($set->name) as $cardData) {
            $cardsCount++; 
            
            // Validação básica dos dados raspados
            if (empty($cardData['name']) || empty($cardData['image_url']) || empty($cardData['detail_url'])) {
                $this->warn(" -> Card com dados incompletos pulado no set: {$set->name}");
                continue;
            }
                
            $cardName = $cardData['name'];
            $detailUrl = $cardData['detail_url'];
            $imageUrl = $cardData['image_url'];
            
            try {
                // 5. Visita a página de detalhes (A URL que você encontrou)
                $detailHtmlBlob = $this->fetchCardDetailHtml($detailUrl);
                if (!$detailHtmlBlob) {
                    $this->warn(" -> Falha ao buscar detalhes para: {$cardName}");
                    continue;
                }
                
                // 6. Parseia o Blob de HTML (Lógica de extração de dados)
                $data = $this->parseHtmlBlob($detailHtmlBlob);
                
                // 6.1 Adiciona o nome do card (que pegamos na lista) aos dados
                $data['name'] = $cardName;

                // 7. Download da Imagem (Sua Regra)
                $localPath = $this->downloadImage($imageUrl, $set->code, $cardName);
                if ($localPath) {
                    $imagesDownloaded++;
                } else {
                    $this->warn(" -> Falha ao baixar imagem para: {$cardName}");
                }

                // 8. Salvar CardFunctionality (Dados "Oracle")
                $functionality = CardFunctionality::updateOrCreate(
                    [
                        'game_id' => $game->id,
                        'name' => $cardName, // Chave única para BS
                        'tcg_name' => 'Battle Scenes', // Definindo o tcg_name
                    ],
                    [
                        'alter_ego' => $data['alter_ego'],
                        'type_line' => $data['type_line'],
                        'rules_text' => $data['rules_text'],
                        'power' => $data['power'],
                        'toughness' => $data['toughness'], // Mapeando Escudo para toughness
                        'oracle_id' => 'bs-' . Str::uuid(), // Gerando um Oracle ID único
                    ]
                );

                // 9. Salvar Card (Dados do "Print")
                Card::updateOrCreate(
                    [
                        'card_functionality_id' => $functionality->id,
                        'set_id' => $set->id,
                        'language_code' => 'pt', // Sua Regra
                    ],
                    [
                        'tcg_name' => 'Battle Scenes',
                        'printed_name' => $cardName,
                        'alter_ego' => $data['alter_ego'], // Salvando o Alter Ego traduzido
                        'printed_text' => $data['rules_text'],
                        'printed_type_line' => $data['type_line'],
                        'power' => $data['power'], // Salvando Power/Toughness no Print também
                        'toughness' => $data['toughness'],
                        'rarity' => strtolower($data['rarity'] ?? 'common'),
                        'flavor_text' => $data['flavor_text'],
                        'artist' => $data['artist'],
                        'layout' => 'normal',
                        'local_image_path_large' => $localPath,
                        'prices' => '[]',
                        'image_uris' => '[]', 
                        'collection_number' => $data['collection_number'] ?? 'N/A', 
                        'collection_code' => $set->code, 
                    ]
                );

            // ***** INÍCIO DA CORREÇÃO DO PARSE ERROR *****
            // O código duplicado e com erros de sintaxe foi removido daqui.
            // O 'catch' abaixo é o fechamento correto do 'try'.
            } catch (QueryException $e) {
                // Captura erros de banco de dados (ex: dados muito longos)
                $this->error(" -> Erro de BD ao processar card '{$cardName}': " . $e->getMessage());
                Log::error("Falha SQL no IngestBattleScenesCards", ['card' => $cardName ?? 'N/A', 'error' => $e->getMessage()]);
            } catch (\Exception $e) {
                // Captura outros erros (ex: falha no parseHtmlBlob)
                $this->error(" -> Erro geral ao processar card '{$cardName}': " . $e->getMessage());
                Log::error("Falha no IngestBattleScenesCards", ['card' => $cardName ?? 'N/A', 'error' => $e->getMessage()]);
            }
            // ***** FIM DA CORREÇÃO *****
        }
        
        if ($cardsCount === 0) {
            $this->warn(" >>> Alerta: O Scraper não retornou NENHUM card para este set. (Verifique o seletor em scrapeCardLinksForSet)");
        } else {
            $this->info(" >>> Sucesso! Cards encontrados: {$cardsCount}. Imagens baixadas: {$imagesDownloaded}.");
        }
    }

    /**
     * Etapa 1: Raspa a página de BUSCA para pegar os links de DETALHE.
     */
    protected function scrapeCardLinksForSet(string $setName): \Generator
    {
        $page = 1;
        do {
            $urlEncodedSetName = urlencode($setName);
            
            // ***** CORREÇÃO DA URL (LINHA 208) *****
            // Adicionando os parâmetros de formulário que faltavam (exibicaobs e enviar)
            $url = $this->resultsUrl . "?serie={$urlEncodedSetName}&formato=detalhes&pag={$page}&exibicaobs=lista&enviar=Buscar";
            Log::debug("Processando URL de LISTA: {$url}");

            try {
                $crawler = $this->client->request('GET', $url);
                
                // Salva o HTML da primeira página de resultados para analisarmos o seletor.
                if ($page === 1) {
                    Storage::disk('local')->put('debug_BS_RESULTS.html', $crawler->html());
                    Log::info('HTML de debug da página de resultados salvo em storage/app/debug_BS_RESULTS.html');
                }
                
            } catch (\Exception $e) {
                Log::error("Erro ao acessar LISTA {$url}: " . $e->getMessage());
                break;
            }

            // O seletor para os links na página de busca (busca.php)
            // Este seletor é baseado na estrutura de tabela do magicjebb (imagem do card)
            $linksInPage = $crawler->filter('td a[href*="bs_card.php"]'); 

            if ($linksInPage->count() === 0) {
                Log::info(" -> Página de LISTA {$page} vazia. Terminando o set '{$setName}'.");
                break; // Sai do loop 'do-while'
            }

            foreach ($linksInPage as $domElement) {
                $cardNode = new Crawler($domElement);
                try {
                    $imageElement = $cardNode->filter('img')->first();
                    $imageUrl = $imageElement->attr('src');
                    $cardName = $imageElement->attr('alt'); // Usando 'alt' em vez de 'title'
                    $detailUrl = $cardNode->attr('href'); // A URL de detalhes (ex: bs_card.php?...)
                    
                    yield [
                        'name' => trim($cardName),
                        'image_url' => 'http://www.magicjebb.com.br/site/' . $imageUrl,
                        'detail_url' => $this->detailUrlBase . $detailUrl,
                    ];
                } catch (\Exception $e) {
                     Log::warning("Erro ao raspar um link de card: " . $e->getMessage());
                }
            }
            $page++;
            usleep(250000); // Pausa
        } while (true);
    }
    
    /**
     * Etapa 2: Visita a página de DETALHE para pegar o HTML.
     */
    protected function fetchCardDetailHtml(string $detailUrl): ?string
    {
        try {
            $crawler = $this->client->request('GET', $detailUrl);
            
            // Na página de detalhes (detalhes_bs.php), os dados estão em <td> com align="left"
            // dentro da tabela principal (width="550").
            $dataNode = $crawler->filter('table[width="550"] td[align="left"]')->first(); 
            
            if ($dataNode->count() === 0) {
                 Log::warning("Nenhum dataNode (td align=left) encontrado em: {$detailUrl}");
                 return null;
            }
            return $dataNode->html();
            
        } catch (\Exception $e) {
            Log::error("Erro ao buscar página de DETALHE {$detailUrl}: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Tenta extrair dados do blob de HTML (DA PÁGINA DE DETALHE).
     */
    protected function parseHtmlBlob(string $html): array
    {
        $data = [
            'type_line' => null,
            'rarity' => 'common',
            'collection_number' => 'N/A',
            'artist' => 'N/A',
            'rules_text' => null,
            'flavor_text' => null,
            'power' => null,
            'toughness' => null, // Escudo
            'alter_ego' => null,
        ];

        // 1. Extrai Alter Ego
        if (preg_match('/<font size="4"><b>.*?<\/b><\/font><br><i>(.*?)<\/i><br><br>/s', $html, $matches)) {
            $data['alter_ego'] = trim($matches[1]);
        }
        
        // 2. Extrai dados baseados em <b>Tags:</b>
        if (preg_match('/<b>Tipo:<\/b>\s*(.*?)<br>/i', $html, $matches)) {
            $data['type_line'] = trim(strip_tags($matches[1]));
        }
        if (preg_match('/<b>Raridade:<\/b>\s*(.*?)<br>/i', $html, $matches)) {
            $data['rarity'] = trim(strip_tags($matches[1]));
        }
         if (preg_match('/<b>Artista:<\/b>\s*(.*?)<br>/i', $html, $matches)) {
            $data['artist'] = trim(strip_tags($matches[1]));
        }
        if (preg_match('/<b>Poder:<\/b>\s*(.*?)<br>/i', $html, $matches)) {
            $data['power'] = trim(strip_tags($matches[1]));
        }
        if (preg_match('/<b>Escudo:<\/b>\s*(.*?)<br>/i', $html, $matches)) {
            $data['toughness'] = trim(strip_tags($matches[1])); // Mapeando Escudo
        }
        // Número da coleção (Ex: 1 / 100)
        if (preg_match('/<b>N.mero:<\/b>\s*(.*?)<br>/i', $html, $matches)) { // Corrigido para 'N.mero:'
            $data['collection_number'] = trim(strip_tags($matches[1]));
        }

        // 3. Extrai Flavor Text (itálico no final)
        if (preg_match('/<br><i>(.*?)<\/i><br>$/s', $html, $matches)) {
            $data['flavor_text'] = trim(strip_tags($matches[1]));
            $html = str_replace($matches[0], '', $html); // Remove do blob
        }
        
        // 4. Limpa o HTML para pegar o Texto de Regras
        $rules = preg_replace('/<font size="4"><b>.*?<\/b><\/font>(<br><i>.*?<\/i>)?(<br><br>)?/s', '', $html, 1); // Remove nome e alter ego
        $rules = preg_replace('/<b>.*?<\/b>.*?<br>/i', '', $rules); // Remove tags de dados (Tipo, Raridade, etc.)
        $rules = preg_replace('/<br>\s*<br>/i', '<br>', $rules); 
        $rules = trim(strip_tags($rules, '<br>')); 
        $data['rules_text'] = str_replace('<br>', "\n", $rules); 

        return $data;
    }

    /**
     * Baixa a imagem e salva localmente. (Inalterado)
     */
    protected function downloadImage(string $url, string $setFolderCode, string $cardName): ?string
    {
        try {
            if (Str::startsWith($url, 'images/')) {
                $url = 'http://www.magicjebb.com.br/site/' . $url;
            }
            
            $response = Http::timeout(20)->get($url);

            if ($response->successful()) {
                $langFolder = 'pt';
                $fileNameBase = Str::slug($cardName) . '_' . substr(md5($url), 0, 6);
                $fileNameLarge = "{$fileNameBase}_large.jpg";
                
                $localPath = "card_images/Battle-Scenes/{$setFolderCode}/{$langFolder}/{$fileNameLarge}";
                $fullAbsolutePath = public_path($localPath);
                
                if (File::exists($fullAbsolutePath)) {
                    return $localPath; 
                }

                File::ensureDirectoryExists(dirname($fullAbsolutePath)); 
                File::put($fullAbsolutePath, $response->body()); 
                
                return $localPath;
            } else {
                Log::warning("Falha ao baixar imagem de {$url}. Status: " . $response->status());
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Erro ao baixar imagem de {$url}: " . $e->getMessage());
            return null;
        }
    }

    // --- MÉTODOS DE CHECKPOINT (Inalterados) ---
    protected function setCheckpoint(int $setId): void 
    { 
        File::put($this->checkpointPath, $setId); 
    }
    protected function getCheckpoint(): ?int 
    { 
        return File::exists($this->checkpointPath) ? (int)File::get($this->checkpointPath) : null; 
    }
    protected function clearCheckpoint(): void 
    { 
        if (File::exists($this->checkpointPath)) { 
            File::delete($this->checkpointPath); 
            $this->info('Checkpoint limpo.'); 
        } 
    }
}

