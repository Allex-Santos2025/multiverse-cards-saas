<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CatalogProduct;
use App\Models\Game; // Ajuste se o seu model de jogo tiver outro nome
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File; // Usando File para manipular a pasta public
use Illuminate\Support\Str;

class IngestInitialProducts extends Command
{
    protected $signature = 'app:ingest-products';
    protected $description = 'Ingere a carga inicial de produtos selados e acessórios no catálogo global';

    public function handle()
    {
        $this->info('Iniciando a ingestão de produtos...');

        // O nome da pasta principal que ficará ao lado de 'card_images'
        $baseDirectory = 'product_images';

        // Buscar o ID do Magic no seu banco (ajuste o nome 'Magic: The Gathering' se necessário)
        $magicGame = Game::where('name', 'like', '%Magic%')->first();
        $magicGameId = $magicGame ? $magicGame->id : 1; 

        // A Carga Inicial (Mock de dados para o MVP)
        $productsToIngest = [
            // ACESSÓRIOS GLOBAIS (game_id = null)
            [
                'game_id' => null,
                'type' => 'accessory',
                'name' => 'Dragon Shield Matte Black (100 sleeves)',
                'description' => 'Protetores de carta de alta qualidade, cor preta fosca.',
                'barcode' => '5706569110015',
                'image_url' => 'https://static.wikia.nocookie.net/mtgsalvation_gamepedia/images/f/f8/Magic_card_back.jpg/revision/latest?cb=20140813141013'
            ],
            [
                'game_id' => null,
                'type' => 'accessory',
                'name' => 'Dragon Shield Matte Clear (100 sleeves)',
                'description' => 'Protetores de carta transparentes foscos.',
                'barcode' => '5706569110008',
                'image_url' => 'https://static.wikia.nocookie.net/mtgsalvation_gamepedia/images/f/f8/Magic_card_back.jpg/revision/latest?cb=20140813141013'
            ],
            
            // PRODUTOS DE MAGIC (game_id = $magicGameId)
            [
                'game_id' => $magicGameId,
                'type' => 'sealed',
                'name' => 'Booster Box: Outlaws of Thunder Junction (Play Booster)',
                'description' => 'Caixa contendo 36 Play Boosters da edição Outlaws of Thunder Junction.',
                'barcode' => '0195166254402',
                'image_url' => 'https://static.wikia.nocookie.net/mtgsalvation_gamepedia/images/f/f8/Magic_card_back.jpg/revision/latest?cb=20140813141013'
            ],
            [
                'game_id' => $magicGameId,
                'type' => 'sealed',
                'name' => 'Commander Deck: Grand Larceny (Outlaws of Thunder Junction)',
                'description' => 'Deck de Commander pré-construído.',
                'barcode' => '0195166254556',
                'image_url' => 'https://static.wikia.nocookie.net/mtgsalvation_gamepedia/images/f/f8/Magic_card_back.jpg/revision/latest?cb=20140813141013'
            ]
        ];

        $bar = $this->output->createProgressBar(count($productsToIngest));
        $bar->start();

        foreach ($productsToIngest as $data) {
            $dbImagePath = null;

            if (!empty($data['image_url'])) {
                try {
                    $imageContents = Http::get($data['image_url'])->body();
                    
                    // Lógica para decidir a subpasta: se não tiver game_id, é 'global'. Senão, é 'magic' (ou outro jogo futuramente)
                    $subFolder = is_null($data['game_id']) ? 'global' : 'magic';
                    
                    $filename = Str::slug($data['name']) . '-' . uniqid() . '.jpg';
                    
                    // Monta o caminho exato dentro da pasta public
                    // Ex: public/product_images/global
                    $absoluteFolderPath = public_path($baseDirectory . '/' . $subFolder);
                    
                    // Se a pasta não existir (ex: é a primeira vez rodando), ele cria a pasta
                    if (!File::exists($absoluteFolderPath)) {
                        File::makeDirectory($absoluteFolderPath, 0755, true);
                    }
                    
                    // Salva a imagem na pasta correta
                    File::put($absoluteFolderPath . '/' . $filename, $imageContents);
                    
                    // Caminho que vai pro banco de dados (ex: product_images/global/dragon-shield.jpg)
                    $dbImagePath = $baseDirectory . '/' . $subFolder . '/' . $filename;

                } catch (\Exception $e) {
                    $this->error("\nErro ao baixar imagem para: {$data['name']}");
                }
            }

            CatalogProduct::updateOrCreate(
                ['barcode' => $data['barcode']],
                [
                    'game_id' => $data['game_id'],
                    'type' => $data['type'],
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'image_path' => $dbImagePath,
                    'is_active' => true,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nCarga inicial concluída com sucesso! Imagens foram separadas e salvas em public/product_images/");
    }
}