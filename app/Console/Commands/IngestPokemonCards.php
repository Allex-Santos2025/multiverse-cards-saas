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
                            {--set-id= : ID do Set na API (ex: swsh1) para baixar apenas um}
                            {--force : Ignora o checkpoint e começa do zero}
                            {--resume : Força a leitura do checkpoint}
                            {--page-size=250 : Quantidade inicial de cartas por requisição}';

    protected $description = 'Ingere cartas de Pokémon TCG via API oficial para a estrutura Catalog V4.';

    protected ?Game $game;
    protected string $apiKey = ''; 
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
        ini_set('max_execution_time', 0); // Sem limite de tempo

        $this->info("--- INGESTÃO POKÉMON TCG (CARTAS) ---");

        $this->game = Game::find(2); 
        if (!$this->game) {
            $this->error("Game ID 2 (Pokémon TCG) não encontrado.");
            return self::FAILURE;
        }
        
        $this->apiKey = config('services.pokemon.api_key', '');

        $setsQuery = Set::where('game_id', $this->game->id)->orderBy('id', 'asc');

        if ($setId = $this->option('set-id')) {
            $setsQuery->where(function($q) use ($setId) {
                $q->where('mtg_scryfall_id', $setId)->orWhere('code', $setId);
            });
            $this->info("Modo Set Único: Processando filtro [{$setId}].");
        } else {
            $lastSetId = $this->getCheckpoint();
            if ($lastSetId && !$this->option('force')) {
                $setsQuery->where('id', '>', $lastSetId);
                $this->info("Retomando a partir do Set ID: {$lastSetId}.");
            } else {
                $this->info("Iniciando do zero.");
            }
        }

        $sets = $setsQuery->get();

        if ($sets->isEmpty()) {
            $this->info("Nenhum Set encontrado para processar.");
            return self::SUCCESS;
        }

        $this->info("Encontrados " . $sets->count() . " Sets para processar.");

        foreach ($sets as $set) {
            $this->processSet($set);
            
            if (!$this->option('set-id')) {
                $this->setCheckpoint($set->id);
            }

            gc_collect_cycles();
        }

        $this->info("\n--- Processo Finalizado ---");
        
        if (!$this->option('set-id')) {
            $this->clearCheckpoint();
        }

        return self::SUCCESS;
    }

    protected function processSet(Set $set)
    {
        $this->conceptIdCache = [];
        $apiSetCode = $set->mtg_scryfall_id ?? $set->code;
        
        $usingFallback = false;

        $this->output->writeln("\nProcessando Set: [{$set->code}] {$set->name} (API ID: $apiSetCode)");

        $page = 1;
        $pageSize = (int) $this->option('page-size'); 
        $processedCount = 0;
        $imagesDownloaded = 0;
        
        $consecutiveFailures = 0;
        $connectionRetries = 0; // Novo contador para erros de conexão
        
        $hasMore = true;

        $headers = [];
        if ($this->apiKey) {
            $headers['X-Api-Key'] = $this->apiKey;
        }
        
        do {
            usleep(200000); 

            $url = "https://api.pokemontcg.io/v2/cards?q=set.id:{$apiSetCode}&page={$page}&pageSize={$pageSize}";
            
            // Log para debug manual se necessário
            // Log::info("Requesting: $url");

            try {
                $response = Http::withHeaders($headers)
                    ->timeout(120) 
                    ->retry(3, 2000) 
                    ->get($url);
                
                if ($response->failed()) {
                    $status = $response->status();
                    
                    // 404: Set não encontrado
                    if ($status === 404) {
                        if (!$usingFallback && $set->mtg_scryfall_id && $set->code && $apiSetCode !== $set->code) {
                            $this->warn("   -> ID '{$apiSetCode}' deu 404. Tentando '{$set->code}'...");
                            $apiSetCode = $set->code;
                            $usingFallback = true;
                            $page = 1;
                            continue; 
                        }
                        $this->error("   -> Set não encontrado (404).");
                        break; 
                    }

                    // 5xx: Erro de Servidor / Timeout
                    if ($status >= 500) {
                        $this->warn("   -> Erro Servidor ({$status}).");
                        
                        if ($pageSize > 50) {
                            $pageSize = 50;
                            $this->info("   -> Reduzindo carga para 50 cartas...");
                            continue;
                        } elseif ($pageSize > 10) {
                            $pageSize = 10;
                            $this->info("   -> Reduzindo carga para 10 cartas...");
                            continue;
                        }

                        $consecutiveFailures++;
                        if ($consecutiveFailures > 3) {
                            $this->error("   -> Falha crítica na API (3x). Pulando.");
                            break; 
                        }
                        
                        sleep(5); 
                        continue; 
                    }

                    $this->error("\nErro API página {$page}: " . $status);
                    break;
                }

                // SUCESSO
                $data = $response->json();
                $cards = $data['data'] ?? [];
                
                $consecutiveFailures = 0; 
                $connectionRetries = 0; // Reseta retry de conexão

                if (empty($cards)) {
                    if ($page === 1) {
                         // Se vazio na primeira página, tenta o código visual antes de desistir
                         if (!$usingFallback && $set->mtg_scryfall_id && $set->code && $apiSetCode !== $set->code) {
                             $this->warn("   -> Retorno vazio para '{$apiSetCode}'. Tentando '{$set->code}'...");
                             $apiSetCode = $set->code;
                             $usingFallback = true;
                             continue;
                         }
                         $this->info("   -> Set vazio ou futuro.");
                    }
                    break; 
                }

                $this->processPageChunk($cards, $set, $processedCount, $imagesDownloaded);
                
                $totalCount = $data['totalCount'] ?? 0;
                $this->output->write("\r   -> Pág {$page} | Cards: {$processedCount}/{$totalCount} | Imgs: {$imagesDownloaded}   ");

                $hasMore = ($page * $pageSize) < $totalCount;
                $page++;
                unset($data, $cards);

            } catch (\Exception $e) {
                // Tratamento de Exceptions (Ex: cURL error 28 Timeout)
                $this->warn("\n   -> Erro Conexão: " . $e->getMessage());
                
                // Tenta o MESMO ID mais algumas vezes antes de trocar
                $connectionRetries++;
                if ($connectionRetries <= 3) {
                    $this->warn("   -> Tentativa {$connectionRetries}/3 no mesmo ID. Aguardando 10s...");
                    sleep(10);
                    continue; // Tenta a mesma página e mesmo ID de novo
                }

                // Se falhou 3x no mesmo ID, aí sim tenta o fallback
                if (!$usingFallback && $set->mtg_scryfall_id && $set->code && $apiSetCode !== $set->code) {
                    $this->warn("   -> Conexão impossível com '{$apiSetCode}'. Tentando ID alternativo '{$set->code}'...");
                    $apiSetCode = $set->code;
                    $usingFallback = true;
                    $connectionRetries = 0; // Reseta para o novo ID
                    $page = 1;
                    continue;
                }

                $this->error("   -> Falha de conexão persistente. Pulando set.");
                break;
            }

        } while ($hasMore);
        
        $this->output->writeln(""); 
    }

    protected function processPageChunk(array $cards, Set $set, int &$processedCount, int &$imagesDownloaded)
    {
        foreach ($cards as $cardData) {
            $success = $this->ingestCard($cardData, $set, $imagesDownloaded);
            if ($success) {
                $processedCount++;
            }
        }
    }

    protected function ingestCard(array $data, Set $set, int &$imagesDownloaded): bool
    {
        return DB::transaction(function () use ($data, $set, &$imagesDownloaded) {
            try {
                // 1. CONCEITO
                $conceptName = $data['name'];
                $catalogConcept = null;

                if (isset($this->conceptIdCache[$conceptName])) {
                    $catalogConcept = CatalogConcept::find($this->conceptIdCache[$conceptName]);
                }

                if (!$catalogConcept) {
                    $catalogConcept = CatalogConcept::where('game_id', $this->game->id)
                        ->where('name', $conceptName)
                        ->first();
                }

                if (!$catalogConcept) {
                    $pkConcept = PkConcept::create([
                        'supertype' => $data['supertype'] ?? null,
                        'hp' => $data['hp'] ?? null,
                        'level' => $data['level'] ?? null,
                        'types' => $data['types'] ?? [],
                        'subtypes' => $data['subtypes'] ?? [],
                        'attacks' => $data['attacks'] ?? [],
                        'abilities' => $data['abilities'] ?? [],
                        'weaknesses' => $data['weaknesses'] ?? [],
                        'resistances' => $data['resistances'] ?? [],
                        'retreat_cost' => $data['retreatCost'] ?? [],
                        'evolves_from' => $data['evolvesFrom'] ?? null,
                        'evolves_to' => $data['evolvesTo'] ?? [],
                        'rules_text' => isset($data['rules']) ? implode("\n", $data['rules']) : null,
                        'national_pokedex_numbers' => $data['nationalPokedexNumbers'] ?? [],
                        'legalities' => $data['legalities'] ?? [],
                        'regulation_mark' => $data['regulationMark'] ?? null,
                        'ancient_trait' => $data['ancientTrait'] ?? null,
                    ]);

                    $catalogConcept = CatalogConcept::create([
                        'game_id' => $this->game->id,
                        'name' => $conceptName,
                        'slug' => Str::slug($conceptName),
                        'search_names' => [$conceptName],
                        'specific_type' => PkConcept::class,
                        'specific_id' => $pkConcept->id,
                    ]);
                }
                
                $this->conceptIdCache[$conceptName] = $catalogConcept->id;

                // 2. IMAGEM
                $imageUrl = $data['images']['large'] ?? $data['images']['small'] ?? null;
                $localPath = null;
                
                if ($imageUrl) {
                    $localPath = $this->downloadImage($imageUrl, $set->code, $data['id']);
                    if ($localPath) {
                         $imagesDownloaded++; 
                    }
                }

                // 3. PRINT
                $catalogPrint = CatalogPrint::where('set_id', $set->id)
                    ->whereHasMorph('specific', [PkPrint::class], function ($q) use ($data) {
                        $q->where('number', $data['number']);
                    })
                    ->first();

                if (!$catalogPrint) {
                    $pkPrint = PkPrint::create([
                        'rarity' => $data['rarity'] ?? null,
                        'artist' => $data['artist'] ?? null,
                        'number' => $data['number'],
                        'flavor_text' => $data['flavorText'] ?? null,
                        'level' => $data['level'] ?? null,
                        'language_code' => 'en', 
                        'tcgplayer' => $data['tcgplayer'] ?? [],
                        'cardmarket' => $data['cardmarket'] ?? [],
                        'images' => $data['images'] ?? [],
                    ]);

                    $catalogPrint = CatalogPrint::create([
                        'concept_id' => $catalogConcept->id,
                        'set_id' => $set->id,
                        'image_path' => $localPath, 
                        'specific_type' => PkPrint::class,
                        'specific_id' => $pkPrint->id,
                    ]);

                } else {
                    $catalogPrint->update([
                        'image_path' => $localPath ?? $catalogPrint->image_path 
                    ]);
                    
                    if ($catalogPrint->specific) {
                        $catalogPrint->specific->update([
                            'tcgplayer' => $data['tcgplayer'] ?? [],
                            'cardmarket' => $data['cardmarket'] ?? [],
                        ]);
                    }
                }

                return true;

            } catch (\Exception $e) {
                return false;
            }
        });
    }

    protected function downloadImage(?string $url, string $setCode, string $cardId): ?string
    {
        if (empty($url)) return null;

        $safeName = $cardId; 
        $extension = Str::endsWith($url, '.png') ? 'png' : 'jpg';
        $fileName = "{$safeName}.{$extension}";
        $relativePath = "card_images/Pokemon/{$setCode}/{$fileName}";
        $fullPath = public_path($relativePath);

        if (File::exists($fullPath) && !$this->option('force')) {
            return $relativePath;
        }

        try {
            if (!File::exists(dirname($fullPath))) {
                File::ensureDirectoryExists(dirname($fullPath));
            }

            usleep(200000); 

            $response = Http::timeout(30)
                ->retry(2, 500) 
                ->sink($fullPath)
                ->get($url);

            if ($response->successful() && File::exists($fullPath) && File::size($fullPath) > 0) {
                return $relativePath;
            } else {
                if (File::exists($fullPath)) File::delete($fullPath);
            }
        } catch (\Exception $e) {
            if (File::exists($fullPath)) File::delete($fullPath);
        }
        
        return File::exists($fullPath) ? $relativePath : null;
    }

    protected function setCheckpoint(int $id) { File::put($this->checkpointPath, $id); }
    protected function getCheckpoint() { return File::exists($this->checkpointPath) ? (int)File::get($this->checkpointPath) : null; }
    protected function clearCheckpoint() { if(File::exists($this->checkpointPath)) File::delete($this->checkpointPath); }
}