<?php

namespace App\Filament\Resources\StoreAdminUsers\Pages;

use App\Filament\Resources\StoreAdminUsers\StoreAdminUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStoreAdminUser extends EditRecord
{
    protected static string $resource = StoreAdminUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
