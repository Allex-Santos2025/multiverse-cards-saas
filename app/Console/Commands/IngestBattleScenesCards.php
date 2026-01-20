<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Set;
use App\Models\Card;
use App\Models\CardFunctionality;
use App\Services\BattleScenesScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IngestBattleScenesCards extends Command
{
    // Permite baixar um set específico pelo código (ex: "UM")
    protected $signature = 'bs:ingest-cards 
                            {--set-code= : Opcional. Código exato do set no banco (ex: "UM") para baixar.}
                            {--force : Força atualização de imagens}';
                            
    protected $description = 'Ingere cartas de Battle Scenes lendo os Sets do banco de dados.';

    protected ?Game $game;

    public function handle(BattleScenesScraper $scraper)
    {
        $this->info("--- INGESTÃO BATTLE SCENES (CARTAS) ---");

        // 1. Configuração do Game
        $this->game = Game::where('name', 'Battle Scenes')->first();
        if (!$this->game) {
            $this->error("Game 'Battle Scenes' não encontrado no banco.");
            return self::FAILURE;
        }
        
        $this->info("Game ID detectado: " . $this->game->id); // Log de segurança

        // 2. Busca Sets no Banco de Dados
        $query = Set::where('game_id', $this->game->id);

        if ($setCode = $this->option('set-code')) {
            $query->where('code', $setCode);
        }

        $sets = $query->get();

        if ($sets->isEmpty()) {
            $this->error("Nenhum Set encontrado no banco com o código informado.");
            return self::FAILURE;
        }

        $this->info("Encontrados " . $sets->count() . " Sets para processar.");

        foreach ($sets as $set) {
            $this->processSet($set, $scraper);
        }

        return self::SUCCESS;
    }

    protected function processSet(Set $set, BattleScenesScraper $scraper)
    {
        $this->info("\nIniciando Set: [{$set->code}] {$set->name}");

        // Gera a URL de debug para o usuário conferir
        $debugUrl = "https://www.magicjebb.com.br/site/busca_avancada_bs.php?serie=" . urlencode($set->name) . "&formato=detalhes&pag=1&exibicaobs=lista&enviar=Buscar";
        $this->line("   -> Link de Debug: $debugUrl");

        $count = $this->runScraperLoop($set, $set->name, $scraper);

        // TENTATIVA DE FALLBACK (ISO-8859-1) para nomes com acentos
        if ($count === 0) {
            $isoName = mb_convert_encoding($set->name, 'ISO-8859-1', 'UTF-8');
            if ($isoName !== $set->name) {
                $this->warn("   -> Tentando novamente com codificação ISO-8859-1...");
                $count = $this->runScraperLoop($set, $isoName, $scraper);
            }
        }

        if ($count === 0) {
            $this->warn("   -> FALHA: Nenhuma carta encontrada. Verifique o link de debug acima.");
        } else {
            $this->info("\n   -> Set Finalizado. Total de cartas: $count");
        }
    }

    protected function runScraperLoop(Set $set, string $searchName, BattleScenesScraper $scraper): int
    {
        $count = 0;
        foreach ($scraper->scrapeCardsForSet($searchName, $set->name) as $cardData) {
            $this->ingestCard($cardData, $set);
            $count++;
        }
        return $count;
    }

    protected function ingestCard(array $data, Set $set)
    {
        // 1. Cria/Atualiza a Funcionalidade (Oracle)
        $functionality = CardFunctionality::updateOrCreate(
            [
                'game_id' => $this->game->id, // ID 4
                'bs_name' => $data['name'], 
                'bs_alter_ego' => $data['bs_alter_ego'] ?? null,
            ],
            [
                'searchable_names' => [$data['name']], 
                
                // Dados Específicos
                'bs_type_line' => $data['bs_type_line'],
                'bs_affiliation' => $data['bs_affiliation'],
                'bs_power' => $data['bs_power'],
                'bs_toughness' => $data['bs_toughness'],
                'bs_cost' => $data['bs_cost'],
                'bs_rules_text' => $data['bs_rules_text'],
                'bs_flavor_text' => $data['bs_flavor_text'] ?? null,
            ]
        );

        // 2. Baixa Imagem (Sempre retorna o caminho)
        $localPath = $this->downloadImage($data['image_url'], $set->code, $data['name']);

        // 3. Cria/Atualiza o Print (Card)
        // CORREÇÃO: Adicionado game_id explicitamente no Card para evitar default=1
        Card::updateOrCreate(
            [
                'set_id' => $set->id,
                'bs_collection_number' => $data['bs_collection_number'],
            ],
            [
                'game_id' => $this->game->id, // <--- GARANTE QUE O PRINT SEJA BATTLE SCENES (ID 4)
                'card_functionality_id' => $functionality->id,
                'mtg_language_code' => 'pt', 
                
                'bs_rarity' => $data['bs_rarity'],
                'bs_artist' => $data['bs_artist'],
                
                'local_image_path' => $localPath,
            ]
        );

        // Feedback Visual
        $statusImg = (!empty($data['image_url'])) ? "IMG:URL-FOUND" : "IMG:MANUAL-REQ";
        $statusData = $data['bs_type_line'] ? "DADOS:OK" : "DADOS:VAZIO";
        $this->line("   > Processado: {$data['name']} | {$statusImg} | {$statusData}"); 
    }

    protected function downloadImage(?string $url, string $setCode, string $cardName): string
    {
        $safeSetCode = Str::slug($setCode); 
        $safeName = Str::slug($cardName);
        
        $extension = 'jpg';
        if ($url) {
            $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
            if (isset($pathInfo['extension'])) {
                $extension = strtolower($pathInfo['extension']);
            }
        }

        $fileName = "{$safeName}.{$extension}";
        $relativePath = "card_images/BattleScenes/{$safeSetCode}/{$fileName}";
        $fullPath = public_path($relativePath);

        // Garante a pasta
        if (!File::exists(dirname($fullPath))) {
            File::ensureDirectoryExists(dirname($fullPath));
        }

        // Se URL vazia, retorna caminho para futuro upload
        if (empty($url)) {
            return $relativePath; 
        }

        if (File::exists($fullPath) && !$this->option('force')) {
            return $relativePath;
        }

        try {
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $content = Http::timeout(10)->get($url)->body();
                if ($content) {
                    File::put($fullPath, $content);
                }
            }
        } catch (\Exception $e) {
            // Falha silenciosa
        }
        
        return $relativePath;
    }
}