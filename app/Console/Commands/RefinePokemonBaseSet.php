<?php

namespace App\Console\Commands;

use App\Models\Set;
use App\Models\Catalog\CatalogPrint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RefinePokemonBaseSet extends Command
{
    protected $signature = 'pokemon:refine-base-set
                            {--force-download : Força o rebaixamento de imagens existentes}';

    protected $description = 'Audita e preenche lacunas de imagens e traduções especificamente para o Base Set (BASE1) de Pokémon TCG.';

    protected string $baseUrl = 'https://api.tcgdex.net/v2';

    public function handle(): int
    {
        $set = Set::where('game_id', 2)
            ->where(function ($q) {
                $q->where('code', 'BASE1')
                  ->orWhere('api_id', 'base1');
            })->first();

        if (!$set) {
            $this->error("Base Set (BASE1) não encontrado na tabela sets para Pokémon.");
            return self::FAILURE;
        }

        $this->info("=== INICIANDO REFINAMENTO CIRÚRGICO: BASE SET (BASE1) ===");

        $locales = ['en', 'fr', 'de', 'it', 'pt', 'es'];
        $apiSetId = 'base1';
        $setCode = 'BASE1';

        // Mapeia os prints canônicos em inglês
        $enPrints = CatalogPrint::where('set_id', $set->id)
            ->where('language_code', 'en')
            ->with(['specific', 'concept'])
            ->get()
            ->keyBy('collector_number');

        if ($enPrints->isEmpty()) {
            $this->error("Nenhum print canônico [en] encontrado para o Base Set.");
            return self::FAILURE;
        }

        $this->info("Total de cartas canônicas [en] no Base Set: " . $enPrints->count());

        $totalDownloads = 0;
        $totalUpdated = 0;

        foreach ($locales as $locale) {
            $this->line("\n--- Processando idioma: [{$locale}] ---");

            // Verifica se o set existe na API para este idioma
            $setRes = Http::timeout(15)->get("{$this->baseUrl}/{$locale}/sets/{$apiSetId}");
            $localeExists = $setRes->successful() && !empty($setRes->json()['cards']);

            foreach ($enPrints as $number => $enPrint) {
                $cardApiId = "{$apiSetId}-{$number}";

                $print = CatalogPrint::where('set_id', $set->id)
                    ->where('collector_number', $number)
                    ->where('language_code', $locale)
                    ->with('specific')
                    ->first();

                if (!$print) {
                    continue;
                }

                // Tenta buscar dados específicos do card na API para este idioma
                $apiData = null;
                if ($localeExists) {
                    $cardRes = Http::timeout(10)->get("{$this->baseUrl}/{$locale}/cards/{$cardApiId}");
                    if ($cardRes->successful()) {
                        $apiData = $cardRes->json();
                    }
                }

                // Caminho unificado da imagem na pasta do idioma: card_images/Pokemon/BASE1/{locale}/base1-1-{locale}.webp
                $fileName = Str::slug("{$cardApiId}-{$locale}") . ".webp";
                $targetRelPath = "card_images/Pokemon/{$setCode}/{$locale}/{$fileName}";
                $targetFullPath = public_path($targetRelPath);

                // Baixa imagem se houver URL na API
                $rawImgUrl = $apiData['image'] ?? null;
                if ($rawImgUrl) {
                    $imgUrl = Str::endsWith($rawImgUrl, '/high.webp') ? $rawImgUrl : "{$rawImgUrl}/high.webp";
                    if (!File::exists($targetFullPath) || $this->option('force-download')) {
                        if ($this->downloadFile($imgUrl, $targetFullPath)) {
                            $totalDownloads++;
                        }
                    }
                }

                // Fallback de imagem para EN se a pasta do idioma não tiver o arquivo físico
                $enFileName = Str::slug("{$cardApiId}-en") . ".webp";
                $enTargetRelPath = "card_images/Pokemon/{$setCode}/en/{$enFileName}";
                
                $finalImagePath = File::exists($targetFullPath)
                    ? $targetRelPath
                    : (File::exists(public_path($enTargetRelPath)) ? $enTargetRelPath : $print->image_path);

                // Atualiza dados textuais (com fallback inteligente para EN se a API retornar vazio)
                $enSpecific = $enPrint->specific;
                $printedName = $apiData['name'] ?? $enPrint->printed_name;
                $rarity = $apiData['rarity'] ?? $enPrint->rarity;
                $flavorText = $apiData['description'] ?? $enSpecific?->flavor_text;
                $attacks = $apiData['attacks'] ?? $enSpecific?->attacks;
                $abilities = $apiData['abilities'] ?? $enSpecific?->abilities;

                $print->update([
                    'printed_name' => $printedName,
                    'rarity'       => Str::lower($rarity),
                    'image_path'   => $finalImagePath,
                ]);

                if ($print->specific) {
                    $print->specific->update([
                        'rarity'      => $rarity,
                        'artist'      => $apiData['illustrator'] ?? $enSpecific?->artist,
                        'flavor_text' => $flavorText,
                        'attacks'     => $attacks,
                        'abilities'   => $abilities,
                        'level'       => $apiData['level'] ?? $enSpecific?->level,
                        'images'      => $rawImgUrl ? [
                            'normal' => "{$rawImgUrl}/high.webp",
                            'small'  => "{$rawImgUrl}/low.webp",
                        ] : $enSpecific?->images,
                    ]);
                }

                $totalUpdated++;
            }
            $this->info("   [{$locale}] Concluído.");
        }

        $this->info("\n=== REFINAMENTO DO BASE SET FINALIZADO ===");
        $this->info("Total de registros atualizados: {$totalUpdated}");
        $this->info("Total de imagens baixadas/verificadas: {$totalDownloads}");

        return self::SUCCESS;
    }

    protected function downloadFile(string $url, string $fullPath): bool
    {
        try {
            File::ensureDirectoryExists(dirname($fullPath));
            $res = Http::timeout(20)->retry(2, 500)->sink($fullPath)->get($url);
            if ($res->successful() && File::exists($fullPath) && File::size($fullPath) > 0) {
                return true;
            }
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        } catch (\Throwable $e) {
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }
        return false;
    }
}
