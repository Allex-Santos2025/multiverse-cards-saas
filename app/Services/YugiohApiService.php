<?php

namespace App\Services;

use App\Models\Set;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YugiohApiService
{
    public function __construct(
        protected string $apiUrl = 'https://db.ygoprodeck.com/api/v7',
        protected ?int $rateLimitMs = 100,
        protected int $gameId = 3,
        protected ?string $urlSlug = 'yugioh'
    ) {}

    public function runIngestionJob(): void
    {
        Log::info("[YugiohApiService] A iniciar o processo de ingestão para Yu-Gi-Oh! (Game ID: {$this->gameId})");
        $this->syncSets();
    }

    public function syncSets(): array
    {
        Log::info("[YugiohApiService] A procurar a lista oficial de coleções na YGOPRODeck...");

        $response = Http::timeout(60)->get("{$this->apiUrl}/cardsets.php");

        if (!$response->successful()) {
            Log::error("[YugiohApiService] Falha ao consultar /cardsets.php. Status: {$response->status()}");
            return [];
        }

        $sets = $response->json();
        $processed = 0;

        foreach ($sets as $setData) {
            $rawName = trim($setData['set_name'] ?? '');
            $cleanName = html_entity_decode($rawName, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $baseCode = strtoupper(trim($setData['set_code'] ?? ''));
            $totalCards = (int)($setData['num_of_cards'] ?? 0);
            $releaseDate = !empty($setData['tcg_date']) ? $setData['tcg_date'] : null;
            $apiId = Str::slug($rawName);

            if (!$cleanName || !$baseCode) {
                continue;
            }

            // 1. Verifica se o set já existe pelo api_id ou pelo nome
            $existing = Set::where('game_id', $this->gameId)
                ->where(function ($q) use ($apiId, $cleanName, $rawName) {
                    $q->where('api_id', $apiId)
                      ->orWhere('name', $cleanName)
                      ->orWhere('name', $rawName);
                })->first();

            if ($existing) {
                $existing->update([
                    'name'        => $cleanName,
                    'api_id'      => $apiId,
                    'card_count'  => $totalCards,
                    'released_at' => $releaseDate ?? $existing->released_at,
                ]);
                $processed++;
                continue;
            }

            // 2. Gera código desambiguado se o baseCode já pertencer a outro produto
            $finalCode = $baseCode;
            $counter = 2;
            while (Set::where('game_id', $this->gameId)->where('code', $finalCode)->exists()) {
                $finalCode = "{$baseCode}-{$counter}";
                $counter++;
            }

            Set::create([
                'game_id'     => $this->gameId,
                'name'        => $cleanName,
                'code'        => $finalCode,
                'api_id'      => $apiId,
                'card_count'  => $totalCards,
                'released_at' => $releaseDate,
            ]);

            $processed++;
        }

        Log::info("[YugiohApiService] {$processed} coleções de Yu-Gi-Oh! sincronizadas com sucesso.");
        return $sets;
    }
}