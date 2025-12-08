<?php

namespace App\Filament\Resources\Sets\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CardsRelationManager extends RelationManager
{
    protected static string $relationship = 'cards';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
    return $table
        ->recordTitleAttribute('name')
        ->columns([
            // A coluna de imagem!
            ImageColumn::make('image_url')
                ->label('Arte')
                ->height(60) // Ajuste a altura como preferir
                ->square(),

            TextColumn::make('name')
                ->label('Nome')
                ->searchable(),

            TextColumn::make('collection_number')
                ->label('Nº')
                ->sortable(),

            TextColumn::make('rarity')
                ->label('Raridade')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'common' => 'gray',
                    'uncommon' => 'info',
                    'rare' => 'warning',
                    'mythic' => 'danger',
                    default => 'primary',
                })
                ->sortable(),
        ]);
    }
}
