<?php

namespace App\Filament\Resources\PlayerUsers\Pages;

use App\Filament\Resources\PlayerUsers\PlayerUserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPlayerUser extends EditRecord
{
    protected static string $resource = PlayerUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
