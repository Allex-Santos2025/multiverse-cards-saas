<?php

// ***** CORREÇÃO: Namespace e Caminho Corretos (Sets) *****
namespace App\Filament\Resources\Sets;

use App\Filament\Resources\Sets\Pages;
use App\Models\Set;
use App\Models\Game;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema; // <-- CORRETO (Usando Schema)
use Filament\Resources\Resource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SetResource extends Resource
{
    protected static ?string $model = Set::class;

    // ***** CORREÇÃO: Usando Funções (V4) (Inspirado no PlayerUserResource) *****
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-rectangle-stack'; // Ícone de Set
    }

    public static function getModelLabel(): string
    {
        return 'Set (Coleção)';
    }

    
    public static function form(Schema $schema): Schema
    {
        // ***** CORREÇÃO: Campos corretos para um SET (não um Card) *****
        return $schema
            ->schema([
                Section::make('Informações do Set')
                    ->schema([
                        Select::make('game_id')
                            ->relationship('game', 'name')
                            ->searchable()
                            ->required()
                            ->label('Jogo'),
                        
                        TextInput::make('name')
                            ->label('Nome do Set (ex: Universo Marvel)')
                            ->required()
                            ->maxLength(100),
                        
                        TextInput::make('code')
                            ->label('Código do Set (ex: BSUM, FAN-LND)')
                            ->required()
                            ->maxLength(50)
                            ->helperText('O código curto usado para pastas e filtros.'),
                        
                        DatePicker::make('released_at')
                            ->label('Data de Lançamento')
                            ->default(now()),
                        
                        TextInput::make('tcg_name')
                            ->label('Nome do TCG (ex: Battle Scenes)')
                            ->required()
                            ->default('Battle Scenes')
                            ->maxLength(50),
                            
                        Toggle::make('is_fanmade')
                            ->label('É um Set Fanmade?')
                            ->default(false)
                            ->helperText('Marque se este set não for oficial (para Battle Scenes).'),

                        TextInput::make('card_count')
                            ->label('Contagem de Cards')
                            ->numeric()
                            ->default(0),

                        TextInput::make('scryfall_id')
                            ->label('Scryfall ID (Opcional)')
                            ->maxLength(36)
                            ->nullable(),
                            
                        TextInput::make('set_type')
                            ->label('Tipo do Set (ex: expansion, fan_made)')
                            ->default('expansion')
                            ->maxLength(50),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        // ***** CORREÇÃO: Colunas corretas para a lista de SETS *****
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome do Set')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('code')
                    ->label('Código')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('game.name')
                    ->label('Jogo')
                    ->badge()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('released_at')
                    ->label('Lançamento')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_fanmade')
                    ->label('Fanmade')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('game_id')
                    ->relationship('game', 'name')
                    ->label('Filtrar por Jogo'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    // ***** CORREÇÃO: Apontando para as Pages corretas de SETS *****
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSets::route('/'),
            'create' => Pages\CreateSet::route('/create'),
            'edit' => Pages\EditSet::route('/{record}/edit'),
        ];
    }    
}

