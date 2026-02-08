<?php

namespace App\Services;

use App\Models\Set;
use App\Models\Card; 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Arr;

class ScryfallApi
{
    protected string $baseUrl;
    protected int $rateLimitMs;
    protected int $gameId;
    
    protected float $lastRequestTime = 0;
    protected string $userAgent = 'multiverse-cards-saas/1.0';
    protected string $gameSlug; // Propriedade do Slug

    /**
     * Construtor ÚNICO que recebe a configuração do TCG da tabela 'games'.
     */
    public function __construct(string $baseUrl, int $rateLimitMs, int $gameId, string $gameSlug)
    {
        $this->baseUrl = $baseUrl;
        $this->rateLimitMs = $rateLimitMs;
        $this->gameId = $gameId;
        
        // Salvando o slug para usar no download da imagem
        $this->gameSlug = $gameSlug;
    }

    /**
     * O método de entrada que o IngestionManager chama.
     */
    public function runIngestionJob(): void
    {
        Log::info("Iniciando ingestão de Magic: The Gathering (Game ID: {$this->gameId}) usando API base: {$this->baseUrl}");

        // 1. Ingestão de SETS (com lógica de download de imagem)
        $this->ingestSets();

        Log::info("Ingestão de Sets finalizada. A ingestão de Cards deve ser rodada separadamente via comando scryfall:ingest-cards.");
    }

    /**
     * Busca todos os Sets do Scryfall, baixa os ícones e salva no banco.
     */
    protected function ingestSets(): void
    {
        Log::info("Buscando sets na API: {$this->baseUrl}/sets");

        // 1. Busca os dados da API do Scryfall
        $response = Http::timeout(30)->get("{$this->baseUrl}/sets");

        if ($response->failed()) {
            Log::error("Falha ao buscar sets do Scryfall: " . $response->status());
            return;
        }

        $json = $response->json();
        // Scryfall retorna a lista dentro da chave 'data'
        $sets = $json['data'] ?? [];

        Log::info("Encontrados " . count($sets) . " sets. Iniciando processamento e download de imagens...");

        foreach ($sets as $setData) {
            
            // --- LÓGICA DE DOWNLOAD DO ÍCONE ---
            // Baixa a imagem e retorna o caminho relativo (public/card_images/...)
            $iconPath = $this->downloadSetIcon(
                $setData['icon_svg_uri'] ?? '', 
                $setData['code']
            );
            // -----------------------------------

            // 2. Salva ou Atualiza no Banco
            Set::updateOrCreate(
                [
                    'code' => $setData['code'], 
                    'game_id' => $this->gameId
                ],
                [
                    'name' => $setData['name'],
                    'released_at' => $setData['released_at'] ?? null,
                    // Aqui salvamos o caminho local (card_images/magic/lea/lea.svg)
                    'icon_url' => $iconPath, 
                    
                    // Outros campos opcionais
                    'set_type' => $setData['set_type'] ?? 'core',
                    'card_count' => $setData['card_count'] ?? 0,
                    'digital' => $setData['digital'] ?? false,
                    'foil_only' => $setData['foil_only'] ?? false,
                ]
            );
        }

        Log::info("Processamento de Sets finalizado.");
    }

    /**
     * Baixa o SVG e salva em: public/card_images/{game}/{set_code}/{set_code}.svg
     * Retorna o caminho relativo para salvar no banco.
     */
    protected function downloadSetIcon(string $url, string $setCode): string
    {
        // Se a URL for vazia, retorna vazio
        if (empty($url)) return '';

        try {
            // Normaliza o código (ex: 'LEA' vira 'lea')
            $safeCode = strtolower($setCode);
            
            // Caminho relativo para salvar no banco (para usar no asset())
            // Ex: card_images/magic/lea/lea.svg
            $relativePath = "card_images/{$this->gameSlug}/{$safeCode}/{$safeCode}.svg";
            
            // Caminho absoluto do sistema para salvar o arquivo
            $fullPath = public_path($relativePath);

            // 1. Verifica se já existe para economizar banda
            if (File::exists($fullPath)) {
                return $relativePath;
            }

            // 2. Garante que a pasta existe (card_images/magic/lea)
            $directory = dirname($fullPath);
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            // 3. Baixa o arquivo
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                // 4. Salva o conteúdo no arquivo
                File::put($fullPath, $response->body());
                return $relativePath; // Sucesso! Retorna o caminho novo.
            }

        } catch (\Exception $e) {
            // Se der erro, loga e segue a vida
            Log::warning("Erro ao baixar ícone do set {$setCode}: " . $e->getMessage());
        }

        // Se falhar, retorna a URL original externa como fallback
        return $url; 
    }

    /**
     * Garante que o limite de taxa da API externa seja respeitado.
     */
    protected function respectRateLimit(): void
    {
        $timeSinceLastRequest = (microtime(true) * 1000) - $this->lastRequestTime;
        $delayNeeded = $this->rateLimitMs - $timeSinceLastRequest;

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

    /**
     * Obtém um conjunto de cartas de uma URL de paginação.
     */
    public function getCardsByUrl(string $url): array
    {
        // Remove a base URL se ela já estiver presente para evitar duplicação
        $path = str_replace($this->baseUrl, '', $url);
        
        $data = $this->makeRequest($path);

        if ($data === null) {
            return ['data' => [], 'has_more' => false];
        }

        return $data;
    }
}