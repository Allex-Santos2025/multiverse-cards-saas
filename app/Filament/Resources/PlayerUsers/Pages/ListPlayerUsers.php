<?php

namespace App\Filament\Resources\PlayerUsers\Pages;

use App\Filament\Resources\PlayerUsers\PlayerUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlayerUsers extends ListRecords
{
    protected static string $resource = PlayerUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
