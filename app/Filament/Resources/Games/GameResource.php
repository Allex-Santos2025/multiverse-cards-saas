<?php

namespace App\Filament\Resources\Games;

use App\Filament\Resources\Games\Pages\CreateGame;
use App\Filament\Resources\Games\Pages\EditGame;
use App\Filament\Resources\Games\Pages\ListGames;
use App\Filament\Resources\Games\RelationManagers;
use App\Models\Game;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // ----------------------------------------------------
                // 1. IDENTIFICAÇÃO BÁSICA
                // ----------------------------------------------------
                TextInput::make('name')
                    ->label('Nome do Jogo (Ex: Magic: The Gathering)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->columnSpan(2), 
                
                TextInput::make('publisher')
                    ->label('Editora')
                    ->nullable()
                    ->maxLength(255)
                    ->columnSpan(2),

                // ----------------------------------------------------
                // 2. INTEGRAÇÃO DA API
                // ----------------------------------------------------
                TextInput::make('api_url')
                    ->label('URL Base da API (Ex: https://api.scryfall.com)')
                    ->nullable()
                    ->url()
                    ->maxLength(255)
                    ->columnSpan(2),

                // ----------------------------------------------------
                // 3. REGRAS E STATUS
                // ----------------------------------------------------
                Textarea::make('formats_list')
                    ->label('Formatos Válidos (JSON)')
                    ->nullable()
                    ->helperText('Ex: ["Commander", "Standard"]. (Necessário para o Deck Builder)')
                    ->columnSpan(2),

                Toggle::make('is_active')
                    ->label('Jogo Ativo')
                    ->default(true)
                    ->columnSpanFull(),
            ])
            ->columns(2); // O formulário terá 2 colunas
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // ADICIONAMOS A COLUNA ID (Obrigatória para construir o link de edição)
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->hidden(), 
                    
                TextColumn::make('name')
                    ->label('Nome do Jogo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('publisher')
                    ->label('Editora')
                    ->searchable(),

                TextColumn::make('api_url')
                    ->label('URL da API')
                    ->url(fn ($record) => $record->api_url, true),

                TextColumn::make('formats_list')
                    ->label('Formatos')
                    ->limit(20)
                    ->tooltip(fn ($state) => $state),

                IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
                    
            ])
            ->filters([
                // Filtros virão aqui
            ])
            // ... dentro de public static function table(Table $table): Table

            ->actions([
            // CORREÇÃO CRÍTICA: FORÇA O EDITAR A USAR O ID
            EditAction::make()
                ->url(fn (Model $record): string => static::getUrl('edit', ['record' => $record->id])),
        ])

            ->bulkActions([
                // Ações em massa virão aqui
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGames::route('/'),
            'create' => CreateGame::route('/create'),
            'edit' => EditGame::route('/{record}/edit'),
        ];
    }
}