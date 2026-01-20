<?php

namespace App\Filament\Resources\CatalogPrints\Pages;

use App\Filament\Resources\CatalogPrints\CatalogPrintResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCatalogPrint extends EditRecord
{
    protected static string $resource = CatalogPrintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}