<?php

namespace App\Filament\Resources\CatalogConcepts\Pages;

// CORREÇÃO: Importação direta da classe do Resource Pai
use App\Filament\Resources\CatalogConcepts\CatalogConceptResource; 

use App\Models\Catalog\CatalogPrint;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCatalogConcept extends ListRecords
{
    protected static string $resource = CatalogConceptResource::class;

    protected function getHeaderActions(): array
    {
        // Removido CreateAction (sistema de ingestão)
        return [];
    }

    /**
     * Sobrescreve a query Eloquent para adicionar a lógica de busca avançada:
     * Procura no Nome do Conceito (name) OU no Nome Impresso (name/printed_name) em Português (pt).
     */
    protected function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $search = $this->getTableSearch();

        if ($search) {
            $query->where(function (Builder $q) use ($search) {
                // 1. Busca no nome principal
                $q->where('name', 'like', "%{$search}%")
                
                // 2. Busca nas impressões ('prints') associadas
                ->orWhereHas('prints', function (Builder $printQuery) use ($search) {
                    $printQuery->whereHas('specific', function (Builder $specificQuery) use ($search) {
                        
                        // Busca o nome impresso em português ('pt')
                        // Tenta 'printed_name' (Magic) e 'name' (Fallback)
                        $specificQuery->where('language_code', 'pt') 
                                      ->where(function (Builder $nameQuery) use ($search) {
                                          $nameQuery->where('printed_name', 'like', "%{$search}%")
                                                    ->orWhere('name', 'like', "%{$search}%");
                                      });
                    });
                });
            });
        }

        return $query;
    }
}