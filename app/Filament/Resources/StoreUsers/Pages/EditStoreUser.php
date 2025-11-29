<?php

namespace App\Filament\Resources\StoreUsers\Pages;

use App\Filament\Resources\StoreUsers\StoreUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStoreUser extends EditRecord
{
    protected static string $resource = StoreUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
