<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Set;
use App\Services\BattleScenesScraper; // Usa o Service que está no Canvas
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IngestBattleScenesSets extends Command
{
    protected $signature = 'battlescenes:ingest-sets';
    protected $description = 'Ingests the list of Sets (collections) for Battle Scenes via scraping.';
    
    public function handle(BattleScenesScraper $scraper)
    {
        $this->info('Inciando Ingestão de Sets para Battle Scenes...');

        // 1. Obter o ID do Jogo (Obrigatório para o FK)
        $game = Game::where('name', 'Battle Scenes')->first(); 
        
        if (!$game) {
            $this->error("Game com nome 'Battle Scenes' não encontrado. Crie o registro no Admin.");
            return self::FAILURE;
        }

        // 2. Obter a Lista de Sets do Scraper
        $setsToScrape = $scraper->getSetsList();

        if (empty($setsToScrape)) {
            $this->error('Falha ao obter links de sets. O Scraper (magicjebb) retornou uma lista vazia.');
            return self::FAILURE;
        }

        $this->info('Sets encontrados no magicjebb: ' . count($setsToScrape));
        
        // 3. Processar e Salvar no Banco de Dados
        $savedCount = 0;
        
        $this->withProgressBar($setsToScrape, function ($setInfo) use ($game, &$savedCount) {
            
            $fullName = $setInfo['name']; // Ex: "Confronto Aracnídeo - Deck"

            // Gera um código curto (ex: "CAD") pegando a primeira letra de cada palavra
            $words = explode(' ', preg_replace('/[^a-zA-Z0-9\s]/', '', $fullName));
            $generatedCode = '';
            foreach ($words as $word) {
                if (!empty($word)) {
                    $generatedCode .= strtoupper($word[0]);
                }
            }
            
            // Garante que o código não seja longo demais para a coluna (limite de 10)
            $generatedCode = substr($generatedCode, 0, 10);


            // 1. Encontra o Set ou cria uma nova instância em memória
            $set = Set::firstOrNew(
                [ // Chaves de busca
                    'game_id' => $game->id,
                    'code' => $generatedCode, // <-- CORREÇÃO: Usando o código gerado limpo (sem prefixo)
                ]
            );

            // 2. Atribui manualmente todos os campos (isso ignora o $fillable)
            $set->name = $fullName; // <-- Usa o nome completo
            $set->released_at = now()->toDateString();

            // --- Placeholders para campos obrigatórios (NOT NULL) do Scryfall ---
            
            // Usa o código curto gerado para o scryfall_id, em vez do slug do nome completo
            $set->scryfall_id = 'bs-' . $generatedCode; // <-- Mantemos o prefixo aqui, pois scryfall_id DEVE ser único
            
            $set->set_type = 'scraped_set'; 
            $set->card_count = 0; 
            // --- Fim dos Placeholders ---

            
            // 3. Salva no banco
            $wasChanged = $set->isDirty(); // Verifica se algo mudou
            $set->save();
            
            if ($wasChanged) {
                $savedCount++;
            }
        });
        
        // ***** CORREÇÃO DE SINTAXE (Typo) *****
        // Trocado de $this.info para $this->info
        $this->info("Ingestão de Sets concluída. Total de sets salvos/atualizados: {$savedCount}.");
        return self::SUCCESS;
    }
}

