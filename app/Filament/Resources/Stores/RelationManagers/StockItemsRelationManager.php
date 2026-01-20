<?php

namespace App\Filament\Resources\Stores\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\CreateAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextInputColumn;

class StockItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockItems';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('card_id')
                    ->label('Card')
                    ->relationship('card', 'name')
                    ->searchable()
                    ->required(),

                TextInput::make('quantity')->label('Estoque')->numeric()->default(1)->required(),
                TextInput::make('price')->label('Preço')->numeric()->prefix('R$')->required(),

                Select::make('language')->label('Idioma')->options(['en' => 'Inglês', 'pt' => 'Português', 'jp' => 'Japonês'])->default('pt')->required(),
                Select::make('condition')->label('Qualidade')->options(['NM' => 'Near Mint', 'SP' => 'Slightly Played', 'MP' => 'Moderately Played', 'HP' => 'Heavily Played', 'DM' => 'Damaged'])->default('NM')->required(),

                Toggle::make('is_foil')->label('Foil'),
            ]);
    }

    public function table(Table $table): Table
{
    return $table
        ->recordTitleAttribute('id')
        ->columns([
            ImageColumn::make('card.image_url')->label('Arte'),
            TextColumn::make('card.name')->label('Card')->searchable(),
            
            // Coluna de Idioma adicionada
            SelectColumn::make('language')->label('Idioma')
                ->options(['pt' => 'PT', 'en' => 'EN', 'jp' => 'JP', 'es' => 'ES']),

            SelectColumn::make('condition')->label('Condição')
                ->options(['NM' => 'NM', 'SP' => 'SP', 'MP' => 'MP', 'HP' => 'HP', 'DM' => 'DM'])
                ->sortable(),
            
            ToggleColumn::make('is_foil')->label('Foil'),
            
            TextInputColumn::make('quantity')->label('Qtd.')
                ->rules(['required', 'numeric', 'min:0']),
                
            TextInputColumn::make('price')->label('Preço')
                ->rules(['required', 'numeric', 'min:0']),
        ])
        ->filters([
            //
        ])
        ->headerActions([
            // ESTA É A PARTE QUE CRIA O BOTÃO "NEW STOCK ITEM"
            CreateAction::make(),
        ])
        ->actions([
            // You can add Edit and Delete buttons here later if you wish
        ]);
}
}
