<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Set;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\Games\Pokemon\PkConcept;
use App\Models\Games\Pokemon\PkPrint;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RefinePokemonSet extends Command
{
    protected $signature = 'pokemon:refine-set
                            {set_code=BASE1 : Código ou api_id do Set (ex: BASE1, base1)}
                            {--locales=en,pt,es,de,fr,it : Idiomas para auditar e refinar}
                            {--force-images : Rebaixa as imagens mesmo se já existirem}';

    protected $description = 'Organiza imagens nas pastas de idioma, unifica conceitos e atualiza traduções/imagens de um Set de Pokémon mantendo os IDs.';

    protected string $baseUrl = 'https://api.tcgdex.net/v2';

    public function handle(): int
    {
        $inputCode = $this->argument('set_code');
        $locales = array_filter(array_map('trim', explode(',', (string)$this->option('locales'))));

        $set = Set::where('game_id', 2)
            ->where(function ($q) use ($inputCode) {
                $q->where('code', strtoupper($inputCode))
                  ->orWhere('api_id', strtolower($inputCode));
            })->first();

        if (!$set) {
            $this->error("Set [{$inputCode}] não encontrado para Pokémon (game_id = 2).");
            return self::FAILURE;
        }

        $apiSetId = strtolower($set->api_id ?: $set->code);
        $setCode = strtoupper($set->code);

        $this->info("=== Refinando Set: [{$setCode}] {$set->name} (ID: {$set->id} | API: {$apiSetId}) ===");

        // 1. Mapeia os prints canônicos em Inglês por collector_number
        $enPrints = CatalogPrint::where('set_id', $set->id)
            ->where('language_code', 'en')
            ->with(['specific', 'concept.specific'])
            ->get()
            ->keyBy('collector_number');

        if ($enPrints->isEmpty()) {
            $this->error("Nenhum print canônico [en] encontrado para o set {$setCode}.");
            return self::FAILURE;
        }

        $this->info("Encontradas {$enPrints->count()} cartas na matriz canônica [en].");

        $updatedTranslations = 0;
        $unifiedConcepts = 0;

        foreach ($locales as $locale) {
            $this->output->writeln("\n-> Auditando idioma: [{$locale}]");

            // Verifica se o set existe na TCGdex neste idioma
            $setRes = Http::timeout(20)->get("{$this->baseUrl}/{$locale}/sets/{$apiSetId}");
            $localeSetExistsInApi = $setRes->successful() && !empty($setRes->json()['cards']);

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

                // A) Unifica o concept_id com o conceito canônico em Inglês (evita conceitos duplicados por idioma)
                if ($locale !== 'en' && $print->concept_id !== $enPrint->concept_id) {
                    $oldConceptId = $print->concept_id;
                    $print->concept_id = $enPrint->concept_id;
                    $print->save();
                    $unifiedConcepts++;

                    if (!CatalogPrint::where('concept_id', $oldConceptId)->exists()) {
                        $orphan = CatalogConcept::find($oldConceptId);
                        if ($orphan) {
                            if ($orphan->specific) {
                                $orphan->specific->delete();
                            }
                            $orphan->delete();
                        }
                    }
                }

                // B) Consulta dados traduzidos na TCGdex se disponível
                $apiData = null;
                if ($localeSetExistsInApi) {
                    $cardRes = Http::timeout(15)->get("{$this->baseUrl}/{$locale}/cards/{$cardApiId}");
                    if ($cardRes->successful()) {
                        $apiData = $cardRes->json();
                    }
                }

                // C) Garante o image_path apontando rigorosamente para a subpasta do idioma (/en/ ou /{locale}/)
                $fileName = Str::slug("{$cardApiId}-{$locale}") . ".webp";
                $targetRelPath = "card_images/Pokemon/{$setCode}/{$locale}/{$fileName}";
                $targetFullPath = public_path($targetRelPath);

                $enFileName = Str::slug("{$cardApiId}-en") . ".webp";
                $enTargetRelPath = "card_images/Pokemon/{$setCode}/en/{$enFileName}";

                $finalImagePath = File::exists($targetFullPath)
                    ? $targetRelPath
                    : (File::exists(public_path($enTargetRelPath)) ? $enTargetRelPath : $print->image_path);

                // D) Atualiza dados traduzidos ou aplica fallback da matriz EN
                $enSpecific = $enPrint->specific;

                $printedName = $apiData['name'] ?? ($locale === 'en' ? $enPrint->printed_name : $enPrint->printed_name);
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
                        'images'      => ($apiData['image'] ?? null) ? [
                            'normal' => "{$apiData['image']}/high.webp",
                            'small'  => "{$apiData['image']}/low.webp",
                        ] : $enSpecific?->images,
                    ]);
                }

                $updatedTranslations++;
                $this->output->write("\r   Cartas auditadas: {$number}/{$enPrints->count()} | Atualizadas: {$updatedTranslations}");
            }
            $this->output->writeln("");
        }

        $this->info("\n=== Resumo do Refinamento [{$setCode}] ===");
        $this->info("Conceitos unificados à matriz EN: {$unifiedConcepts}");
        $this->info("Registros de prints atualizados: {$updatedTranslations}");

        return self::SUCCESS;
    }
}