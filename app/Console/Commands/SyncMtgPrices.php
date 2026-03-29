<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ScryfallApi;
use Illuminate\Support\Facades\DB;
use JsonMachine\Items;

class SyncMtgPrices extends Command
{
    protected $signature = 'mtg:sync-prices';
    protected $description = 'Sincroniza os preços mundiais usando o ScryfallApi Service';

    public function handle()
    {
        $this->info('Configurando conexão com o Scryfall via Banco de Dados...');

        // 1. Localiza as configurações usando o nome correto da coluna: url_slug
        $game = DB::table('games')->where('url_slug', 'magic')->first();

        if (!$game) {
            $this->error('Jogo com url_slug "magic" não encontrado na tabela games.');
            return;
        }

        // 2. Instancia o seu Service com as colunas reais: api_url, rate_limit_ms e url_slug
        $scryfall = new ScryfallApi(
            $game->api_url, 
            $game->rate_limit_ms ?? 100, 
            $game->id, 
            $game->url_slug
        );

        $this->info('Consultando metadados de preços...');
        
        // Chamada ao método que adicionamos ao seu Service
        $bulkData = $scryfall->getBulkDataInfo('default_cards');

        if (!$bulkData || !isset($bulkData['download_uri'])) {
            $this->error('Não foi possível obter o link de download. Verifique a conexão com a API.');
            return;
        }

        $downloadUri = $bulkData['download_uri'];
        $tempPath = storage_path('app/scryfall_default_cards.json');

        $this->info('Iniciando download do arquivo Bulk Data...');

        // 3. Download via CURL com bypass de SSL para o terminal
        $fp = fopen($tempPath, 'w+');
        $ch = curl_init($downloadUri);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_USERAGENT, 'multiverse-cards-saas/1.0');
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        $this->info('Download concluído. Iniciando sincronização com mtg_prints...');

        // 4. Processamento via JsonMachine
        $cards = Items::fromFile($tempPath);
        $count = 0;
        $updated = 0;

        DB::beginTransaction();

        foreach ($cards as $card) {
            $count++;

            if (isset($card->id) && isset($card->prices)) {
                $affected = DB::table('mtg_prints')
                    ->where('api_id', $card->id)
                    ->update([
                        'prices' => json_encode($card->prices),
                        'updated_at' => now(),
                    ]);

                if ($affected) {
                    $updated++;
                }
            }

            if ($count % 1000 === 0) {
                DB::commit();
                DB::beginTransaction();
                $this->line("Processados: {$count} | Atualizados no banco: {$updated}");
            }
        }

        DB::commit();
        
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        $this->info("Sincronização Finalizada!");
        $this->info("Cartas processadas: {$count}");
        $this->info("Preços atualizados com sucesso: {$updated}");
    }
}