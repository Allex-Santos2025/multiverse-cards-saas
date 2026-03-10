<?php

namespace App\Filament\Resources\CatalogProducts\Pages;

use App\Filament\Resources\CatalogProducts\CatalogProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCatalogProduct extends CreateRecord
{
    protected static string $resource = CatalogProductResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}