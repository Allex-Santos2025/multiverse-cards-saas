<?php

namespace App\Filament\Resources\CatalogConcepts;

use App\Filament\Resources\CatalogConcepts\Pages;
use App\Filament\Resources\CatalogConcepts\RelationManagers;
use App\Models\Catalog\CatalogConcept;
use App\Models\Game;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Schema; // V4: CORRETO - Usando sua estrutura personalizada
use Filament\Forms; 
use Filament\Forms\Get; 

class CatalogConceptResource extends Resource
{
    protected static ?string $model = CatalogConcept::class;

    

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [
            'Jogo' => $record->game->name ?? 'N/A',
        ];

        if ($record->specific) {
            // MAGIC (ID 1)
            if ($record->game_id === 1) { 
                $details['Tipo'] = $record->specific->mtg_type_line ?? '';
                if (!empty($record->specific->mtg_mana_cost)) {
                    $details['Custo'] = new HtmlString(static::convertManaSymbolsToHtml($record->specific->mtg_mana_cost));
                }
            } 
            // POKÉMON (ID 2)
            elseif ($record->game_id === 2) { 
                $sub = is_array($record->specific->subtypes) ? implode(' ', $record->specific->subtypes) : '';
                $details['Tipo'] = trim(($record->specific->supertype ?? '') . ' ' . $sub);
            }
        }

        return $details;
    }

    // --- 2. FORMULÁRIO (ADAPTADO PARA SCHEMA V4) ---

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\Grid::make(1)->schema([
                    // Seleção de Jogo
                    Forms\Components\Select::make('game_id')
                        ->label('Jogo')
                        ->relationship('game', 'name')
                        ->required()
                        ->live() 
                        ->disabled(), 
                ]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Informações Básicas')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nome Principal')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // SEÇÃO MAGIC: THE GATHERING (ID 1)
                Forms\Components\Section::make('Detalhes Magic')
                    ->relationship('specific') 
                    ->schema([
                        Forms\Components\TextInput::make('mtg_mana_cost')->label('Custo de Mana'),
                        Forms\Components\TextInput::make('mtg_type_line')->label('Tipo'),
                        Forms\Components\Textarea::make('mtg_rules_text')->label('Texto de Regras')->rows(4),
                    ])
                    ->visible(fn (Get $get) => $get('game_id') == 1 || optional($schema->getRecord())->game_id == 1),

                // SEÇÃO POKÉMON TCG (ID 2)
                Forms\Components\Section::make('Detalhes Pokémon')
                    ->relationship('specific') 
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('hp')->label('HP'),
                            Forms\Components\TextInput::make('supertype')->label('Tipo Principal'),
                            Forms\Components\TextInput::make('level')->label('Level'),
                        ]),
                        Forms\Components\TagsInput::make('types')->label('Tipos'),
                        Forms\Components\TagsInput::make('subtypes')->label('Subtipos'),
                    ])
                    ->visible(fn (Get $get) => $get('game_id') == 2 || optional($schema->getRecord())->game_id == 2),
            ]);
    }

    // --- 3. TABELA (Listagem) ---

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->width(50)
                    ->sortable(),

                Tables\Columns\TextColumn::make('game.name')
                    ->label('Jogo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pokémon TCG' => 'warning',
                        'Magic: The Gathering' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // Coluna Tipo Dinâmica
                Tables\Columns\TextColumn::make('type_line')
                    ->label('Tipo')
                    ->state(function (Model $record) {
                        if (!$record->specific) return '-';
                        
                        if ($record->game_id === 1) { // Magic
                            return $record->specific->mtg_type_line;
                        }
                        if ($record->game_id === 2) { // Pokemon
                            $sub = is_array($record->specific->subtypes) ? implode(' ', $record->specific->subtypes) : '';
                            return trim(($record->specific->supertype ?? '') . ' ' . $sub);
                        }
                        return '-';
                    }),

                // Coluna Custo/Info Dinâmica
                Tables\Columns\TextColumn::make('cost_info')
                    ->label('Custo / HP')
                    ->html()
                    ->state(function (Model $record) {
                        if (!$record->specific) return '-';

                        // Magic: Custo de Mana com ícones
                        if ($record->game_id === 1 && !empty($record->specific->mtg_mana_cost)) {
                            return static::convertManaSymbolsToHtml($record->specific->mtg_mana_cost);
                        }

                        // Pokemon: HP em vermelho
                        if ($record->game_id === 2 && !empty($record->specific->hp)) {
                            return "<span class='text-red-600 font-bold'>HP {$record->specific->hp}</span>";
                        }

                        return '-';
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('game_id')
                    ->label('Filtrar por Jogo')
                    ->relationship('game', 'name'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCatalogConcepts::route('/'),
            'create' => Pages\CreateCatalogConcept::route('/create'),
            'view' => Pages\ViewCatalogConcept::route('/{record}'),
            'edit' => Pages\EditCatalogConcept::route('/{record}/edit'),
        ];
    }

    // --- 4. HELPER VISUAL ---
    public static function convertManaSymbolsToHtml(?string $text): string
    {
        if (blank($text)) {
            return '';
        }
        
        preg_match_all('/({[^}]+})/', $text, $matches);
        
        if (empty($matches[0])) {
            return htmlspecialchars($text);
        }
        
        $html = htmlspecialchars($text);
        foreach ($matches[0] as $symbol) {
            $class = strtolower(trim($symbol, '{}'));
            $class = str_replace(['/', 'p'], '', $class); 
            if ($class === 't') $class = 'tap'; 
            if ($class === 'q') $class = 'untap'; 
            $class = preg_replace('/[^a-z0-9\-]/', '', $class); 
            
            if (!empty($class)) {
                $iconHtml = "<i class=\"ms ms-{$class} ms-cost ms-shadow\" style=\"vertical-align: -0.05em; font-size: 0.9em;\"></i>"; 
                $html = preg_replace('/' . preg_quote($symbol, '/') . '/', $iconHtml, $html, 1);
            }
        }
        return $html;
    }
}