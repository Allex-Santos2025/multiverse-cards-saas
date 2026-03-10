<?php

namespace App\Filament\Resources\CatalogProducts;

use App\Filament\Resources\CatalogProducts\Pages\CreateCatalogProduct;
use App\Filament\Resources\CatalogProducts\Pages\EditCatalogProduct;
use App\Filament\Resources\CatalogProducts\Pages\ListCatalogProducts;
use App\Models\CatalogProduct;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select; // Importante para os dropdowns
use Filament\Schemas\Schema;
use Filament\Schemas\Components;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;
use BackedEnum;
use UnitEnum;

class CatalogProductResource extends Resource
{
    protected static ?string $model = CatalogProduct::class;
    
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'Catálogo de Produtos';
    protected static UnitEnum|string|null $navigationGroup = 'Gestão de Catálogo';
    protected static ?string $modelLabel = 'Produto Global';
    protected static ?string $pluralModelLabel = 'Produtos Globais';

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Components\Section::make('Detalhes Oficiais da Plataforma')
                ->description('As alterações feitas aqui refletem na vitrine de todos os lojistas.')
                ->columns(2)
                ->schema([
                    
                    // 1. NOME: Agora editável para novos cadastros
                    TextInput::make('name')
                        ->label('Produto Oficial')
                        ->required()
                        ->maxLength(255),

                    // 2. JOGO (SELECT): Você vê o Nome, o Filament salva o ID.
                    Select::make('game_id')
                        ->label('Jogo Relacionado')
                        ->relationship('game', 'name') // 'game' é o método no Model, 'name' é a coluna na tabela games
                        ->searchable()
                        ->preload()
                        ->placeholder('Selecione o Jogo (ou deixe vazio para Global)'),

                    // 3. TIPO (SELECT): Para não precisar digitar 'sealed' ou 'accessory'
                    Select::make('type')
                        ->label('Tipo de Item')
                        ->options([
                            'sealed' => 'Produto Selado',
                            'accessory' => 'Acessório',
                        ])
                        ->required()
                        ->native(false), // Deixa o visual mais moderno igual ao resto do Filament

                    TextInput::make('barcode')
                        ->label('Código de Barras (EAN)')
                        ->unique(ignoreRecord: true),

                    \Filament\Forms\Components\Placeholder::make('imagem_atual')
                        ->label('Imagem Atual do Produto')
                        ->content(fn ($record) => $record?->image_path 
                            ? new \Illuminate\Support\HtmlString('<img src="'.asset($record->image_path).'" style="max-width: 150px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">') 
                            : 'Sem foto cadastrada')
                        ->visible(fn ($record) => $record !== null),

                    FileUpload::make('image_path')
                        ->label('Substituir/Fazer Upload')
                        ->saveUploadedFileUsing(function ($file, $record) {
                            if ($record && $record->image_path) {
                                $caminhoAntigo = public_path($record->image_path);
                                if (file_exists($caminhoAntigo)) {
                                    @unlink($caminhoAntigo);
                                }
                            }

                            $filename = $file->hashName();
                            // Mantive sua lógica de pasta fixa para não mudar o comportamento agora
                            copy($file->getRealPath(), public_path('product_images/magic/' . $filename));
                            
                            return 'product_images/magic/' . $filename;
                        })
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Descrição (Português)')
                        ->rows(6)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\ImageColumn::make('image_path')
                    ->label('Foto')
                    ->getStateUsing(fn ($record) => $record->image_path ? asset($record->image_path) : null),

                Columns\TextColumn::make('name')
                    ->label('Produto Oficial')
                    ->searchable()
                    ->sortable(),

                // Mostra o nome do jogo na tabela para facilitar o gerenciamento
                Columns\TextColumn::make('game.name')
                    ->label('Jogo')
                    ->badge()
                    ->color('info')
                    ->placeholder('Global'),

                Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sealed' => 'success',
                        'accessory' => 'warning',
                        default => 'gray',
                    }),

                Columns\IconColumn::make('is_translated')
                    ->label('Traduzido')
                    ->boolean()
                    ->state(fn ($record): bool => !empty($record->description)),
            ])
            ->filters([
                // Filtro por Jogo na tabela também para você achar rápido os produtos
                \Filament\Tables\Filters\SelectFilter::make('game_id')
                    ->label('Filtrar por Jogo')
                    ->relationship('game', 'name'),

                Filter::make('sem_imagem')
                    ->label('Sem Imagem')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNull('image_path')->orWhere('image_path', '')),

                Filter::make('sem_traducao')
                    ->label('Sem Tradução')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNull('description')->orWhere('description', '')),
            ])
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCatalogProducts::route('/'),
            'create' => CreateCatalogProduct::route('/create'),
            'edit' => EditCatalogProduct::route('/{record}/edit'),
        ];
    }
}