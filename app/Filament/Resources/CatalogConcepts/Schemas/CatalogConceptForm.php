<?php

namespace App\Filament\Resources\CatalogConcepts\Schemas; // Namespace correto para o aninhamento

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema; // V4: Class Schema

class CatalogConceptForm
{
    /**
     * Define o Schema da Funcionalidade (Conceito)
     * @param Schema $schema
     * @return Schema
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // game_id (Herdado do antigo)
                TextInput::make('game_id')
                    ->label('ID do Jogo (Herdado)')
                    ->required()
                    ->numeric()
                    ->disabled(),
                
                // Campos de identificação (Herdado do antigo: oracle_id, generic_name)
                // Na V4, o oracle_id agora vive em colunas específicas (mtg_oracle_id, etc.)
                TextInput::make('name')
                    ->label('Nome Principal (Conceito)')
                    ->required(),
                
                // Campos de regras/custo (Geralmente JSON)
                TextInput::make('mtg_mana_cost') // Ex: Custo de Mana (Se for Magic)
                    ->label('Custo de Mana'),
                
                Textarea::make('rules_text') // Texto de Regras
                    ->label('Texto de Regras (Genérico)')
                    ->default(null)
                    ->columnSpanFull(),
                    
                // legalities (JSON de legalidades)
                Textarea::make('legalities')
                    ->label('Legalidades JSON')
                    ->default(null)
                    ->columnSpanFull(),
                
                // colors (JSON de cores)
                Textarea::make('colors')
                    ->label('Cores/Identidade JSON')
                    ->default(null)
                    ->columnSpanFull(),

                // max_copies (Máximo de cópias)
                TextInput::make('max_copies')
                    ->label('Máximo de Cópias')
                    ->required()
                    ->numeric()
                    ->default(4),
            ]);
    }
}