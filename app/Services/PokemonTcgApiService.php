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
    
    // TCGdex API v2 - Rota em Português para o Game 2
    protected string $baseUrl = 'https://api.tcgdex.net/v2';
    protected float $lastRequestTime = 0;
    protected string $userAgent = 'multiverse-cards-saas/1.0';

    public function __construct(string $apiKey, int $rateLimitMs, int $gameId)
    {
        $this->apiKey = $apiKey;
        $this->rateLimitMs = $rateLimitMs;
        $this->gameId = $gameId;

        Log::info("PokemonTcgApiService instanciado (TCGdex). GameID: {$gameId} | Rate: {$rateLimitMs}ms");
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
        // Define o locale de acordo com o jogo (Game 2 = pt, Game 9 = ja)
        $locale = ($this->gameId === 9) ? 'ja' : 'pt';
        
        Log::info("Iniciando sincronização de Sets via TCGdex (locale: {$locale})...");

        $setsList = $this->makeRequest("/{$locale}/sets");

        if (!$setsList || !is_array($setsList)) {
            Log::error("Falha crítica: A API TCGdex não retornou a lista de sets para o idioma {$locale}. Abortando.");
            return;
        }

        $count = count($setsList);
        Log::info("Encontrados {$count} sets na TCGdex. Processando...");

        $processed = 0;
        foreach ($setsList as $apiSet) {
            $this->upsertSet($apiSet, $locale);
            $processed++;

            if ($processed % 25 === 0 || $processed === $count) {
                Log::info("Progresso: {$processed} / {$count} sets sincronizados.");
            }
        }

        echo "\n [TCGdex] {$processed}/{$count} sets processados com sucesso. \n";
    }

    protected function upsertSet(array $apiSet, string $locale = 'pt'): void
    {
        try {
            $technicalId = $apiSet['id']; // Ex: 'swsh3', 'base1'
            $displayCode = strtoupper($apiSet['id']);

            $totalCards = 0;
            if (isset($apiSet['cardCount']['total'])) {
                $totalCards = (int) $apiSet['cardCount']['total'];
            } elseif (isset($apiSet['total'])) {
                $totalCards = (int) $apiSet['total'];
            }

            $icon = $apiSet['logo'] ?? $apiSet['symbol'] ?? null;

            $setData = [
                'name'         => $apiSet['name'],
                'card_count'   => $totalCards,
                'set_type'     => 'Expansion',
                'digital'      => 0,
                'foil_only'    => 0,
                'is_fanmade'   => 0,
                'icon_svg_uri' => $icon,
                'updated_at'   => now(),
            ];

            // 1. Tenta achar pelo api_id (ID Técnico)
            $set = Set::where('game_id', $this->gameId)
                      ->where('api_id', $technicalId)
                      ->first();

            // 2. Se não achou pelo api_id, tenta pelo code existente
            if (!$set) {
                $set = Set::where('game_id', $this->gameId)
                          ->where('code', $technicalId)
                          ->first();
            }

            // 3. Fallback adicional para displayCode
            if (!$set) {
                $set = Set::where('game_id', $this->gameId)
                          ->where('code', $displayCode)
                          ->first();
            }

            if ($set) {
                // Atualiza sem duplicar
                $set->update(array_merge($setData, [
                    'api_id' => $technicalId,
                ]));
            } else {
                // Cria se não existir
                Set::create(array_merge($setData, [
                    'game_id' => $this->gameId,
                    'code'    => $displayCode,
                    'api_id'  => $technicalId,
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
                'User-Agent' => $this->userAgent,
                'Accept'     => 'application/json',
            ])->timeout(60)->get($fullUrl, $queryParams);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("Erro HTTP API TCGdex ({$response->status()}) para {$fullUrl}: " . $response->body());
            echo "\n [API ERRO] {$response->status()} - Veja logs. \n";
            return null;

        } catch (\Throwable $t) {
            Log::error("Erro Crítico de Conexão API TCGdex: " . $t->getMessage());
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
