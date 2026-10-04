<?php

namespace App\Console\Commands;

use App\Models\Set;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RepairPokemonSetsData extends Command
{
    protected $signature = 'pokemon:repair-sets';
    protected $description = 'Corrige datas de lancamento (released_at) e simbolos dos sets de Pokemon';

    public function handle()
    {
        $sets = Set::where('game_id', 2)->get();
        $this->info("Atualizando dados de {$sets->count()} coleções...");

        $updatedDates = 0;
        $updatedIcons = 0;

        foreach ($sets as $set) {
            $apiId = strtolower($set->api_id ?: $set->code);
            $url = "https://api.tcgdex.net/v2/en/sets/{$apiId}";

            try {
                $res = Http::withHeaders([
                    'User-Agent' => 'multiverse-cards-saas/1.0',
                    'Accept'     => 'application/json',
                ])->timeout(15)->retry(2, 500)->get($url);

                if (!$res->successful()) {
                    continue;
                }

                $data = $res->json();
                $updateData = [];

                // 1. Data de Lancamento Oficial
                if (!empty($data['releaseDate'])) {
                    $updateData['released_at'] = $data['releaseDate'];
                    $updatedDates++;
                }

                // 2. Download e vinculacao do simbolo oficial
                $symbolUrl = $data['symbol'] ?? null;
                if ($symbolUrl) {
                    $relativePath = "set_icons/Pokemon/{$set->code}.png";
                    $fullPath = public_path($relativePath);

                    if (!File::exists(dirname($fullPath))) {
                        File::ensureDirectoryExists(dirname($fullPath));
                    }

                    if (!File::exists($fullPath) || File::size($fullPath) < 100) {
                        $candidates = [
                            $symbolUrl,
                            Str::endsWith($symbolUrl, '.png') ? $symbolUrl : "{$symbolUrl}.png",
                            Str::endsWith($symbolUrl, '.webp') ? $symbolUrl : "{$symbolUrl}.webp",
                        ];

                        foreach ($candidates as $cand) {
                            $imgRes = Http::timeout(15)->sink($fullPath)->get($cand);
                            if ($imgRes->successful() && File::exists($fullPath) && File::size($fullPath) > 100) {
                                break;
                            }
                        }
                    }

                    if (File::exists($fullPath) && File::size($fullPath) > 100) {
                        $updateData['icon_svg_uri'] = '/' . $relativePath;
                        $updatedIcons++;
                    }
                }

                if (!empty($updateData)) {
                    $set->update($updateData);
                    $this->line("Set [{$set->code}] atualizado -> Data: " . ($updateData['released_at'] ?? 'mantida'));
                }

            } catch (\Throwable $e) {
                // Silencia e continua
            }

            usleep(80000); // 80ms
        }

        $this->info("\nConcluído!");
        $this->info("Datas atualizadas: {$updatedDates}");
        $this->info("Símbolos validados/atualizados: {$updatedIcons}");

        return self::SUCCESS;
    }
}
