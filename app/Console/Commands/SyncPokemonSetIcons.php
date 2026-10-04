<?php

namespace App\Console\Commands;

use App\Models\Set;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncPokemonSetIcons extends Command
{
    protected $signature = 'pokemon:sync-set-icons';
    protected $description = 'Baixa e atualiza os simbolos oficiais de cada set de Pokemon via TCGdex';

    public function handle()
    {
        $sets = Set::where('game_id', 2)->get();
        $this->info("Iniciando busca de simbolos para {$sets->count()} colecoes...");

        $updated = 0;
        $failed = 0;

        foreach ($sets as $set) {
            $apiId = strtolower($set->api_id ?: $set->code);
            $url = "https://api.tcgdex.net/v2/en/sets/{$apiId}";

            try {
                $res = Http::withHeaders([
                    'User-Agent' => 'multiverse-cards-saas/1.0',
                    'Accept'     => 'application/json',
                ])->timeout(15)->retry(2, 500)->get($url);

                if (!$res->successful()) {
                    $this->warn("Set [{$set->code}] ({$apiId}): Erro HTTP {$res->status()} na API.");
                    $failed++;
                    continue;
                }

                $data = $res->json();
                $symbolUrl = $data['symbol'] ?? null;

                if (!$symbolUrl) {
                    $this->line("Set [{$set->code}]: Nao possui campo 'symbol' na resposta da API.");
                    continue;
                }

                // A CDN da TCGdex entrega imagem original se bater na URL pura ou adicionando .webp/.png
                // Se a URL nao terminar em extensao, testa direto ou usa .png/.webp
                $candidates = [$symbolUrl];
                if (!Str::endsWith($symbolUrl, ['.png', '.webp', '.jpg'])) {
                    $candidates[] = "{$symbolUrl}.png";
                    $candidates[] = "{$symbolUrl}.webp";
                }

                $downloaded = false;
                $relativePath = "set_icons/Pokemon/{$set->code}.png";
                $fullPath = public_path($relativePath);

                if (!File::exists(dirname($fullPath))) {
                    File::ensureDirectoryExists(dirname($fullPath));
                }

                foreach ($candidates as $candUrl) {
                    $imgRes = Http::withHeaders([
                        'User-Agent' => 'multiverse-cards-saas/1.0',
                    ])->timeout(15)->sink($fullPath)->get($candUrl);

                    if ($imgRes->successful() && File::exists($fullPath) && File::size($fullPath) > 100) {
                        $downloaded = true;
                        break;
                    }
                }

                if ($downloaded) {
                    $set->update(['icon_svg_uri' => '/' . $relativePath]);
                    $updated++;
                    $this->info("Set [{$set->code}] simbolo salvo com sucesso!");
                } else {
                    if (File::exists($fullPath)) File::delete($fullPath);
                    $this->warn("Set [{$set->code}]: Falha ao transferir o arquivo de imagem do simbolo.");
                    $failed++;
                }

            } catch (\Throwable $e) {
                $this->error("Set [{$set->code}]: Excecao: " . $e->getMessage());
                $failed++;
            }

            usleep(100000); // 100ms de intervalo
        }

        $this->output->writeln("\n=======================================================");
        $this->info("Concluido! {$updated} simbolos salvos | {$failed} falhas.");
        $this->output->writeln("=======================================================");

        return self::SUCCESS;
    }
}
