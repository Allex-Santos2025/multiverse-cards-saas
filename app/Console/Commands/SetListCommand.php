<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Set;
use App\Models\Game; // Para garantir que pegamos apenas sets de Magic

/**
 * Comando para listar todos os Sets de Magic: The Gathering salvos no banco de dados.
 */
class SetListCommand extends Command
{
    /**
     * O nome e a assinatura do comando Artisan.
     * @var string
     */
    protected $signature = 'app:list-sets';

    /**
     * A descrição do comando console.
     * @var string
     */
    protected $description = 'Lista todos os sets de Magic: The Gathering salvos no banco de dados e exibe a contagem total.';

    /**
     * Executa o comando console.
     */
    public function handle()
    {
        // Define o Game ID para Magic (assumindo que o ID 1 é Magic como no SetListCommand anterior)
        // No seu sistema real, você deve buscar isso dinamicamente se necessário.
        $gameId = 1; 

        // Tenta encontrar o nome do jogo
        $gameName = 'Magic: The Gathering';
        try {
            // Se você tiver um modelo Game:
            $game = Game::find($gameId);
            if ($game) {
                $gameName = $game->name;
            }
        } catch (\Throwable $e) {
            // Se o modelo Game não existir, usa o padrão.
        }

        $this->info("Buscando Sets para o jogo: {$gameName} (ID: {$gameId}).");

        // Busca todos os Sets do jogo.
        // A ordenação é feita pela coluna 'released_at' (data de lançamento), que é a forma mais fácil de conferir.
        $sets = Set::where('game_id', $gameId)
                    ->orderBy('released_at', 'desc')
                    ->get();
        
        $totalSets = $sets->count();

        if ($totalSets === 0) {
            $this->error("Nenhum Set encontrado no banco de dados para o Game ID: {$gameId}.");
            return 1;
        }

        $this->comment("Contagem Total de Sets Encontrados: {$totalSets}");

        // Prepara os dados para a exibição em tabela
        $headers = ['Código MTG', 'Nome', 'Tipo', 'Cards', 'Lançamento', 'ID Scryfall'];
        $data = $sets->map(function ($set) {
            // Usando os nomes exatos das colunas do DB: released_at, set_type, card_count
            $releaseDate = $set->released_at 
                ? date('Y-m-d', strtotime($set->released_at)) 
                : 'N/A';
            
            return [
                'mtg_code' => $set->mtg_code,
                'name' => $set->name,
                'mtg_set_type' => $set->set_type,      
                'mtg_card_count' => $set->card_count, 
                'mtg_released_at' => $releaseDate,
                'mtg_scryfall_id' => $set->mtg_scryfall_id,
            ];
        });

        // Exibe a tabela
        $this->table($headers, $data->toArray());

        $this->comment("Total de Sets no banco de dados: {$totalSets}");

        return 0;
    }
}