<?php

namespace App\Filament\Resources\CardFunctionalities\Pages;

use App\Filament\Resources\CardFunctionalities\CardFunctionalityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCardFunctionality extends EditRecord
{
    protected static string $resource = CardFunctionalityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
