<?php

namespace App\Services;

use App\Models\Set;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OnePieceApiService
{
    protected string $baseUrl;
    protected int $rateLimitMs;
    protected int $gameId;
    protected string $gameSlug;
    protected float $lastRequestTime = 0;
    protected string $userAgent = 'multiverse-cards-saas/1.0';

    public function __construct(string $baseUrl, int $rateLimitMs, int $gameId, string $gameSlug = 'one-piece')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->rateLimitMs = $rateLimitMs > 0 ? $rateLimitMs : 100;
        $this->gameId = $gameId;
        $this->gameSlug = $gameSlug;

        Log::info("OnePieceApiService instanciado. GameID: {$this->gameId} | BaseURL: {$this->baseUrl}");
    }

    public function runIngestionJob(): void
    {
        Log::info("=== Iniciando Ingestão de Sets: One Piece Card Game (Game ID: {$this->gameId}) ===");
        $this->ingestSets();
        $this->ingestDecks();
        $this->ingestPromos();
        Log::info("=== Ingestão de Sets de One Piece Finalizada ===");
    }

    public function ingestSets(): void
    {
        Log::info("Sincronizando Booster Sets de One Piece...");
        $setsList = $this->makeRequest('/api/allSets/');

        if (!$setsList || !is_array($setsList)) {
            Log::error("Falha ao buscar /api/allSets/ de One Piece.");
            return;
        }

        $saved = 0;
        foreach ($setsList as $apiSet) {
            $name = trim($apiSet['set_name'] ?? '');
            $code = strtoupper(trim($apiSet['set_id'] ?? ''));

            if (empty($name) || empty($code)) {
                continue;
            }

            Set::updateOrCreate(
                [
                    'game_id' => $this->gameId,
                    'code'    => $code,
                ],
                [
                    'name'        => $name,
                    'name_pt'     => $name,
                    'api_id'      => $code,
                    'set_type'    => 'booster_pack',
                    'card_count'  => 0,
                    'is_fanmade'  => false,
                    'digital'     => false,
                    'foil_only'   => false,
                    'updated_at'  => now(),
                ]
            );
            $saved++;
        }

        echo "\n [OnePiece] {$saved} Booster Sets sincronizados com sucesso.\n";
    }

    public function ingestDecks(): void
    {
        Log::info("Sincronizando Starter Decks de One Piece...");
        $decksList = $this->makeRequest('/api/allDecks/');

        if (!$decksList || !is_array($decksList)) {
            Log::warning("Falha ao buscar /api/allDecks/ de One Piece.");
            return;
        }

        $saved = 0;
        foreach ($decksList as $apiDeck) {
            $name = trim($apiDeck['structure_deck_name'] ?? '');
            $code = strtoupper(trim($apiDeck['structure_deck_id'] ?? ''));

            if (empty($name) || empty($code)) {
                continue;
            }

            Set::updateOrCreate(
                [
                    'game_id' => $this->gameId,
                    'code'    => $code,
                ],
                [
                    'name'        => $name,
                    'name_pt'     => $name,
                    'api_id'      => $code,
                    'set_type'    => 'starter_deck',
                    'card_count'  => 0,
                    'is_fanmade'  => false,
                    'digital'     => false,
                    'foil_only'   => false,
                    'updated_at'  => now(),
                ]
            );
            $saved++;
        }

        echo "\n [OnePiece] {$saved} Starter Decks sincronizados com sucesso.\n";
    }

    public function ingestPromos(): void
    {
        Log::info("Sincronizando Promos de One Piece...");
        $promosList = $this->makeRequest('/api/allPromos/');

        if (!$promosList || !is_array($promosList)) {
            return;
        }

        $saved = 0;
        foreach ($promosList as $apiPromo) {
            $name = trim($apiPromo['promo_name'] ?? $apiPromo['name'] ?? '');
            $code = strtoupper(trim($apiPromo['promo_id'] ?? $apiPromo['id'] ?? 'PROMO'));

            if (empty($name)) {
                continue;
            }

            Set::updateOrCreate(
                [
                    'game_id' => $this->gameId,
                    'code'    => $code,
                ],
                [
                    'name'        => $name,
                    'name_pt'     => $name,
                    'api_id'      => $code,
                    'set_type'    => 'promo',
                    'card_count'  => 0,
                    'is_fanmade'  => false,
                    'digital'     => false,
                    'foil_only'   => false,
                    'updated_at'  => now(),
                ]
            );
            $saved++;
        }

        if ($saved > 0) {
            echo "\n [OnePiece] {$saved} Promos sincronizados com sucesso.\n";
        }
    }

    public function makeRequest(string $endpoint, array $queryParams = []): ?array
    {
        $this->respectRateLimit();
        $fullUrl = str_starts_with($endpoint, 'http') ? $endpoint : $this->baseUrl . $endpoint;

        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept'     => 'application/json',
            ])->timeout(60)->withoutVerifying()->get($fullUrl, $queryParams);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("Erro HTTP ({$response->status()}) em {$fullUrl}");
            return null;
        } catch (\Throwable $t) {
            Log::error("Erro de Conexão com API One Piece ({$fullUrl}): " . $t->getMessage());
            return null;
        }
    }

    protected function respectRateLimit(): void
    {
        $delay = $this->rateLimitMs - ((microtime(true) * 1000) - $this->lastRequestTime);
        if ($delay > 0) {
            usleep((int) ($delay * 1000));
        }
        $this->lastRequestTime = microtime(true) * 1000;
    }
}