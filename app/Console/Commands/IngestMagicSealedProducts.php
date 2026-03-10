<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CatalogProduct;
use App\Models\Game;
use Illuminate\Support\Facades\Http;

class IngestMagicSealedProducts extends Command
{
    protected $signature = 'app:ingest-magic-sealed';
    protected $description = 'Ingere todos os produtos selados oficiais de Magic via MTGJSON';

    public function handle()
    {
        $this->info('Iniciando a busca de coleções no MTGJSON...');

        // 1. Pega o ID do Magic no banco de dados
        $magicGame = Game::where('name', 'like', '%Magic%')->first();
        $magicGameId = $magicGame ? $magicGame->id : 1;

        // 2. Busca a lista de todos os Sets (Coleções)
        $response = Http::get('https://mtgjson.com/api/v5/SetList.json');
        
        if (!$response->successful()) {
            $this->error('Falha ao conectar no MTGJSON.');
            return;
        }

        $sets = $response->json()['data'];
        $this->info('Encontradas ' . count($sets) . ' coleções. Verificando produtos selados...');

        // Barra de progresso baseada no número de coleções
        $bar = $this->output->createProgressBar(count($sets));
        $bar->start();

        $produtosCadastrados = 0;

        // 3. Varre cada coleção para pegar os selados
        foreach ($sets as $set) {
            $setCode = $set['code'];
            
            // Pega os detalhes específicos da coleção
            $setDataResponse = Http::get("https://mtgjson.com/api/v5/{$setCode}.json");
            
            if ($setDataResponse->successful()) {
                $setData = $setDataResponse->json()['data'];

                // Verifica se a coleção tem a chave "sealedProduct"
                if (isset($setData['sealedProduct']) && is_array($setData['sealedProduct'])) {
                    
                    foreach ($setData['sealedProduct'] as $product) {
                        // Salva no banco usando o UUID do MTGJSON como "barcode" para evitar duplicatas
                        CatalogProduct::updateOrCreate(
                            ['barcode' => $product['uuid']], 
                            [
                                'game_id' => $magicGameId,
                                'type' => 'sealed',
                                'name' => $product['name'],
                                'description' => "Produto selado da coleção {$setData['name']} ({$setCode})",
                                // O MTGJSON não fornece a URL da imagem da caixa diretamente, 
                                // então deixamos nulo para o lojista (ou você) subir a foto depois,
                                // garantindo que pelo menos o produto exista para venda imediata.
                                'image_path' => null, 
                                'is_active' => true,
                            ]
                        );
                        $produtosCadastrados++;
                    }
                }
            }
            
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nCarga finalizada! {$produtosCadastrados} produtos selados de Magic foram cadastrados no catálogo.");
    }
}
