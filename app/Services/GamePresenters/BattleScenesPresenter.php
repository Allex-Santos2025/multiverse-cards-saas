<?php

namespace App\Services\GamePresenters;

use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BattleScenesPresenter implements GamePresenterInterface
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
        return null;
    }

    public function hasSecondaryTitle(): bool
    {
        return false;
    }

    public function getCollectorNumberLabel(?CatalogPrint $print = null): ?string
    {
        $target = $print ?: $this->activePrint;
        if (!$target || empty($target->collector_number)) {
            return null;
        }

        $num = $target->collector_number;
        $set = $target->set;
        $total = $set->card_count ?? $set->card_count_official ?? $set->total_cards ?? null;

        return $total ? "#{$num}/{$total}" : "#{$num}";
    }

    public function getDefaultIconType(): string
    {
        return 'shield';
    }

    public function resolveMatchingPrints(Collection $allPrints, string $conceptSlug): Collection
    {
        $targetPrint = null;
        $targetSlug = request('card') ?: $conceptSlug;

        if (preg_match('/-(\d+[a-zA-Z]?)-(\d+)$/', $targetSlug, $m)) {
            $num = $m[1];
            $total = (int) $m[2];

            $targetPrint = $allPrints->first(function ($p) use ($num, $total) {
                $pNum = (string) ($p->collector_number ?? '');
                $setTotal = (int) ($p->set->card_count ?? $p->set->card_count_official ?? $p->set->total_cards ?? 0);
                return $pNum === (string) $num && ($setTotal === $total || $setTotal === 0);
            });

            if (!$targetPrint) {
                $targetPrint = $allPrints->firstWhere('collector_number', $num);
            }
        }

        if (!$targetPrint && preg_match('/-(\d+[a-zA-Z]?)$/', $targetSlug, $m)) {
            $num = $m[1];
            $targetPrint = $allPrints->firstWhere('collector_number', $num);
        }

        if (!$targetPrint) {
            $targetPrint = $allPrints->first();
        }

        if ($targetPrint) {
            return $allPrints->sortBy(fn($p) => $p->id === $targetPrint->id ? 0 : 1)->values();
        }

        return $allPrints;
    }

    public function resolveProductData(string $conceptSlug): array
    {
        $cleanSlug = $conceptSlug;
        if (preg_match('/^(.*?)-(\d+[a-zA-Z]?)-(\d+)$/', $conceptSlug, $m)) {
            $cleanSlug = $m[1];
        } elseif (preg_match('/^(.*?)-(\d+[a-zA-Z]?)$/', $conceptSlug, $m)) {
            $cleanSlug = $m[1];
        }

        $concept = CatalogConcept::where('game_id', 4)
            ->where(function ($q) use ($cleanSlug, $conceptSlug) {
                $q->where('slug', $cleanSlug)
                  ->orWhere('slug', $conceptSlug)
                  ->orWhere('name', 'like', str_replace('-', ' ', $cleanSlug));
            })
            ->with(['specific', 'prints', 'prints.set'])
            ->first();

        if (!$concept) {
            abort(404, 'Carta de Battle Scenes não encontrada no catálogo.');
        }

        $this->concept = $concept;
        $matchingPrints = $this->resolveMatchingPrints($concept->prints, $conceptSlug);

        $this->activePrint = $matchingPrints->first();
        $this->localizedName = $concept->name;

        $cardDetails = DB::table('bs_prints')->where('id', $this->activePrint?->specific_id)->first();

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
        $conceptName = is_object($concept) ? ($concept->name ?? "") : ($concept["name"] ?? "");
        $conceptSlug = is_object($concept) ? ($concept->slug ?? "") : ($concept["slug"] ?? "");

        if (!empty($extra)) {
            return \Illuminate\Support\Str::slug($conceptName . "-" . $extra);
        }
        if (!empty($vNum)) {
            return \Illuminate\Support\Str::slug($conceptName) . "-" . $vNum;
        }
        return preg_replace("/-[a-f0-9]{4}$/", "", $conceptSlug ?: \Illuminate\Support\Str::slug($conceptName));
    }

    public function buildDisplayTitles(string $nomeEn, string $nomePt, $print = null, ?string $vNum = null, ?string $extra = null): array
    {
        $titulo = $nomePt ?: $nomeEn;

        if (!empty($extra)) {
            $titulo = "$titulo ($extra)";
        } elseif (!empty($vNum)) {
            $titulo = "$titulo #$vNum";
        }

        return [
            "pt" => $titulo,
            "en" => null,
        ];
    }

    public function groupGhostPrints(int $conceptId, ?string $conceptName = null, ?string $numberFilter = null): array
    {
        $prints = \App\Models\Catalog\CatalogPrint::select("catalog_prints.*", "bs_prints.artist", "sets.code as set_code")
            ->leftJoin("bs_prints", "catalog_prints.specific_id", "=", "bs_prints.id")
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
        $attrs = [];
        $conceptData = $this->concept?->specific;
        $printData = $this->activePrint?->specific;

        if (!empty($conceptData?->alter_ego)) {
            $attrs[] = ["label" => "Alter Ego", "value" => e($conceptData->alter_ego), "cols" => 2];
        }

        if (!empty($conceptData?->type_line)) {
            $attrs[] = ["label" => "Tipo", "value" => e($conceptData->type_line), "cols" => 1];
        }

        if (!empty($conceptData?->affiliation)) {
            $attrs[] = ["label" => "Afiliação", "value" => e($conceptData->affiliation), "cols" => 1];
        }

        if (isset($conceptData?->power) || isset($conceptData?->toughness)) {
            $power = $conceptData?->power ?? "-";
            $toughness = $conceptData?->toughness ?? "-";
            $attrs[] = ["label" => "Energia / Escudo", "value" => e("{$power} / {$toughness}"), "cols" => 1];
        }

        $artist = $printData?->artist ?? null;
        if (!empty($artist)) {
            $attrs[] = ["label" => "Ilustrador", "value" => "<span class=\"italic\">" . e($artist) . "</span>", "cols" => 1];
        }

        return $attrs;
    }

    public function getAvailableStockExtras(): array
    {
        return \App\Enums\BattleScenesStockExtra::options();
    }

    public function getPrintVariantKey($print): string
    {
        return $print->collector_number ?? "default";
    }

    // === DECK BUILDER, SANDBOX & PREÇOS ===
    public function getDeckFormats(): array
    {
        return [
            'padrao'   => 'Formato Padrão (60 Cartas)',
            'tribal'   => 'Tribal / Afiliação Temática',
            'iniciante'=> 'Modo Batalha Rápida (30 Cartas)',
        ];
    }

    public function getDeckZones(?string $format = null): array
    {
        if (strtolower((string)$format) === 'iniciante') {
            return [
                'mainboard' => ['label' => 'Deck Principal (30 Cartas)', 'min' => 30, 'max' => 30],
            ];
        }

        return [
            'mainboard' => ['label' => 'Deck Principal (60 Cartas)', 'min' => 60, 'max' => 60],
        ];
    }

    public function getDeckValidationRules(?string $format = null): array
    {
        $isIniciante = strtolower((string)$format) === 'iniciante';
        return [
            'total_cards_exact'  => $isIniciante ? 30 : 60,
            'min_mainboard'      => $isIniciante ? 30 : 60,
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

        // Battle Scenes é cotado diretamente em Reais (BRL)
        if (!empty($specific->base_price)) {
            return (float) $specific->base_price;
        }

        return 0.00;
    }
}
