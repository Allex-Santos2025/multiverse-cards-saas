<?php

namespace App\Filament\Resources\StoreAdminUsers\Pages;

use App\Filament\Resources\StoreAdminUsers\StoreAdminUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStoreAdminUsers extends ListRecords
{
    protected static string $resource = StoreAdminUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
