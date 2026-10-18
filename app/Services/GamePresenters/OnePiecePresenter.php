<?php

namespace App\Services\GamePresenters;

use App\Enums\OnePieceStockExtra;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnePiecePresenter implements GamePresenterInterface
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
            return "{$englishName} (#{$target->collector_number})";
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

        return "#{$target->collector_number}";
    }

    public function getDefaultIconType(): string
    {
        return 'anchor';
    }

    public function resolveMatchingPrints(Collection $allPrints, string $conceptSlug): Collection
    {
        $targetPrint = null;
        $targetSlug = request('card') ?: $conceptSlug;

        // 1. Tenta casar com o collector_number exato passado via requisição ou slug
        if (preg_match('/-([a-zA-Z0-9]+(?:-[0-9]+)?(?:_[a-zA-Z0-9]+)?)$/i', $targetSlug, $m)) {
            $num = $m[1];
            $targetPrint = $allPrints->first(function ($p) use ($num) {
                return strcasecmp((string)$p->collector_number, $num) === 0;
            });
        }

        // 2. Se não casou específico, tenta pegar o primeiro em inglês ou padrão
        if (!$targetPrint) {
            $targetPrint = $allPrints->firstWhere(fn($p) => strtolower($p->language_code ?? '') === 'en') 
                        ?? $allPrints->first();
        }

        // 3. REGRA CRUCIAL: Retorna TODOS os prints do conceito (incluindo idiomas FR, ZH, KO, JA e artes alternativas),
        // colocando o print ativo no topo da ordenação, sem descartar nenhum da tabela de Edições Disponíveis!
        if ($targetPrint) {
            return $allPrints->sortBy(fn($p) => $p->id === $targetPrint->id ? 0 : 1)->values();
        }

        return $allPrints;
    }

    public function resolveProductData(string $conceptSlug): array
    {
        $cleanSlug = $conceptSlug;
        $collectorNumber = null;

        if (preg_match('/^(.*?)-([a-zA-Z0-9]+-[0-9]+(?:_[a-zA-Z0-9]+)?)$/i', $conceptSlug, $m)) {
            $cleanSlug = $m[1];
            $collectorNumber = $m[2];
        }

        // 1. Tenta encontrar pelo slug direto do catálogo
        $concept = CatalogConcept::where('game_id', 5)
            ->where('slug', $conceptSlug)
            ->with(['specific', 'prints', 'prints.set'])
            ->first();

        // 2. Se veio com número de colecionador, acha o print para recuperar o conceito pai
        if (!$concept && $collectorNumber) {
            $matchingPrint = CatalogPrint::whereHas('concept', fn($q) => $q->where('game_id', 5))
                ->where(function ($q) use ($collectorNumber) {
                    $baseCode = explode('_', $collectorNumber)[0];
                    $q->where('collector_number', $collectorNumber)
                      ->orWhere('collector_number', 'like', $baseCode . '%');
                })
                ->orderByRaw("CASE WHEN language_code = 'en' THEN 0 WHEN language_code = 'ja' THEN 1 ELSE 2 END")
                ->first();

            if ($matchingPrint) {
                $concept = CatalogConcept::where('id', $matchingPrint->concept_id)
                    ->with(['specific', 'prints', 'prints.set'])
                    ->first();
            }
        }

        // 3. Fallback procurando por slug limpo ou nome
        if (!$concept) {
            $concept = CatalogConcept::where('game_id', 5)
                ->where(function ($q) use ($cleanSlug, $conceptSlug) {
                    $q->where('slug', $cleanSlug)
                      ->orWhere('slug', $conceptSlug)
                      ->orWhere('name', 'like', str_replace('-', ' ', $cleanSlug));
                })
                ->with(['specific', 'prints', 'prints.set'])
                ->first();
        }

        if (!$concept) {
            abort(404, 'Carta de One Piece não encontrada no catálogo.');
        }

        $this->concept = $concept;
        $matchingPrints = $this->resolveMatchingPrints($concept->prints, $conceptSlug);

        // Define a estampa ativa selecionada
        $requestedCard = request('card');
        if ($requestedCard) {
            $this->activePrint = $matchingPrints->first(fn($p) => strcasecmp((string)$p->collector_number, $requestedCard) === 0)
                              ?? $matchingPrints->first();
        } else {
            $this->activePrint = $matchingPrints->firstWhere(fn($p) => strtolower($p->language_code) === 'en')
                              ?? $matchingPrints->first();
        }

        $printEn = $matchingPrints->firstWhere(fn($p) => strtolower($p->language_code) === 'en' && !empty($p->printed_name));
        $this->localizedName = $printEn ? $printEn->printed_name : $concept->name;

        $opConceptData = (array) (DB::table('op_concepts')->where('id', $concept->specific_id)->first() ?? []);
        $opPrintData = (array) (DB::table('op_prints')->where('id', $this->activePrint?->specific_id)->first() ?? []);
        $cardDetails = (object) array_merge($opPrintData, $opConceptData);

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
        $conceptName = is_object($concept) ? ($concept->name ?? '') : ($concept['name'] ?? '');
        $baseSlug = Str::slug($conceptName);

        $num = $vNum ?: ($print?->collector_number ?? null);

        if ($num) {
            return "{$baseSlug}-" . Str::slug($num);
        }
        return $baseSlug;
    }

    public function buildDisplayTitles(string $nomeEn, string $nomePt, $print = null, ?string $vNum = null, ?string $extra = null): array
    {
        $num = $vNum ?: ($print?->collector_number ?? null);

        if (!empty($extra)) {
            $tag = "({$extra})";
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

    public function groupGhostPrints(int $conceptId, ?string $conceptName = null, ?string $numberFilter = null): array
    {
        $prints = CatalogPrint::with('set')
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

    public function getRulesHtml(): string
    {
        $printData = $this->activePrint?->specific;
        $conceptData = $this->concept?->specific;

        $text = $printData?->printed_text ?? $conceptData?->oracle_text ?? $this->concept?->description ?? '';
        $trigger = $printData?->printed_trigger_text ?? $conceptData?->trigger_text ?? null;

        $html = '';
        if (!empty($text)) {
            $html .= "<p class=\"mb-2 text-gray-800 font-medium whitespace-pre-line\">" . e($text) . "</p>";
        }

        if (!empty($trigger)) {
            $html .= "<div class=\"mt-2 pt-2 border-t border-gray-100\">
                <span class=\"inline-block px-1.5 py-0.5 text-[10px] font-extrabold uppercase bg-yellow-100 text-yellow-800 rounded mr-1.5\">Trigger</span>
                <span class=\"text-xs text-gray-700 font-medium\">" . e($trigger) . "</span>
            </div>";
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

        $cardType = $conceptData?->card_type ?? null;
        if (!empty($cardType)) {
            $attrs[] = ["label" => "Tipo de Carta", "value" => e($cardType), "cols" => 1];
        }

        $colors = $conceptData?->colors ?? [];
        if (!empty($colors)) {
            $attrs[] = ["label" => "Cor", "value" => e(is_array($colors) ? implode(' / ', $colors) : $colors), "cols" => 1];
        }

        if (isset($conceptData?->cost) && $conceptData->cost !== null) {
            $attrs[] = ["label" => "Custo", "value" => (string) $conceptData->cost, "cols" => 1];
        }

        if (isset($conceptData?->power) && $conceptData->power !== null) {
            $attrs[] = ["label" => "Poder", "value" => number_format($conceptData->power, 0, ',', '.'), "cols" => 1];
        }

        if (isset($conceptData?->counter) && $conceptData->counter !== null) {
            $attrs[] = ["label" => "Contador", "value" => "+ " . number_format($conceptData->counter, 0, ',', '.'), "cols" => 1];
        }

        if (isset($conceptData?->life) && $conceptData->life !== null) {
            $attrs[] = ["label" => "Vida", "value" => (string) $conceptData->life, "cols" => 1];
        }

        $attribute = $conceptData?->attribute ?? null;
        if (!empty($attribute)) {
            $attrs[] = ["label" => "Atributo", "value" => e($attribute), "cols" => 1];
        }

        $typeLine = $printData?->printed_type_line ?? $conceptData?->type_line ?? null;
        if (!empty($typeLine)) {
            $attrs[] = ["label" => "Características / Família", "value" => e($typeLine), "cols" => 2];
        }

        $artist = $printData?->artist ?? null;
        if (!empty($artist)) {
            $attrs[] = ["label" => "Ilustrador", "value" => "<span class=\"italic\">" . e($artist) . "</span>", "cols" => 2];
        }

        return $attrs;
    }

    public function getAvailableStockExtras(): array
    {
        return OnePieceStockExtra::options();
    }
}
