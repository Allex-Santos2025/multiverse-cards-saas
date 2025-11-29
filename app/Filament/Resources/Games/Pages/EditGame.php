<?php

namespace App\Filament\Resources\Games\Pages;

use App\Filament\Resources\Games\GameResource;
use App\Filament\Resources\Sets\SetResource;
use Filament\Actions;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\EditRecord;

class EditGame extends EditRecord
{
    protected static string $resource = GameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    /**
     * CORREÇÃO FINAL DO CONFLITO: Força o Admin a buscar o registro pelo ID, 
     * ignorando o getRouteKeyName('url_slug') que está ativo no Model.
     */
    protected function resolveRecord(mixed $key): \Illuminate\Database\Eloquent\Model
    {
        // O $key será o valor '1' na URL. Forçamos o Laravel a procurar na coluna 'id'.
        return static::getModel()::findOrFail($key);
    }
}
