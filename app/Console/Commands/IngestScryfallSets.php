<?php

namespace App\Console\Commands;

use App\Services\ScryfallApi;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use App\Models\Game;
use App\Models\Set; 
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IngestScryfallSets extends Command
{
    protected $signature = 'scryfall:ingest'; 
    protected $description = 'Ingests all sets data from Scryfall API.';

    public function handle() // Parâmetro ScryfallApi removido para evitar erro de DI
    {
        $this->info('Starting Scryfall SETS ingestion...');
        
        // Busca a configuração do jogo Magic no DB
        $magicGame = Game::where('url_slug', 'magic')->first();

        if (!$magicGame) {
            $this->error('Magic: The Gathering not found. Por favor, garanta que o registro do jogo Magic exista na tabela games.');
            return self::FAILURE;
        }

        // --- CORREÇÃO DO ERRO DE DEPENDÊNCIA (BindingResolutionException) ---
        // Instanciação manual do ScryfallApi usando os parâmetros do Game model.
        try {
            $scryfallApi = new ScryfallApi(
                $magicGame->api_url,
                $magicGame->rate_limit_ms,
                $magicGame->id
            );
        } catch (\Throwable $e) {
            $this->error("Erro ao instanciar o ScryfallApi: " . $e->getMessage());
            return self::FAILURE;
        }
        // --- FIM DA CORREÇÃO ---

        $setsData = $scryfallApi->getAllSets();

        if (empty($setsData['data'])) {
            $this->error('Failed to retrieve sets data.');
            return self::FAILURE;
        }

        $this->output->progressStart(count($setsData['data']));
        $setsToInsert = [];

        foreach ($setsData['data'] as $set) {
            $setsToInsert[] = [
                'game_id' => $magicGame->id,
                'mtg_scryfall_id' => $set['id'],
                'mtg_icon_svg_uri' => $set['icon_svg_uri'] ?? null,
                'code' => $set['code'],
                'name' => $set['name'],
                'set_type' => $set['set_type'],
                'released_at' => $set['released_at'] ?? null,
                'card_count' => $set['card_count'] ?? 0,
                'digital' => $set['digital'],
                'foil_only' => $set['foil_only'],
                
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $this->output->progressAdvance();
        }

        Set::upsert($setsToInsert, ['mtg_scryfall_id', 'game_id'], [
            'name', 'set_type', 'card_count', 'released_at', 'mtg_icon_svg_uri',
            'digital', 'foil_only', 'code'
        ]);

        $this->output->progressFinish();
        $this->info('Successfully ingested ' . count($setsData['data']) . ' sets into the database.');
        $this->info('Próximo passo: execute DELETE FROM sets WHERE game_id != 1; e depois php artisan scryfall:ingest-cards');
        return self::SUCCESS;
    }
}