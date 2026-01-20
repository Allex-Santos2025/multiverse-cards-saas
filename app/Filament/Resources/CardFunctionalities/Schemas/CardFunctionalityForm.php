<?php

namespace App\Filament\Resources\CardFunctionalities\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CardFunctionalityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('game_id')
                    ->required()
                    ->numeric(),
                TextInput::make('oracle_id')
                    ->required(),
                TextInput::make('generic_name')
                    ->required(),
                Textarea::make('rules_text')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('max_copies')
                    ->required()
                    ->numeric()
                    ->default(4),
                Textarea::make('legalities')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('colors')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
