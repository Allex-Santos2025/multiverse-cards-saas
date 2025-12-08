<?php

namespace App\Filament\Resources\CatalogConcepts\Pages;

use App\Filament\Resources\CatalogConcepts\CatalogConceptResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCatalogConcept extends EditRecord
{
    protected static string $resource = CatalogConceptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
