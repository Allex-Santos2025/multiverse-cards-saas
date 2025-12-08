<?php

namespace App\Filament\Resources\ListCatalogPrints\Pages;

use App\Filament\Resources\ListCatalogPrints\ListCatalogPrint;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCatalogPrints extends ListRecords
{
    protected static string $resource = ListCatalogPrint::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}