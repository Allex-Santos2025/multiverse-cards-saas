<?php

namespace App\Console\Commands;

use App\Models\Set;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class ResolveMissingSetIcons extends Command
{
    protected $signature = 'pokemon:resolve-missing-icons';
    protected $description = 'Resolve icones faltantes com variacoes de ID e heranca de sets pais';

    public function handle()
    {
        $missing = Set::where('game_id', 2)->whereNull('icon_svg_uri')->get();
        $this->info("Resolvendo icones para {$missing->count()} sets...");

        // Mapeamento de heranca para subsets que usam o icone da colecao pai
        $parentMap = [
            'swsh9tg'    => 'SWSH09',
            'swsh10tg'   => 'SWSH10',
            'swsh11tg'   => 'SWSH11',
            'swsh12tg'   => 'SWSH12',
            'swsh12.5gg' => 'SWSH12.5',
            'swsh4.5sv'  => 'SWSH04.5',
            'cel25cc'    => 'CEL25',
            '30th-c'     => '30TH',
            'sve'        => 'SVP',
        ];

        // Mapeamento de slugs alternativos na API
        $aliasMap = [
            'sv08'  => 'sv8',
            'sm3.5' => 'sm35',
            'sm7.5' => 'sm75',
        ];

        foreach ($missing as $set) {
            $apiId = strtolower($set->api_id ?: $set->code);

            // 1. Tenta heranca do Set Pai
            if (isset($parentMap[$apiId])) {
                $parentCode = $parentMap[$apiId];
                $parent = Set::where('game_id', 2)
                    ->where(function ($q) use ($parentCode) {
                        $q->where('code', $parentCode)
                          ->orWhere('api_id', strtolower($parentCode));
                    })
                    ->whereNotNull('icon_svg_uri')
                    ->first();

                if ($parent) {
                    $set->update(['icon_svg_uri' => $parent->icon_svg_uri]);
                    $this->info("Set [{$set->code}]: Herdado simbolo do set pai [{$parent->code}].");
                    continue;
                }
            }

            // 2. Tenta aliases alternativos na API da TCGdex
            $candidates = [];
            if (isset($aliasMap[$apiId])) {
                $candidates[] = $aliasMap[$apiId];
            }
            $candidates[] = str_replace('.', '', $apiId);
            $candidates[] = preg_replace('/^([a-z]+)0+([0-9]+)/', '$1$2', $apiId);

            $found = false;
            foreach (array_unique($candidates) as $candId) {
                if ($candId === $apiId) continue;

                $url = "https://api.tcgdex.net/v2/en/sets/{$candId}";
                try {
                    $res = Http::timeout(10)->get($url);
                    if ($res->successful()) {
                        $symbolUrl = $res->json('symbol');
                        if ($symbolUrl) {
                            $relativePath = "set_icons/Pokemon/{$set->code}.png";
                            $fullPath = public_path($relativePath);

                            $imgRes = Http::timeout(15)->sink($fullPath)->get("{$symbolUrl}.png");
                            if (!$imgRes->successful() || File::size($fullPath) === 0) {
                                $imgRes = Http::timeout(15)->sink($fullPath)->get($symbolUrl);
                            }

                            if ($imgRes->successful() && File::exists($fullPath) && File::size($fullPath) > 100) {
                                $set->update(['icon_svg_uri' => '/' . $relativePath]);
                                $this->info("Set [{$set->code}]: Simbolo baixado com alias [{$candId}].");
                                $found = true;
                                break;
                            }
                        }
                    }
                } catch (\Throwable $e) {}
            }
        }

        $remaining = Set::where('game_id', 2)->whereNull('icon_svg_uri')->count();
        $this->info("Concluido! Restam apenas {$remaining} sets sem icone.");

        return self::SUCCESS;
    }
}
