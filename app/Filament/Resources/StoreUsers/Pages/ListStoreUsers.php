<?php

namespace App\Filament\Resources\StoreUsers\Pages;

use App\Filament\Resources\StoreUsers\StoreUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStoreUsers extends ListRecords
{
    protected static string $resource = StoreUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
