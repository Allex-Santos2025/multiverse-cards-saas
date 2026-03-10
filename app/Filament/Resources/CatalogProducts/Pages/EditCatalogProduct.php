<?php

namespace App\Filament\Resources\CatalogProducts\Pages;

use App\Filament\Resources\CatalogProducts\CatalogProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCatalogProduct extends EditRecord
{
    protected static string $resource = CatalogProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}