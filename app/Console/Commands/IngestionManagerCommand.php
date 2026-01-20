<?php

namespace App\Console\Commands;

use App\Models\Game;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IngestionManagerCommand extends Command
{
    /**
     * O nome e a assinatura do comando do console.
     * @var string
     */
    protected $signature = 'ingest:run {--game= : Opcional. ID ou url_slug do game específico para ingestão.}';

    /**
     * A descrição do comando do console.
     * @var string
     */
    protected $description = 'Orquestra o processo de ingestão de dados (Sets e Cards) para todos os TCGs ativos.';

    /**
     * Executa o comando.
     * @return int
     */
    public function handle(): int
    {
        $this->info('Iniciando o Gerenciador de Ingestão (Ingestion Manager)...');

        // 1. Busca todos os jogos ativos ou um jogo específico.
        $query = Game::where('is_active', true);

        if ($gameIdentifier = $this->option('game')) {
            $query->where(function ($q) use ($gameIdentifier) {
                $q->where('id', $gameIdentifier)
                  ->orWhere('url_slug', $gameIdentifier);
            });
            $this->warn("Filtrando para o jogo: {$gameIdentifier}");
        }

        $games = $query->get();

        if ($games->isEmpty()) {
            $this->error('Nenhum TCG ativo encontrado para ingestão.');
            return self::FAILURE;
        }

        $this->info("Encontrados {$games->count()} TCGs ativos para processar.");

        foreach ($games as $game) {
            $this->processGame($game);
        }

        $this->info('Gerenciador de Ingestão finalizado.');
        return self::SUCCESS;
    }

    /**
     * Processa a ingestão para um único Jogo.
     * Este método é o DISTRIBUIDOR que lê a configuração da tabela 'games'.
     * @param Game $game
     */
    protected function processGame(Game $game): void
    {
        $ingestorClass = $game->ingestor_class; // Ex: App\Services\ScryfallApi

        if (empty($ingestorClass) || !class_exists($ingestorClass)) {
            $this->error("Game '{$game->name}' (ID: {$game->id}) não tem uma classe ingestora válida definida: {$ingestorClass}");
            Log::error("Ingestion Manager: Classe ingestora não encontrada para Game ID: {$game->id}");
            return;
        }

        $this->line("--- Processando TCG: {$game->name} (ID: {$game->id}) ---");

        try {
            // 1. Instancia o Serviço de API Específico (ScryfallApi, PokemonTcgApi, etc.).
            // A CLASSE DE INGESTÃO (o Serviço) SÓ RECEBE SUAS PRÓPRIAS CONFIGURAÇÕES.
            $apiService = new $ingestorClass(
                $game->api_url,
                $game->rate_limit_ms,
                $game->id // ESSENCIAL: O ID do jogo é injetado no serviço para uso no saveSets
            );

            // 2. Chama o ponto de entrada principal de ingestão no Serviço ESPECÍFICO.
            // Aqui, o ScryfallApi::runIngestionJob() será chamado, executando a lógica SÓ do Magic.
            $apiService->runIngestionJob();

            $this->info("Sucesso na ingestão de '{$game->name}'.");

        } catch (\Throwable $e) {
            $this->error("Erro crítico ao processar '{$game->name}': " . $e->getMessage());
            Log::error("Ingestion Manager: Erro ao processar Game ID {$game->id}", ['exception' => $e]);
        }
    }
}