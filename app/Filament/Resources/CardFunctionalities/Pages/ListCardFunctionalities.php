<?php

namespace App\Filament\Resources\CardFunctionalities\Pages;

use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CardFunctionalities\CardFunctionalityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCardFunctionalities extends ListRecords
{
    protected static string $resource = CardFunctionalityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    protected function getEloquentQuery(): Builder
    {
        // Pega a query padrão que o Filament faria
        $query = parent::getEloquentQuery();

        // Pega o que o usuário digitou na barra de busca
        $search = $this->getTableSearch();

        // Se o usuário digitou algo...
        if ($search) {
            // ...modifica a query para procurar em dois lugares:
            $query->where(function (Builder $q) use ($search) {
                // 1. No nome genérico (Inglês)
                $q->where('generic_name', 'like', "%{$search}%")
                // OU...
                ->orWhereHas('cards', function (Builder $cardQuery) use ($search) {
                    // 2. Nas impressões ('cards') associadas...
                    $cardQuery->where('language_code', 'pt') // ...que estejam em Português ('pt')...
                                ->where('name', 'like', "%{$search}%"); // ...e cujo nome impresso corresponda à busca.
                });
            });
        }

        // Retorna a query modificada (ou a original, se não houve busca)
        return $query;
    }
}
