<?php

namespace App\Services;

use App\Models\Set;
use App\Models\Card; // Necessário se formos adicionar lógica de card aqui futuramente
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class ScryfallApi
{
    protected string $baseUrl;
    protected int $rateLimitMs;
    protected int $gameId;
    
    protected float $lastRequestTime = 0;
    protected string $userAgent = 'multiverse-cards-saas/1.0';

    /**
     * Construtor que recebe a configuração do TCG da tabela 'games'.
     */
    public function __construct(string $baseUrl, int $rateLimitMs, int $gameId)
    {
        $this->baseUrl = $baseUrl;
        $this->rateLimitMs = $rateLimitMs;
        $this->gameId = $gameId;
    }

    /**
     * Garante que o limite de taxa da API externa seja respeitado.
     */
    protected function respectRateLimit(): void
    {
        $timeSinceLastRequest = (microtime(true) * 1000) - $this->lastRequestTime;
        $delayNeeded = $this->rateLimitMs - $timeSinceLastRequest; // Usando a propriedade correta rateLimitMs

        if ($delayNeeded > 0) {
            usleep((int)($delayNeeded * 1000));
        }

        $this->lastRequestTime = microtime(true) * 1000;
    }

    /**
     * Faz uma requisição HTTP robusta usando o cliente nativo do Laravel.
     */
    protected function makeRequest(string $urlOrEndpoint): ?array
    {
        $this->respectRateLimit();

        $fullUrl = $urlOrEndpoint;

        if (! str_starts_with($urlOrEndpoint, 'http')) {
            $fullUrl = $this->baseUrl . $urlOrEndpoint;
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'application/json',
                'Connection' => 'close',
            ])
            ->timeout(60) 
            ->withOptions([
                'verify' => false, 
                'force_ip_resolve' => 'v4', 
            ])
            ->get($fullUrl);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['object']) && $data['object'] === 'error') {
                    Log::warning("Scryfall API returned error for {$fullUrl}: {$data['details']}");
                    return null;
                }
                return $data;
            }

            Log::error("Scryfall API HTTP Error {$response->status()} para {$fullUrl}");
            return null;

        } catch (\Throwable $t) {
            Log::error("Scryfall API Connection Error para {$fullUrl}: " . $t->getMessage());
            return null;
        }
    }

    /**
     * O método de entrada que o IngestionManager chama.
     */
    public function runIngestionJob(): void
    {
        Log::info("Iniciando ingestão de Magic: The Gathering (Game ID: {$this->gameId}) usando API base: {$this->baseUrl}");

        // 1. Ingestão de SETS (com lógica de resgate)
        $this->ingestSets();

        Log::info("Ingestão de Sets finalizada. A ingestão de Cards deve ser rodada separadamente via comando scryfall:ingest-cards.");
    }

    /**
     * Busca todos os Sets do Scryfall e os salva no banco de dados.
     */
    protected function ingestSets(): void
    {
        $setsData = $this->getAllSets();
        $totalCount = count($setsData['data'] ?? []);
        
        // Lógica de resgate se faltarem sets
        if ($totalCount < 1019) { 
            Log::warning("Contagem de Sets Baixa ({$totalCount}). Ativando modo de Resgate via Paginação de Cards.");
            $setsData = $this->getSetsViaCardPagination();
        }

        if ($setsData && isset($setsData['data'])) {
            $this->mapAndUpsertSets($setsData['data']);
        }
    }

    /**
     * Tenta obter todos os sets via endpoint direto.
     */
    public function getAllSets(): ?array
    {
        return $this->makeRequest('/sets');
    }
    
    /**
     * Método de resgate para encontrar sets via paginação de cards.
     */
    protected function getSetsViaCardPagination(): array
    {
        $uniqueSets = [];
        $nextPageUrl = '/cards/search?q=game%3Apaper&unique=sets&order=released'; 
        $maxIterations = 1000;
        $iteration = 0;

        while ($nextPageUrl && $iteration < $maxIterations) {
            $iteration++;
            $data = $this->makeRequest(str_replace($this->baseUrl, '', $nextPageUrl));
            
            if (!$data || empty($data['data'])) break;

            foreach ($data['data'] as $card) {
                $setCode = Arr::get($card, 'set');
                if ($setCode && !isset($uniqueSets[$setCode])) {
                    $uniqueSets[$setCode] = [
                        'code' => $setCode,
                        'name' => Arr::get($card, 'set_name'),
                        'id' => Arr::get($card, 'set_id'),
                        'set_type' => Arr::get($card, 'set_type', 'unknown'),
                        'card_count' => Arr::get($card, 'card_count', 0),
                        'released_at' => Arr::get($card, 'released_at'),
                    ];
                }
            }
            $nextPageUrl = $data['has_more'] ? ($data['next_page'] ?? null) : null;
        }
        
        return ['object' => 'list', 'data' => array_values($uniqueSets)];
    }

    protected function mapAndUpsertSets(array $setsData): int
    {
        $setsToInsert = [];
        foreach ($setsData as $setData) {
            $setsToInsert[] = [
                'game_id' => $this->gameId, 
                'mtg_scryfall_id' => $setData['id'] ?? null, 
                'code' => $setData['code'] ?? null, 
                'icon_svg_uri' => $setData['icon_svg_uri'] ?? null,
                'name' => $setData['name'] ?? 'Set Sem Nome',
                'set_type' => $setData['set_type'] ?? 'core',
                'released_at' => $setData['released_at'] ?? null,
                'card_count' => $setData['card_count'] ?? 0,
                'digital' => $setData['digital'] ?? false,
                'foil_only' => $setData['foil_only'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        Set::upsert($setsToInsert, ['mtg_scryfall_id', 'game_id'], [
            'name', 'set_type', 'card_count', 'released_at', 'icon_svg_uri', 'digital', 'foil_only', 'code'
        ]);
        
        return count($setsToInsert);
    }

    /**
     * Obtém um conjunto de cartas de uma URL de paginação.
     * ESTE É O MÉTODO QUE ESTAVA FALTANDO.
     * @param string $url A URL completa ou parcial para buscar.
     * @return array
     */
    public function getCardsByUrl(string $url): array
    {
        // Remove a base URL se ela já estiver presente para evitar duplicação no makeRequest
        $path = str_replace($this->baseUrl, '', $url);
        
        $data = $this->makeRequest($path);

        if ($data === null) {
            // Retorna estrutura vazia consistente para evitar erros no loop
            return ['data' => [], 'has_more' => false];
        }

        return $data;
    }
}