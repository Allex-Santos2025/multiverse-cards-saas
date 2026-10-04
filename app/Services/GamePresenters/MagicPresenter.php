<?php

namespace App\Services\GamePresenters;

use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MagicPresenter implements GamePresenterInterface
{
    public function __construct(
        protected ?object $concept = null,
        protected ?CatalogPrint $activePrint = null,
        protected ?string $localizedName = null
    ) {}

    public function getPrimaryTitle(): string
    {
        return $this->localizedName ?: ($this->concept->name ?? '');
    }

    public function getSecondaryTitle(): ?string
    {
        $conceptName = $this->concept->name ?? '';
        if ($this->localizedName && $this->localizedName !== $conceptName) {
            return $conceptName;
        }
        return null;
    }

    public function hasSecondaryTitle(): bool
    {
        return !empty($this->getSecondaryTitle());
    }

    public function getCollectorNumberLabel(?CatalogPrint $print = null): ?string
    {
        $target = $print ?: $this->activePrint;
        return $target && !empty($target->collector_number) ? "#{$target->collector_number}" : null;
    }

    public function getDefaultIconType(): string
    {
        return 'magic';
    }

    public function resolveMatchingPrints(Collection $allPrints, string $conceptSlug): Collection
    {
        $specificIds = $allPrints->pluck('specific_id')->filter()->unique();
        $mtgPrintsData = DB::table('mtg_prints')->whereIn('id', $specificIds)->get()->keyBy('id');

        $variantCounts = [];
        foreach ($allPrints as $print) {
            $printMtgData = $mtgPrintsData->get($print->specific_id);
            $art = $printMtgData->artist ?? 'Artista Desconhecido';
            $setCode = strtoupper($print->set->code ?? '');
            
            if (in_array($setCode, ['FEM', 'ALL', 'HML']) && preg_match('/[a-zA-Z]/', $print->collector_number)) {
                if (!isset($variantCounts[$art])) $variantCounts[$art] = [];
                $variantCounts[$art][] = $print->collector_number;
            }
        }
        
        foreach ($variantCounts as $art => $nums) {
            sort($variantCounts[$art]);
        }

        foreach ($allPrints as $print) {
            $printMtgData = $mtgPrintsData->get($print->specific_id);
            $rawArtist = $printMtgData->artist ?? 'Artista Desconhecido';
            $nomeArtista = $rawArtist;
            $englishName = $this->concept->name ?? '';
            $setCode = strtoupper($print->set->code ?? '');
            $isVariantSet = in_array($setCode, ['FEM', 'ALL', 'HML']);
            $hasLetterInNumber = preg_match('/[a-zA-Z]/', $print->collector_number);
            $isBasicLand = str_contains($print->type_line ?? '', 'Basic Land');

            if ($isVariantSet && $hasLetterInNumber && !$isBasicLand) {
                if (isset($variantCounts[$rawArtist]) && count($variantCounts[$rawArtist]) > 1) {
                    $idx = array_search($print->collector_number, $variantCounts[$rawArtist]);
                    if ($idx !== false) {
                        $nomeArtista .= ' ' . ($idx + 1);
                    }
                }
                $print->artist = $nomeArtista;
                $print->virtual_slug = Str::slug($englishName . '-' . $nomeArtista);
                $print->is_art_variant = true;
            } else {
                $print->artist = $rawArtist;
                $print->virtual_slug = $conceptSlug;
                $print->is_art_variant = false;
            }
        }

        return $allPrints->filter(fn($p) => $p->virtual_slug === $conceptSlug);
    }

    public function resolveProductData(string $conceptSlug): array
    {
        $isBasicLandSlug = preg_match('/^(plains|island|swamp|mountain|forest)-(\d+)$/', $conceptSlug, $matches);

        if ($isBasicLandSlug) {
            $basicTypeMap = [
                'plains' => 'Plains', 'island' => 'Island', 'swamp' => 'Swamp',
                'mountain' => 'Mountain', 'forest' => 'Forest',
            ];
            $englishBasicTypeName = $basicTypeMap[$matches[1]] ?? null;
            $collectorNumber = $matches[2];

            $prints = CatalogPrint::query()
                ->where('collector_number', $collectorNumber)
                ->where('type_line', 'LIKE', '%Basic Land%')
                ->where('type_line', 'LIKE', '%' . $englishBasicTypeName . '%')
                ->whereHas('set', fn($q) => $q->where('game_id', 1))
                ->with(['concept', 'set', 'concept.prints'])
                ->get();

            if ($prints->isEmpty()) abort(404, 'Terreno básico não encontrado.');

            $baseConcept = $prints->first()->concept;
            $displayEnglishName = sprintf('%s (#%s)', $englishBasicTypeName, $collectorNumber);
            $globalPtName = $baseConcept->prints
                ->firstWhere(fn($p) => in_array($p->language_code, ['pt', 'PT', 'pt-br', 'pt-BR']))
                ?->printed_name;

            $displayPtName = $globalPtName ? sprintf('%s (#%s)', $globalPtName, $collectorNumber) : $displayEnglishName;

            $this->concept = (object)[
                'id'        => $baseConcept->id,
                'name'      => $displayEnglishName,
                'slug'      => $conceptSlug,
                'specific'  => $baseConcept->specific,
                'type_line' => $prints->first()->type_line,
            ];

            $this->activePrint = $prints->firstWhere('language_code', 'en') ?? $prints->first();
            $this->localizedName = $displayPtName;
            $cardDetails = DB::table('mtg_prints')->where('id', $this->activePrint?->specific_id)->first();

            return [
                'concept'        => $this->concept,
                'prints'         => $prints,
                'activePrint'    => $this->activePrint,
                'nomeLocalizado' => $this->localizedName,
                'cardDetails'    => $cardDetails,
            ];
        }

        // Cartas normais
        $conceptFound = CatalogConcept::where('game_id', 1)
            ->where('slug', $conceptSlug)
            ->with(['specific', 'prints', 'prints.set'])
            ->first();

        if (!$conceptFound) {
            $conceptFound = CatalogConcept::where('game_id', 1)
                ->where('slug', 'like', $conceptSlug . '-____')
                ->with(['specific', 'prints', 'prints.set'])
                ->first();
        }

        if (!$conceptFound) {
            $parts = explode('-', $conceptSlug);
            array_pop($parts);
            while (count($parts) > 0) {
                $testSlug = implode('-', $parts);
                $conceptFound = CatalogConcept::where('game_id', 1)
                    ->where('slug', 'like', $testSlug . '-____')
                    ->with(['specific', 'prints', 'prints.set'])
                    ->first();
                if ($conceptFound) break;
                array_pop($parts);
            }
        }

        if (!$conceptFound) abort(404, 'Carta não encontrada no catálogo.');

        $this->concept = $conceptFound;
        $matchingPrints = $this->resolveMatchingPrints($this->concept->prints, $conceptSlug);

        if ($matchingPrints->isEmpty()) {
            $matchingPrints = $this->concept->prints;
        }

        $printPt = $matchingPrints->firstWhere(fn($p) => in_array($p->language_code, ['pt', 'pt-br', 'pt-BR']) && !empty($p->printed_name));
        $baseName = $printPt ? $printPt->printed_name : $this->concept->name;
        $primeiroPrint = $matchingPrints->first();

        $this->localizedName = ($primeiroPrint && !empty($primeiroPrint->is_art_variant))
            ? $baseName . ' (' . $primeiroPrint->artist . ')'
            : $baseName;

        $this->activePrint = $matchingPrints->firstWhere('language_code', 'en') ?? $matchingPrints->first();
        $cardDetails = DB::table('mtg_prints')->where('id', $this->activePrint?->specific_id)->first();

        return [
            'concept'        => $this->concept,
            'prints'         => $matchingPrints,
            'activePrint'    => $this->activePrint,
            'nomeLocalizado' => $this->localizedName,
            'cardDetails'    => $cardDetails,
        ];
    }

    public function formatPublicSlug(string $slug, ?string $conceptName = null): string
    {
        return preg_replace('/-[a-f0-9]{4}$/', '', $slug);
    }

    public function groupGhostPrints(int $conceptId, ?string $conceptName = null, ?string $numberFilter = null): array
    {
        $isBasicLand = preg_match('/^(Plains|Island|Swamp|Mountain|Forest)$/i', $conceptName ?? '');
        $groups = [];

        if (!$isBasicLand) {
            $prints = \App\Models\Catalog\CatalogPrint::select('catalog_prints.*', 'mtg_prints.artist', 'sets.code as set_code')
                ->leftJoin('mtg_prints', 'catalog_prints.specific_id', '=', 'mtg_prints.id')
                ->join('sets', 'catalog_prints.set_id', '=', 'sets.id')
                ->where('concept_id', $conceptId)->get();

            foreach ($prints as $p) {
                $isVar = in_array(strtoupper($p->set_code ?? ''), ['FEM', 'ALL', 'HML']) && preg_match('/[a-zA-Z]/', $p->collector_number ?? '');
                $vId = $isVar ? $p->collector_number : 'default';
                $groups[$p->set_id][$vId][] = $p;
            }
        } else {
            $allNumbersBySet = \App\Models\Catalog\CatalogPrint::where('concept_id', $conceptId)
                ->when($numberFilter, fn($q) => $q->where('collector_number', $numberFilter))
                ->select('collector_number', 'set_id')
                ->get()
                ->groupBy('set_id');

            foreach ($allNumbersBySet as $sid => $numbers) {
                foreach ($numbers->pluck('collector_number')->unique() as $num) {
                    $prints = \App\Models\Catalog\CatalogPrint::where('concept_id', $conceptId)
                        ->where('set_id', $sid)
                        ->where('collector_number', $num)
                        ->get();
                    $groups[$sid][(string)$num] = $prints->all();
                }
            }
        }

        return $groups;
    }

    public function getPrintVariantKey($print): string
    {
        $isBasicLand = stripos($print->type_line ?? '', 'Basic Land') !== false;
        $isVariantSet = in_array(strtoupper($print->set_code ?? ($print->set->code ?? '')), ['FEM', 'ALL', 'HML']);
        $hasLetterInNumber = preg_match('/[a-zA-Z]/', $print->collector_number ?? '');

        if ($isBasicLand || ($isVariantSet && $hasLetterInNumber)) {
            return (string) $print->collector_number;
        }
        return '';
    }

    public function buildProductSlug($concept, $print = null, ?string $vNum = null, ?string $extra = null): string
    {
        $conceptName = is_object($concept) ? ($concept->name ?? '') : ($concept['name'] ?? '');
        $conceptSlug = is_object($concept) ? ($concept->slug ?? '') : ($concept['slug'] ?? '');

        if (!empty($extra)) {
            return \Illuminate\Support\Str::slug($conceptName . '-' . $extra);
        }
        if (!empty($vNum)) {
            return \Illuminate\Support\Str::slug($conceptName) . '-' . $vNum;
        }
        return preg_replace('/-[a-f0-9]{4}$/', '', $conceptSlug ?: \Illuminate\Support\Str::slug($conceptName));
    }

    public function buildDisplayTitles(string $nomeEn, string $nomePt, $print = null, ?string $vNum = null, ?string $extra = null): array
    {
        if (!empty($extra)) {
            return [
                'en' => "$nomeEn ($extra)",
                'pt' => "$nomePt ($extra)",
            ];
        }
        if (!empty($vNum)) {
            return [
                'en' => "$nomeEn #$vNum",
                'pt' => "$nomePt #$vNum",
            ];
        }
        return [
            'en' => $nomeEn,
            'pt' => $nomePt,
        ];
    }

    public function getRulesHtml(): string
    {
        $printData = $this->activePrint?->specific;
        $conceptData = $this->concept?->specific;

        $raw = $printData?->printed_text ?? $conceptData?->oracle_text ?? $this->concept?->description ?? "";
        if (empty($raw)) {
            return "";
        }

        $html = preg_replace_callback("/\{([^}]+)\}/", function($m) {
            $val = strtolower(str_replace("/", "", $m[1]));
            return "<i class=\"ms ms- ms-cost text-xs\" style=\"filter: drop-shadow(-1px 1px 0px rgba(0,0,0,0.6));\"></i>";
        }, $raw);

        return "<p class=\"mb-2 text-gray-800 font-medium\">" . nl2br($html) . "</p>";
    }

    public function getFlavorText(): ?string
    {
        return $this->activePrint?->specific?->flavor_text ?? $this->concept?->specific?->flavor_text ?? null;
    }

    public function getTechnicalAttributes(): array
    {
        $attrs = [];
        $conceptData = $this->concept?->specific;
        $printData = $this->activePrint?->specific;

        if (!empty($conceptData?->mana_cost)) {
            $manaHtml = preg_replace_callback("/\{([^}]+)\}/", function($m) {
                $val = strtolower(str_replace("/", "", $m[1]));
                return "<i class=\"ms ms- ms-cost text-base\" style=\"filter: drop-shadow(-1px 1px 0px rgba(0,0,0,0.6));\"></i>";
            }, $conceptData->mana_cost);

            $attrs[] = ["label" => "Custo de Mana", "value" => $manaHtml, "cols" => 1];
        }

        $typeLine = $printData?->printed_type_line ?? $conceptData?->type_line ?? null;
        if (!empty($typeLine)) {
            $attrs[] = ["label" => "Tipo", "value" => e($typeLine), "cols" => 1];
        }

        $artist = $printData?->artist ?? null;
        if (!empty($artist)) {
            $attrs[] = ["label" => "Artista", "value" => "<span class=\"italic\">" . e($artist) . "</span>", "cols" => 1];
        }

        if (isset($conceptData?->power) && isset($conceptData?->toughness)) {
            $attrs[] = ["label" => "Poder / Resistência", "value" => e("{$conceptData->power} / {$conceptData->toughness}"), "cols" => 1];
        }

        if (isset($conceptData?->loyalty)) {
            $attrs[] = ["label" => "Lealdade", "value" => e($conceptData->loyalty), "cols" => 1];
        }

        return $attrs;
    }
}
