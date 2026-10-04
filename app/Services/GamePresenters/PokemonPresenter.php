<?php

namespace App\Services\GamePresenters;

use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PokemonPresenter implements GamePresenterInterface
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
        $target = $this->activePrint;
        $englishName = $this->concept->name ?? '';

        if ($target && !empty($target->collector_number)) {
            $num = $target->collector_number;
            $set = $target->set;
            $total = $set->card_count ?? $set->card_count_official ?? $set->total_cards ?? null;
            $numberStr = $total ? "#{$num}/{$total}" : "#{$num}";
            return "{$englishName} ({$numberStr})";
        }

        return $englishName;
    }

    public function hasSecondaryTitle(): bool
    {
        return true;
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
        return 'pokeball';
    }

    public function resolveMatchingPrints(Collection $allPrints, string $conceptSlug): Collection
    {
        $targetPrint = null;
        $targetSlug = request('card') ?: $conceptSlug;

        // 1. Padrão canônico de Pokémon: {nome}-{numero}-{total} (ex: kabuto-38-129)
        if (preg_match('/-(\d+[a-zA-Z]?)-(\d+)$/', $targetSlug, $m)) {
            $num = $m[1];
            $total = (int) $m[2];

            $targetPrint = $allPrints->first(function ($p) use ($num, $total) {
                $pNum = (string) ($p->collector_number ?? '');
                $setTotal = (int) ($p->set->card_count ?? $p->set->card_count_official ?? $p->set->total_cards ?? 0);
                return $pNum === (string)$num && ($setTotal === $total || $setTotal === 0);
            });

            if (!$targetPrint) {
                $targetPrint = $allPrints->firstWhere('collector_number', $num);
            }
        }

        // 2. URL ou parâmetro com indicador explícito de set/número (ex: aerodactyl-base3-1)
        if (!$targetPrint && preg_match('/-([a-zA-Z0-9]+)-(\d+[a-zA-Z]?)$/', $targetSlug, $m)) {
            $setCodeOrApi = strtolower($m[1]);
            $num = $m[2];

            $targetPrint = $allPrints->first(function ($p) use ($setCodeOrApi, $num) {
                $code = strtolower($p->set->code ?? '');
                $apiId = strtolower($p->set->api_id ?? '');
                return ($code === $setCodeOrApi || $apiId === $setCodeOrApi) && (string)$p->collector_number === (string)$num;
            });
        }

        // 3. Se a URL traz apenas o número no final (ex: kabuto-38)
        if (!$targetPrint && preg_match('/-(\d+[a-zA-Z]?)$/', $targetSlug, $m)) {
            $num = $m[1];
            $targetPrint = $allPrints->firstWhere('collector_number', $num);
        }

        // 4. Se a URL é limpa (ex: aerodactyl), tenta pegar pelo slug gravado no conceito
        if (!$targetPrint) {
            $conceptSlugBanco = $this->concept->slug ?? '';
            if (preg_match('/-([a-zA-Z0-9]+)-(\d+[a-zA-Z]?)$/', $conceptSlugBanco, $m)) {
                $setCodeOrApi = strtolower($m[1]);
                $num = $m[2];
                $targetPrint = $allPrints->first(function ($p) use ($setCodeOrApi, $num) {
                    $code = strtolower($p->set->code ?? '');
                    $apiId = strtolower($p->set->api_id ?? '');
                    return ($code === $setCodeOrApi || $apiId === $setCodeOrApi) && (string)$p->collector_number === (string)$num;
                });
            }
        }

        if (!$targetPrint) {
            $targetPrint = $allPrints->first();
        }

        if ($targetPrint) {
            $targetSetId = $targetPrint->set_id;
            $targetNumber = $targetPrint->collector_number;

            $matchingPrints = $allPrints->filter(function ($p) use ($targetSetId, $targetNumber) {
                return $p->set_id == $targetSetId && (string)$p->collector_number === (string)$targetNumber;
            });

            return $matchingPrints->isNotEmpty() ? $matchingPrints : collect([$targetPrint]);
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

        $concept = \App\Models\Catalog\CatalogConcept::where('game_id', 2)
            ->where(function ($q) use ($cleanSlug, $conceptSlug) {
                $q->where('slug', $cleanSlug)
                  ->orWhere('slug', $conceptSlug)
                  ->orWhere('name', 'like', str_replace('-', ' ', $cleanSlug));
            })
            ->with(['specific', 'prints', 'prints.set'])
            ->first();

        if (!$concept) {
            abort(404, 'Carta de Pokémon não encontrada no catálogo.');
        }

        $this->concept = $concept;
        $matchingPrints = $this->resolveMatchingPrints($concept->prints, $conceptSlug);

        $printPt = $matchingPrints->firstWhere(fn($p) => in_array(strtolower($p->language_code), ['pt', 'pt-br']) && !empty($p->printed_name));
        $this->localizedName = $printPt ? $printPt->printed_name : $concept->name;

        $this->activePrint = $matchingPrints->firstWhere(fn($p) => strtolower($p->language_code) === 'en') ?? $matchingPrints->first();
        $cardDetails = \Illuminate\Support\Facades\DB::table('pk_prints')->where('id', $this->activePrint?->specific_id)->first();

        return [
            'concept'        => $this->concept,
            'prints'         => $matchingPrints,
            'activePrint'    => $this->activePrint,
            'nomeLocalizado' => $this->localizedName,
            'cardDetails'    => $cardDetails,
        ];
    }

    public function groupGhostPrints(int $conceptId, ?string $conceptName = null, ?string $numberFilter = null): array
    {
        $prints = \App\Models\Catalog\CatalogPrint::with('set')
            ->where('concept_id', $conceptId)
            ->when($numberFilter, fn($q) => $q->where('collector_number', $numberFilter))
            ->get();

        $groups = [];
        foreach ($prints as $p) {
            $num = (string)($p->collector_number ?? 'default');
            $groups[$p->set_id][$num][] = $p;
        }

        return $groups;
    }

    public function getPrintVariantKey($print): string
    {
        return (string) ($print->collector_number ?? 'default');
    }

    public function buildProductSlug($concept, $print = null, ?string $vNum = null, ?string $extra = null): string
    {
        $conceptName = is_object($concept) ? ($concept->name ?? '') : ($concept['name'] ?? '');
        $baseSlug = \Illuminate\Support\Str::slug($conceptName);

        $num = $vNum ?: ($print?->collector_number ?? null);
        $total = null;

        if ($print && $print->set) {
            $total = $print->set->card_count ?? $print->set->card_count_official ?? $print->set->total_cards ?? null;
        }

        if ($num && $total) {
            return "{$baseSlug}-{$num}-{$total}";
        }
        if ($num) {
            return "{$baseSlug}-{$num}";
        }
        return $baseSlug;
    }

    public function buildDisplayTitles(string $nomeEn, string $nomePt, $print = null, ?string $vNum = null, ?string $extra = null): array
    {
        $num = $vNum ?: ($print?->collector_number ?? null);
        $total = null;

        if ($print && $print->set) {
            $total = $print->set->card_count ?? $print->set->card_count_official ?? $print->set->total_cards ?? null;
        }

        if ($num && $total) {
            $tag = "(#{$num}/{$total})";
            return [
                'en' => "{$nomeEn} {$tag}",
                'pt' => "{$nomePt} {$tag}",
            ];
        }
        if ($num) {
            return [
                'en' => "{$nomeEn} (#{$num})",
                'pt' => "{$nomePt} (#{$num})",
            ];
        }
        return [
            'en' => $nomeEn,
            'pt' => $nomePt,
        ];
    }

    public function formatPublicSlug(string $slug, ?string $conceptName = null): string
    {
        return preg_replace('/-[a-f0-9]{4}$/', '', $slug);
    }

    public function getRulesHtml(): string
    {
        $conceptData = $this->concept?->specific;
        $html = "";

        if (!empty($conceptData?->abilities)) {
            foreach ((array)$conceptData->abilities as $ab) {
                $tipo = e($ab["type"] ?? "Habilidade");
                $nome = e($ab["name"] ?? "");
                $efeito = e($ab["effect"] ?? "");

                $html .= "<div class=\"mb-2.5 pb-2 border-b border-gray-100 last:border-none last:pb-0\">
                    <span class=\"inline-block px-1.5 py-0.5 text-[9px] font-extrabold uppercase bg-red-100 text-red-700 rounded mr-1\">{$tipo}</span>
                    <strong class=\"text-gray-900 text-xs\">{$nome}</strong>
                    <p class=\"text-gray-700 text-xs mt-0.5\">{$efeito}</p>
                </div>";
            }
        }

        if (!empty($conceptData?->attacks)) {
            foreach ((array)$conceptData->attacks as $atk) {
                $cost = implode(", ", (array)($atk["cost"] ?? []));
                $nome = e($atk["name"] ?? "");
                $dano = !empty($atk["damage"]) ? "<span class=\"text-sm font-black text-gray-900\">" . e($atk["damage"]) . "</span>" : "";
                $efeito = !empty($atk["effect"]) ? "<p class=\"text-gray-600 text-xs mt-0.5\">" . e($atk["effect"]) . "</p>" : "";

                $html .= "<div class=\"mb-2.5 pb-2 border-b border-gray-100 last:border-none last:pb-0\">
                    <div class=\"flex justify-between items-center\">
                        <div class=\"flex items-center gap-1.5\">
                            <span class=\"text-[10px] font-bold text-gray-500\">[{$cost}]</span>
                            <strong class=\"text-gray-900 text-xs\">{$nome}</strong>
                        </div>
                        {$dano}
                    </div>
                    {$efeito}
                </div>";
            }
        }

        if (empty($html) && !empty($conceptData?->rules_text)) {
            $html = "<p class=\"mb-2 text-gray-800 font-medium whitespace-pre-line\">" . e($conceptData->rules_text) . "</p>";
        }

        return $html;
    }

    public function getFlavorText(): ?string
    {
        return $this->activePrint?->specific?->flavor_text ?? null;
    }

    public function getTechnicalAttributes(): array
    {
        $attrs = [];
        $conceptData = $this->concept?->specific;
        $printData = $this->activePrint?->specific;

        if (!empty($conceptData?->hp)) {
            $attrs[] = ["label" => "Pontos de Saúde (HP)", "value" => e("{$conceptData->hp} HP"), "cols" => 1];
        }

        if (!empty($conceptData?->types)) {
            $attrs[] = ["label" => "Tipo", "value" => e(implode(", ", (array)$conceptData->types)), "cols" => 1];
        }

        if (!empty($conceptData?->supertype)) {
            $attrs[] = ["label" => "Supertipo", "value" => e($conceptData->supertype), "cols" => 1];
        }

        if (!empty($conceptData?->subtypes)) {
            $attrs[] = ["label" => "Estágio / Subtipo", "value" => e(implode(", ", (array)$conceptData->subtypes)), "cols" => 1];
        }

        if (!empty($conceptData?->weaknesses)) {
            $weak = "";
            foreach ((array)$conceptData->weaknesses as $w) {
                $weak .= ($w["type"] ?? "") . " " . ($w["value"] ?? "") . " ";
            }
            $attrs[] = ["label" => "Fraqueza", "value" => "<span class=\"text-red-600 font-bold\">" . e(trim($weak)) . "</span>", "cols" => 1];
        }

        if (!empty($conceptData?->retreat_cost)) {
            $attrs[] = ["label" => "Recuo", "value" => (string) count((array)$conceptData->retreat_cost), "cols" => 1];
        }

        $artist = $printData?->artist ?? null;
        if (!empty($artist)) {
            $attrs[] = ["label" => "Ilustrador", "value" => "<span class=\"italic\">" . e($artist) . "</span>", "cols" => 2];
        }

        return $attrs;
    }
}
