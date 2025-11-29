<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ListScryfallSets extends Command
{
    /**
     * O nome e a assinatura do comando do console.
     *
     * @var string
     */
    protected $signature = 'debug:list-sets';

    /**
     * A descrição do console command.
     *
     * @var string
     */
    protected $description = 'Lista Sets do Scryfall, focando em Código e Card Count para diagnóstico.';

    protected $scryfallBaseUrl = 'https://api.scryfall.com';

    /**
     * Executa o comando do console.
     */
    public function handle()
    {
        $this->info('--- Iniciando Diagnóstico de Dados de Sets (API Scryfall) ---');
        $this->warn('Isto apenas lista. Não salva nada no banco de dados.');

        $response = Http::get("{$this->scryfallBaseUrl}/sets");

        if (!$response->successful()) {
            $this->error("Falha ao buscar Sets do Scryfall: {$response->status()}.");
            return Command::FAILURE;
        }

        $setsData = $response->json()['data'];
        $setsList = [];

        foreach ($setsData as $setData) {
            // Extrai APENAS os campos essenciais para o diagnóstico
            $setsList[] = [
                'Code' => $setData['code'] ?? 'N/A',
                'Name' => $setData['name'] ?? 'Set Sem Nome',
                'Card Count' => $setData['card_count'] ?? 0, // O CAMPO CRÍTICO
                'Set Type' => $setData['set_type'] ?? 'unknown',
            ];
        }

        // Exibe a tabela completa de Sets
        $this->table(
            ['Code', 'Name', 'Card Count', 'Set Type'],
            $setsList
        );

        $this->info("✅ Diagnóstico concluído. Total de Sets encontrados: " . count($setsList));

        return Command::SUCCESS;
    }
}