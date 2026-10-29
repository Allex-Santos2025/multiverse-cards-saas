<?php

namespace App\Services\GamePresenters;

use App\Models\Catalog\CatalogPrint;
use Illuminate\Support\Collection;

interface GamePresenterInterface
{
    public function getPrimaryTitle(): string;
    public function getSecondaryTitle(): ?string;
    public function hasSecondaryTitle(): bool;
    public function getCollectorNumberLabel(?CatalogPrint $print = null): ?string;
    public function getDefaultIconType(): string;
    public function resolveMatchingPrints(Collection $allPrints, string $conceptSlug): Collection;
    public function resolveProductData(string $conceptSlug): array;
    public function formatPublicSlug(string $slug, ?string $conceptName = null): string;
    public function getAvailableStockExtras(): array;
    public function getPrintVariantKey($print): string;

    // === DECK BUILDER, SANDBOX & PREÇOS (MULTI-GAME) ===
    public function getDeckZones(?string $format = null): array;
    public function getDeckFormats(): array;
    public function getDeckValidationRules(?string $format = null): array;
    public function getPrintMarketPrice(CatalogPrint $print, array $extras = []): float;
}
