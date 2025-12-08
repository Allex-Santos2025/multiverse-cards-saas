<?php

namespace App\Services;

use App\Models\Set;
use App\Models\Game;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BattleScenesIngestorService
{
    protected ?string $apiUrl;
    protected int $rateLimit;
    protected int $gameId;
    protected BattleScenesScraper $scraper;

    /**
     * O IngestionManagerCommand injeta esses dados automaticamente.
     */
    public function __construct(?string $apiUrl, ?int $rateLimit, int $gameId)
    {
        $this->apiUrl = $apiUrl;
        $this->rateLimit = $rateLimit ?? 1000;
        $this->gameId = $gameId;
        
        // Instancia o Scraper
        $this->scraper = new BattleScenesScraper();
    }

    /**
     * Método padrão chamado pelo IngestionManagerCommand.
     * Responsabilidade única: Garantir que os Sets existam no banco.
     */
    public function runIngestionJob(): void
    {
        Log::channel('ingest')->info("--- [Battle Scenes] Sincronizando Sets (Modo Preservação de Código) ---");

        // 1. Usa o Scraper para pegar a lista bruta do site
        $setsList = $this->scraper->getSetsList();
        
        Log::channel('ingest')->info("Encontrados " . count($setsList) . " sets no MagicJebb.");

        foreach ($setsList as $setData) {
            
            // Tenta encontrar o Set pelo NOME (que é a âncora confiável do Battle Scenes)
            $existingSet = Set::where('game_id', $this->gameId)
                              ->where('name', $setData['name'])
                              ->first();

            if ($existingSet) {
                // Se já existe, NÃO mexemos no 'code'. 
                // Isso preserva os códigos manuais (ex: 'UM', 'BSPO') que você definiu.
                // Apenas atualizamos timestamp se necessário.
                $existingSet->touch(); 
                // Log::channel('ingest')->debug("Set existente encontrado: {$existingSet->name} ({$existingSet->code}). Ignorando update de código.");
            } else {
                // Se NÃO existe, criamos um novo.
                // Aqui sim geramos um código inicial, que você poderá alterar manualmente depois.
                $shortCode = $this->generateShortCode($setData['name']);
                
                Set::create([
                    'game_id' => $this->gameId,
                    'name' => $setData['name'],
                    'code' => $shortCode, // Código sugerido inicial
                    'set_type' => 'expansion',
                    'card_count' => 0, 
                    'released_at' => now(),
                ]);
                
                Log::channel('ingest')->info("Novo Set criado: {$setData['name']} -> Código sugerido: {$shortCode}");
            }
        }

        Log::channel('ingest')->info("--- [Battle Scenes] Sincronização de Sets finalizada ---");
    }

    /**
     * Gera uma sigla de 2 a 4 letras baseada no nome do Set.
     * Usado APENAS para novos sets.
     */
    private function generateShortCode(string $name): string
    {
        $cleanName = Str::replaceFirst('Battle Scenes', '', $name);
        $cleanName = preg_replace('/[^A-Za-z0-9\s]/', '', $cleanName);
        
        $words = explode(' ', trim($cleanName));
        $code = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $code .= strtoupper(substr($word, 0, 1));
            }
        }

        if (strlen($code) < 2) {
            $code = strtoupper(substr(Str::slug($name), 0, 3));
        }

        return substr($code, 0, 4);
    }
}