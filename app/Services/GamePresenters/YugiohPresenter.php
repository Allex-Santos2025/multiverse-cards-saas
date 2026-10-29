<?php

namespace App\Services\GamePresenters;

use App\Enums\YugiohStockExtra;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class YugiohPresenter implements GamePresenterInterface
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
        return 'shield';
    }

    public function resolveMatchingPrints(Collection $allPrints, string $conceptSlug): Collection
    {
        return $allPrints;
    }

    public function resolveProductData(string $conceptSlug): array
    {
        $conceptFound = CatalogConcept::where('game_id', 3)
            ->where('slug', $conceptSlug)
            ->with(['specific', 'prints', 'prints.set'])
            ->first();

        if (!$conceptFound) {
            $parts = explode('-', $conceptSlug);
            array_pop($parts);
            while (count($parts) > 0) {
                $testSlug = implode('-', $parts);
                $conceptFound = CatalogConcept::where('game_id', 3)
                    ->where('slug', 'like', $testSlug . '%')
                    ->with(['specific', 'prints', 'prints.set'])
                    ->first();
                if ($conceptFound) break;
                array_pop($parts);
            }
        }

        if (!$conceptFound) {
            $conceptFound = CatalogConcept::where('game_id', 3)
                ->where('name', 'like', str_replace('-', ' ', $conceptSlug))
                ->with(['specific', 'prints', 'prints.set'])
                ->first();
        }

        if (!$conceptFound) {
            abort(404, 'Carta de Yu-Gi-Oh! não encontrada no catálogo.');
        }

        $this->concept = $conceptFound;
        $matchingPrints = $this->resolveMatchingPrints($this->concept->prints, $conceptSlug);

        if ($matchingPrints->isEmpty()) {
            $matchingPrints = $this->concept->prints;
        }

        $printPt = $matchingPrints->firstWhere(fn($p) => in_array(strtolower($p->language_code ?? ''), ['pt', 'pt-br']) && !empty($p->printed_name));
        $this->localizedName = $printPt ? $printPt->printed_name : $this->concept->name;

        $this->activePrint = $matchingPrints->firstWhere(fn($p) => strtolower($p->language_code ?? '') === 'en') ?? $matchingPrints->first();

        $ygConceptData = [];
        if ($this->concept->specific_id) {
            foreach (['yg_concepts', 'yugioh_concepts'] as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $ygConceptData = (array) (DB::table($table)->where('id', $this->concept->specific_id)->first() ?? []);
                    if (!empty($ygConceptData)) break;
                }
            }
        }

        $ygPrintData = [];
        if ($this->activePrint?->specific_id) {
            foreach (['yg_prints', 'yugioh_prints', 'yg_cards'] as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $ygPrintData = (array) (DB::table($table)->where('id', $this->activePrint->specific_id)->first() ?? []);
                    if (!empty($ygPrintData)) break;
                }
            }
        }

        $cardDetails = (object) array_merge($ygPrintData, $ygConceptData);

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

    public function buildProductSlug($concept, $print = null, ?string $vNum = null, ?string $extra = null): string
    {
        $conceptSlug = is_object($concept) ? ($concept->slug ?? '') : ($concept['slug'] ?? '');
        return preg_replace('/-[a-f0-9]{4}$/', '', $conceptSlug ?: Str::slug(is_object($concept) ? $concept->name : ($concept['name'] ?? '')));
    }

    public function buildDisplayTitles(string $nomeEn, string $nomePt, $print = null, ?string $vNum = null, ?string $extra = null): array
    {
        return [
            'en' => $nomeEn,
            'pt' => $nomePt,
        ];
    }

    public function groupGhostPrints(int $conceptId, ?string $conceptName = null, ?string $numberFilter = null): array
    {
        $prints = CatalogPrint::select("catalog_prints.*", "sets.code as set_code")
            ->join("sets", "catalog_prints.set_id", "=", "sets.id")
            ->where("concept_id", $conceptId)
            ->when($numberFilter, fn($q) => $q->where("catalog_prints.collector_number", $numberFilter))
            ->get();

        $groups = [];
        foreach ($prints as $p) {
            $vId = $p->collector_number ?? "default";
            $groups[$p->set_id][$vId][] = $p;
        }

        return $groups;
    }

    public function getPrintVariantKey($print): string
    {
        return '';
    }

    public function getAvailableStockExtras(): array
    {
        return YugiohStockExtra::options();
    }

    public function getIntrinsicFinishKey(?CatalogPrint $print = null): ?string
    {
        $target = $print ?: $this->activePrint;
        $rawRarity = strtolower($target?->rarity ?? '');

        return match (true) {
            str_contains($rawRarity, 'quarter')   => 'quarter_century_secret',
            str_contains($rawRarity, 'starlight') => 'starlight_rare',
            str_contains($rawRarity, 'prismatic') => 'prismatic_secret_rare',
            str_contains($rawRarity, 'ghost')     => 'ghost_rare',
            str_contains($rawRarity, 'ultimate')  => 'ultimate_rare',
            str_contains($rawRarity, 'collector') => 'collectors_rare',
            str_contains($rawRarity, 'gold')      => 'gold_rare',
            str_contains($rawRarity, 'secret')    => 'secret_rare',
            str_contains($rawRarity, 'ultra')     => 'ultra_rare',
            str_contains($rawRarity, 'super')     => 'super_rare',
            str_contains($rawRarity, 'rare')      => 'rare',
            default                               => 'common',
        };
    }

    public function getLockedStockExtras(?CatalogPrint $print = null): array
    {
        $intrinsic = $this->getIntrinsicFinishKey($print);
        return $intrinsic ? [$intrinsic] : [];
    }

    public function applyNumberSort($query, string $direction = 'asc'): void
    {
        $dir = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
        $query->orderByRaw("CAST(REGEXP_REPLACE(catalog_prints.collector_number, '[^0-9]', '') AS UNSIGNED) {$dir}")
              ->orderBy('catalog_prints.collector_number', $dir);
    }

    public function resolveCardImage($printOrPath, $extras = null): string
    {
        $basePath = is_object($printOrPath) ? ($printOrPath->image_path ?? '') : (string) $printOrPath;

        if (empty($basePath)) {
            return 'https://placehold.co/250x350/eeeeee/999999?text=Sem+Imagem';
        }

        if (filter_var($basePath, FILTER_VALIDATE_URL)) {
            return $basePath;
        }

        $extrasString = is_array($extras) ? json_encode($extras) : strtolower((string) $extras);

        if (str_contains($extrasString, 'unlimited')) {
            $uePath = preg_replace('/(_1e)?\.jpg$/i', '_ue.jpg', $basePath);
            if (File::exists(public_path($uePath))) {
                return $uePath;
            }
        }

        if (str_contains($extrasString, '1st_edition')) {
            $firstEdPath = preg_replace('/(_ue)?\.jpg$/i', '_1e.jpg', $basePath);
            if (File::exists(public_path($firstEdPath))) {
                return $firstEdPath;
            }
        }

        return $basePath;
    }

    public function getRulesHtml(): string
    {
        $conceptData = $this->concept?->specific;
        $text = $conceptData?->rules_text ?? $this->concept?->description ?? "";
        if (empty($text)) {
            return "";
        }
        return "<p class=\"mb-2 text-gray-800 font-medium whitespace-pre-line\">" . e($text) . "</p>";
    }

    public function getFlavorText(): ?string
    {
        return $this->activePrint?->specific?->flavor_text ?? $this->concept?->specific?->flavor_text ?? null;
    }

    public function getTechnicalAttributes(): array
    {
        return [];
    }

    // === DECK BUILDER, SANDBOX & PREÇOS ===
    public function getDeckFormats(): array
    {
        return [
            'advanced' => 'Formato Avançado (TCG)',
            'traditional' => 'Formato Tradicional',
            'speed_duel' => 'Speed Duel',
        ];
    }

    public function getDeckZones(?string $format = null): array
    {
        if (strtolower((string)$format) === 'speed_duel') {
            return [
                'mainboard' => ['label' => 'Deck Principal (20-30)', 'min' => 20, 'max' => 30],
                'extra'     => ['label' => 'Deck Adicional (0-6)', 'min' => 0, 'max' => 6],
                'sideboard' => ['label' => 'Deck Auxiliar (Side)', 'min' => 0, 'max' => 6],
            ];
        }

        return [
            'mainboard' => ['label' => 'Deck Principal (40-60)', 'min' => 40, 'max' => 60],
            'extra'     => ['label' => 'Deck Adicional (Extra Deck)', 'min' => 0, 'max' => 15],
            'sideboard' => ['label' => 'Deck Auxiliar (Side Deck)', 'min' => 0, 'max' => 15],
        ];
    }

    public function getDeckValidationRules(?string $format = null): array
    {
        $isSpeed = strtolower((string)$format) === 'speed_duel';
        return [
            'min_mainboard'      => $isSpeed ? 20 : 40,
            'max_mainboard'      => $isSpeed ? 30 : 60,
            'max_copies_default' => 3,
            'exempt_types'       => [],
        ];
    }

    public function getPrintMarketPrice(CatalogPrint $print, array $extras = []): float
    {
        $specific = $print->specific;
        if (!$specific && class_exists($print->specific_type)) {
            $specific = ($print->specific_type)::find($print->specific_id);
        }

        if (!$specific) return 0.00;

        $usdRate = 5.60;
        if (!empty($specific->cardmarket_price)) {
            return round((float)$specific->cardmarket_price * 1.08 * $usdRate, 2);
        }
        if (!empty($specific->tcgplayer_price)) {
            return round((float)$specific->tcgplayer_price * $usdRate, 2);
        }

        return 0.00;
    }
}
