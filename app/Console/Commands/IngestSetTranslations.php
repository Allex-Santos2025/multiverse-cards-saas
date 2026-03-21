<?php

namespace App\Console\Commands;

use App\Models\Set;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class IngestSetTranslations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ingest-set-translations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Baixa as traduções oficiais das edições de Magic: The Gathering via MTGJSON e atualiza o banco de dados.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando o download do arquivo SetList do MTGJSON...');

        // Bate no endpoint oficial que lista todas as coleções do Magic
        $response = Http::timeout(60)->get('https://mtgjson.com/api/v5/SetList.json');

        if ($response->failed()) {
            $this->error('Falha ao conectar com a API do MTGJSON. Tente novamente mais tarde.');
            return Command::FAILURE;
        }

        $data = $response->json('data');

        if (!$data) {
            $this->error('O arquivo JSON veio vazio ou em um formato inválido.');
            return Command::FAILURE;
        }

        $this->info('Dados baixados com sucesso! Atualizando as coleções locais...');

        // Cria uma barra de progresso visual no terminal
        $bar = $this->output->createProgressBar(count($data));
        $bar->start();

        $updatedCount = 0;

        foreach ($data as $mtgSet) {
            $code = $mtgSet['code'] ?? null;
            $translations = $mtgSet['translations'] ?? [];

            // Só processa se a edição tiver código e alguma tradução disponível
            if ($code && !empty($translations)) {
                
                // O MTGJSON mapeia o PT-BR exatamente com a chave "Portuguese (Brazil)"
                $namePt = $translations['Portuguese (Brazil)'] ?? $translations['Portuguese'] ?? null;

                // Faz o update silencioso (sem disparar eventos) para não sobrecarregar o sistema
                $updated = Set::where('code', $code)
                    ->update([
                        'name_pt' => $namePt,
                        'translations' => $translations, // O Laravel faz o cast para JSON automaticamente aqui
                    ]);

                // Conta apenas as coleções que nós já tínhamos no banco e que foram atualizadas
                if ($updated) {
                    $updatedCount++;
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info("Operação concluída com sucesso! {$updatedCount} coleções receberam os nomes localizados.");

        return Command::SUCCESS;
    }
}