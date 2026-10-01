<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncMtgPrices extends Command
{
    protected $signature = 'mtg:sync-prices';
    protected $description = 'Sincroniza os preços mundiais usando o Scryfall Bulk Data (JSONL)';

    public function handle()
    {
        $this->info('Consultando metadados de preços diretamente na API...');
        
        $response = Http::withHeaders([
            'User-Agent' => 'VersusTCG-App/1.0',
            'Accept'     => 'application/json'
        ])->get('https://api.scryfall.com/bulk-data/all_cards');

        if (!$response->successful()) {
            $this->error("Falha ao buscar metadados na API do Scryfall: {$response->status()}");
            return;
        }

        $bulkData = $response->json();
        $downloadUri = $bulkData['jsonl_download_uri'] ?? $bulkData['download_uri'] ?? null;

        if (!$downloadUri) {
            $this->error('Não foi possível obter o link de download na resposta da API.');
            return;
        }

        $tempGzPath = storage_path('app/scryfall_all_cards.jsonl.gz');
        $tempJsonlPath = storage_path('app/scryfall_all_cards.jsonl');

        $this->info('Iniciando download do arquivo compactado (Isso pode demorar um pouco)...');

        $fp = fopen($tempGzPath, 'w+');
        $ch = curl_init($downloadUri);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_USERAGENT, 'VersusTCG-App/1.0');
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        $this->info('Download concluído. Descompactando arquivo...');

        $gz = gzopen($tempGzPath, 'rb');
        $jsonl = fopen($tempJsonlPath, 'w');
        
        while (!gzeof($gz)) {
            fwrite($jsonl, gzread($gz, 4096 * 8));
        }
        
        gzclose($gz);
        fclose($jsonl);

        if (file_exists($tempGzPath)) {
            unlink($tempGzPath);
        }

        $this->info('Descompactação concluída. Iniciando sincronização com mtg_prints...');

        // Leitura otimizada linha por linha nativa do PHP (Perfeita para JSONL)
        $handle = fopen($tempJsonlPath, 'r');
        $count = 0;
        $updated = 0;

        if ($handle) {
            DB::beginTransaction();

            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if (empty($line)) continue;

                $card = json_decode($line, true);
                $count++;

                $cardId = $card['id'] ?? null;
                $cardPrices = $card['prices'] ?? null;

                if ($cardId && $cardPrices) {
                    $affected = DB::table('mtg_prints')
                        ->where('api_id', $cardId)
                        ->update([
                            'prices' => json_encode($cardPrices),
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
            fclose($handle);
        }
        
        if (file_exists($tempJsonlPath)) {
            unlink($tempJsonlPath);
        }

        $this->info("Sincronização Finalizada!");
        $this->info("Cartas processadas: {$count}");
        $this->info("Preços atualizados com sucesso: {$updated}");
    }
}