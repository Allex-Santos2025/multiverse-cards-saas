<?php

namespace App\Services;

use App\Models\Set;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PokemonTcgApiService
{
    protected string $apiKey;
    protected int $rateLimitMs;
    protected int $gameId; 
    
    protected string $baseUrl = 'https://api.pokemontcg.io/v2';
    protected float $lastRequestTime = 0;
    protected string $userAgent = 'multiverse-cards-saas/1.0';

    public function __construct(string $apiKey, int $rateLimitMs, int $gameId)
    {
        $this->apiKey = $apiKey;
        $this->rateLimitMs = $rateLimitMs;
        $this->gameId = $gameId;

        Log::info("PokemonTcgApiService instanciado. GameID: {$gameId} | Rate: {$rateLimitMs}ms");
    }

    public function runIngestionJob(): void
    {
        Log::info("=== Iniciando Ingestão: Pokémon TCG (Game ID: {$this->gameId}) ===");

        // 1. Ingestão de SETS
        $this->ingestSets();

        Log::info("=== Ingestão Finalizada com Sucesso ===");
    }

    protected function ingestSets(): void
    {
        $page = 1;
        $pageSize = 250;
        $hasMore = true;
        
        Log::info("Iniciando sincronização de Sets...");

        while ($hasMore) {
            Log::info("Requisitando página {$page} de Sets (tamanho: {$pageSize})...");

            $data = $this->makeRequest('/sets', [
                'page' => $page,
                'pageSize' => $pageSize,
                'orderBy' => '-releaseDate'
            ]);

            if (!$data) {
                Log::error("Falha crítica: A API não retornou dados para a página {$page}. Abortando.");
                break;
            }

            if (empty($data['data'])) {
                Log::warning("A API retornou uma lista vazia de dados na página {$page}. Finalizando paginação.");
                break;
            }

            $count = count($data['data']);
            Log::info("Encontrados {$count} sets na página {$page}. Processando...");

            foreach ($data['data'] as $apiSet) {
                $this->upsertSet($apiSet);
            }

            $totalCount = $data['totalCount'] ?? 0;
            $currentCount = ($page - 1) * $pageSize + $count;

            Log::info("Progresso: {$currentCount} / {$totalCount} sets processados.");

            if ($currentCount >= $totalCount || $count < $pageSize) {
                $hasMore = false;
            } else {
                $page++;
            }
        }
    }

    protected function upsertSet(array $apiSet): void
    {
        try {
            // LÓGICA DE CÓDIGOS AJUSTADA (DATA HEALING):
            // 1. 'mtg_scryfall_id' armazena o ID Técnico (swsh1)
            // 2. 'code' armazena o Código Visual (SSH)
            
            $displayCode = $apiSet['ptcgoCode'] ?? $apiSet['id']; // Ex: 'SSH'
            $technicalId = $apiSet['id']; // Ex: 'swsh1'

            $setData = [
                'name'         => $apiSet['name'],
                'card_count'   => $apiSet['total'] ?? 0,
                'set_type'     => $apiSet['series'] ?? 'Expansion', 
                'digital'      => 0, 
                'foil_only'    => 0, 
                'is_fanmade'   => 0, 
                'released_at'  => $apiSet['releaseDate'] ?? null,
                'icon_svg_uri' => $apiSet['images']['logo'] ?? null, 
                'updated_at'   => now(),
            ];

            // ESTRATÉGIA DE CURA: 
            // Resolve o erro 'Duplicate entry' procurando por qualquer um dos identificadores existentes.

            // 1. Tenta achar pelo ID Técnico (o mais seguro)
            $set = Set::where('game_id', $this->gameId)
                      ->where('mtg_scryfall_id', $technicalId)
                      ->first();

            // 2. Se não achou, tenta achar pelo Código Visual (evita colisão de chave única)
            if (!$set) {
                $set = Set::where('game_id', $this->gameId)
                          ->where('code', $displayCode)
                          ->first();
            }

            if ($set) {
                // Se encontrou (por qualquer método), ATUALIZA unificando os dados
                // Isso garante que registros antigos ganhem o mtg_scryfall_id correto
                $set->update(array_merge($setData, [
                    'code' => $displayCode,
                    'mtg_scryfall_id' => $technicalId
                ]));
            } else {
                // Se não existe de jeito nenhum, CRIA
                Set::create(array_merge($setData, [
                    'game_id' => $this->gameId,
                    'code' => $displayCode,
                    'mtg_scryfall_id' => $technicalId
                ]));
            }

        } catch (\Exception $e) {
            $msg = "ERRO ao salvar Set {$apiSet['name']} ({$apiSet['id']}): " . $e->getMessage();
            Log::error($msg);
            echo "\n [ERRO SQL] $msg \n"; 
        }
    }

    protected function makeRequest(string $endpoint, array $queryParams = []): ?array
    {
        $this->respectRateLimit();
        $fullUrl = $this->baseUrl . $endpoint;

        try {
            $response = Http::withHeaders([
                'X-Api-Key' => $this->apiKey,
                'User-Agent' => $this->userAgent,
            ])->timeout(60)->get($fullUrl, $queryParams);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("Erro HTTP API Pokemon ({$response->status()}) para {$fullUrl}: " . $response->body());
            echo "\n [API ERRO] {$response->status()} - Veja logs. \n";
            return null;

        } catch (\Throwable $t) {
            Log::error("Erro Crítico de Conexão API Pokemon: " . $t->getMessage());
            return null;
        }
    }

    protected function respectRateLimit(): void
    {
        $delay = $this->rateLimitMs - ((microtime(true) * 1000) - $this->lastRequestTime);
        if ($delay > 0) usleep((int)($delay * 1000));
        $this->lastRequestTime = microtime(true) * 1000;
    }
}