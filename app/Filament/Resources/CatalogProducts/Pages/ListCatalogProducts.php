<?php

namespace App\Filament\Resources\CatalogProducts\Pages; // Namespace ajustado

use App\Filament\Resources\CatalogProducts\CatalogProductResource; // Resource ajustado
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCatalogProducts extends ListRecords
{
    protected static string $resource = CatalogProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo Produto Global'),
        ];
    }
}