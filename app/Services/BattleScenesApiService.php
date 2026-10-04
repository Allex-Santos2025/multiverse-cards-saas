<?php

namespace App\Services;

use App\Models\Set;
use App\Services\BattleScenesScraper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BattleScenesApiService
{
    protected string $baseUrl;
    protected int $rateLimitMs;
    protected int $gameId;
    protected string $gameSlug;

    public function __construct(string $baseUrl, int $rateLimitMs, int $gameId, string $gameSlug = 'battle-scenes')
    {
        $this->baseUrl = $baseUrl;
        $this->rateLimitMs = $rateLimitMs;
        $this->gameId = $gameId;
        $this->gameSlug = $gameSlug;

        Log::info("BattleScenesApiService instanciado. GameID: {$gameId}");
    }

    public function runIngestionJob(): void
    {
        Log::info("=== Iniciando Ingestão de Sets: Battle Scenes (Game ID: {$this->gameId}) ===");
        $this->ingestSets();
        Log::info("=== Ingestão de Sets de Battle Scenes Finalizada ===");
    }

    protected function ingestSets(): void
    {
        $scraper = app(BattleScenesScraper::class);
        $setsList = $scraper->getSetsList();

        if (empty($setsList)) {
            Log::error("Falha crítica: O scraper não retornou coleções de Battle Scenes.");
            return;
        }

        $count = count($setsList);
        Log::info("Encontrados {$count} sets de Battle Scenes no MagicJebb. Processando...");

        $saved = 0;
        foreach ($setsList as $item) {
            $name = trim($item['name']);
            $originalCode = (string) $item['original_code'];
            $dbCode = $item['db_code'] ?? Str::slug($name);

            // Gera código curto amigável se necessário
            $words = explode(' ', preg_replace('/[^a-zA-Z0-9\s]/', '', $name));
            $shortCode = '';
            foreach ($words as $w) {
                if (!empty($w)) {
                    $shortCode .= strtoupper($w[0]);
                }
            }
            $shortCode = substr($shortCode, 0, 10) ?: strtoupper(substr($dbCode, 0, 10));

            Set::updateOrCreate(
                [
                    'game_id' => $this->gameId,
                    'api_id'  => $originalCode,
                ],
                [
                    'name'         => $name,
                    'name_pt'      => $name,
                    'code'         => $shortCode,
                    'set_type'     => Str::contains(strtolower($name), 'deck') ? 'deck' : 'expansion',
                    'released_at'  => now()->toDateString(),
                    'card_count'   => 0,
                    'is_fanmade'   => false,
                    'digital'      => false,
                    'foil_only'    => false,
                ]
            );

            $saved++;
        }

        Log::info("Sucesso: {$saved} sets de Battle Scenes salvos/atualizados.");
    }
}
