<?php

namespace App\Filament\Resources\Stores;

use App\Filament\Resources\Stores\Pages\CreateStore;
use App\Filament\Resources\Stores\Pages\EditStore;
use App\Filament\Resources\Stores\Pages\ListStores;
use App\Filament\Resources\Stores\Schemas\StoreForm;
use App\Filament\Resources\Stores\Tables\StoresTable;
use App\Models\Store;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn; // <-- ADICIONE ESTE USE STATEMENT NO TOPO!


class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema 
{
    return $schema
        ->schema([
            // ----------------------------------------------------
            // 1. CAMPO DE POSSE (HIDDEN)
            // ----------------------------------------------------
            Hidden::make('user_id')
                ->default(Auth::id())
                ->required(), 
            
            // ----------------------------------------------------
            // 2. CAMPOS DE IDENTIDADE (Aquisição)
            // ----------------------------------------------------
            TextInput::make('name')
                ->label('Nome da Loja (Marca)')
                ->required()
                ->maxLength(255)
                ->columnSpan(1),

            TextInput::make('url_slug')
                ->label('Identificador da URL (Slug)')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50)
                ->columnSpan(1),

            Textarea::make('slogan')
                ->label('Slogan ou Breve Descrição')
                ->nullable()
                ->rows(2)
                ->maxLength(500)
                ->columnSpanFull(),

            // 3. MARGENS FINANCEIRAS
            TextInput::make('purchase_margin_cash')
                ->label('Margem de Lucro (Dinheiro/PIX)')
                ->required()
                ->numeric()
                ->step(0.001)
                ->minValue(0.05)
                ->maxValue(1.0)
                ->default(0.400)
                ->columnSpan(1),

            TextInput::make('purchase_margin_credit')
                ->label('Margem de Lucro (Crédito na Loja)')
                ->required()
                ->numeric()
                ->step(0.001)
                ->minValue(0.05)
                ->maxValue(1.0)
                ->default(0.300)
                ->columnSpan(1),
                
            // 4. LIMITES DE FIDELIDADE
            TextInput::make('max_loyalty_discount')
                ->label('Máx. Desconto por Fidelidade')
                ->required()
                ->numeric()
                ->step(0.001)
                ->minValue(0.0) 
                ->maxValue(0.5) 
                ->default(0.200) 
                ->columnSpan(1),

            TextInput::make('pix_discount_rate')
                ->label('Taxa de Desconto PIX')
                ->required()
                ->numeric()
                ->step(0.001)
                ->minValue(0.0)
                ->maxValue(0.10) 
                ->default(0.050) 
                ->columnSpan(1),
        ]);
        // Não podemos usar ->columns(2) em Schema.
        // O layout será resolvido no Painel Admin.
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome da Loja')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url_slug')
                    ->label('Slug da URL')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('purchase_margin_cash')
                    ->label('Margem (Dinheiro)')
                    ->prefix('%') // Adiciona o símbolo de % (opcional)
                    ->sortable(),

                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge() // Exibe um indicador visual (badge)
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'success',
                        '0' => 'danger',
                    }),
            ])
            ->filters([
                // Filtros virão aqui
            ])
            ->actions([
                // Ações virão aqui
            ])
            ->bulkActions([
                // Ações em massa virão aqui
            ]);
    }

    // MÉTODOS DE NAVEGAÇÃO
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-building-storefront';
    }
    
    public static function getModelLabel(): string
    {
        return 'Lojas';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Gestão de Clientes e Lojas';
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StockItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStores::route('/'),
            'create' => CreateStore::route('/create'),
            'edit' => EditStore::route('/{record}/edit'),
        ];
    }
}
