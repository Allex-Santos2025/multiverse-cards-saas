<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CatalogPrints\Pages;
use App\Models\Catalog\CatalogPrint; // O novo Model de Impressão
use App\Models\Catalog\CatalogConcept; // Para a View
use App\Models\Game;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema; // V4: CORRETO
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; 
use Filament\Actions\ViewAction; 
use Filament\Actions\EditAction; 
use Filament\Actions\BulkActionGroup; 
use Filament\Actions\DeleteBulkAction; 
use Filament\Forms\Get;

class CatalogPrintResource extends Resource
{
    protected static ?string $model = CatalogPrint::class;

    // --- PROPRIEDADES DE NAVEGAÇÃO V4 ---
    // Substituí as propriedades estáticas por funções (V4) para resolver o erro de tipagem anterior
    public static function getNavigationIcon(): string { return 'heroicon-o-camera'; }
    public static function getNavigationGroup(): ?string { return 'Catálogo V4'; }
    public static function getNavigationLabel(): string { return 'Impressões (Prints)'; }
    public static function getNavigationSort(): ?int { return 2; }
    
    // --- 1. BUSCA GLOBAL (Adaptada do CardResource) ---

    // A busca é feita no nome do Conceito (Pai)
    protected static ?string $recordTitleAttribute = 'concept.name';

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->concept->name ?? 'N/A';
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [
            'Jogo' => $record->concept->game->name ?? 'N/A', // Acesso ao Jogo via Conceito
            'Set' => $record->set->name ?? 'N/A',
        ];

        if ($record->specific) {
            $details['Número'] = $record->specific->number ?? '-';
            $details['Raridade'] = $record->specific->rarity ?? 'Comum';
        }

        return $details;
    }

    // --- 2. FORMULÁRIO (Adaptado para Polimorfismo) ---
    
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)->schema([
                    // --- COLUNA 1 & 2 (Larga): Vínculos e Textos ---
                    Section::make('Vínculos e Textos')->columnSpan(2)
                        ->schema([
                            // Vínculos (Desabilitados, vêm da ingestão)
                            Grid::make(3)->schema([
                                Select::make('concept_id')
                                    ->relationship('concept', 'name')
                                    ->label('Conceito Base')
                                    ->disabled()
                                    ->required(),
                                
                                Select::make('set_id')
                                    ->relationship('set', 'name')
                                    ->label('Set / Coleção')
                                    ->disabled()
                                    ->required(),
                            ]),

                            // Textos de Edição Manual (Agora no CatalogPrint)
                            TextInput::make('printed_name')
                                ->label('Nome Impresso (Traduzido)')
                                ->maxLength(255),
                            
                            TextInput::make('language_code')
                                ->label('Idioma')
                                ->default('en')
                                ->required()
                                ->maxLength(5),
                            
                            Textarea::make('rules_text') 
                                ->label('Texto de Regras (Override)')
                                ->rows(3),
                            
                            Textarea::make('flavor_text')
                                ->label('Texto de Ambientação (Override)')
                                ->rows(2),
                        ]),

                    // --- COLUNA 3 (Estreita): Imagem e Dados Específicos ---
                    Section::make('Dados da Impressão')->columnSpan(1)
                        ->schema([
                            FileUpload::make('custom_image_path')
                                ->label('Upload Imagem (Custom)')
                                ->disk('public')
                                ->directory('card_images/Custom') 
                                ->image(),
                            
                            // Estes campos agora vêm dos SPECIFIC PRINTS
                            TextInput::make('specific.number')
                                ->label('Nº Colecionador')
                                ->disabled(),
                            TextInput::make('specific.rarity')
                                ->label('Raridade')
                                ->disabled(),
                        ]),
                ])
            ]);
    }

    // --- 3. TABELA (Listagem) ---
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Imagem (Lê do image_path local)
                Tables\Columns\ImageColumn::make('image_path') 
                    ->label('Imagem')
                    ->height(60)
                    ->checkFileExistence(false)
                    ->width(40),
                
                // Conceito Pai
                Tables\Columns\TextColumn::make('concept.name') 
                    ->label('Nome (Conceito)')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                // Set
                Tables\Columns\TextColumn::make('set.name')
                    ->label('Set')
                    ->searchable()
                    ->sortable(),
                
                // Número (Polimórfico - do PkPrint/MtgPrint)
                Tables\Columns\TextColumn::make('specific.number') 
                    ->label('Nº Coleção')
                    ->searchable()
                    ->sortable(),

                // Raridade (Polimórfico)
                Tables\Columns\TextColumn::make('specific.rarity') 
                    ->label('Raridade')
                    ->badge()
                    ->sortable(),
                
                // Idioma (Polimórfico)
                Tables\Columns\TextColumn::make('language_code') // LanguageCode está no CatalogPrint
                    ->label('Idioma')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('set_id')
                    ->relationship('set', 'name')
                    ->label('Filtrar por Set'),
            ])
            ->actions([
                // Clicar no ViewAction vai abrir o ViewCatalogPrint
                Tables\Actions\ViewAction::make(), 
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    // --- 4. REGISTRO DE PÁGINAS ---
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCatalogPrints::route('/'),
            'create' => Pages\CreateCatalogPrints::route('/create'),
            'edit' => Pages\EditCatalogPrint::route('/{record}/edit'),
            'view' => Pages\ViewCatalogPrint::route('/{record}/view'),
        ];
    }
}