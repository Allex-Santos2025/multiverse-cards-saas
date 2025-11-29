<?php

namespace App\Filament\Resources\CardFunctionalities;

use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\CardFunctionalities\Pages;
use App\Filament\Resources\CardFunctionalities\RelationManagers;
use App\Models\CardFunctionality;
use App\Models\Game; 
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\schema\Components\Grid; // Adicionado para o formulário
use Filament\schema\Components\Section; // Adicionado para o formulário
use Filament\Forms\Get; // Adicionado para o formulário
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Schema; // V4: CORRETO

class CardFunctionalityResource extends Resource
{
    protected static ?string $model = CardFunctionality::class;

    public static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    // -- LÓGICA DE BUSCA E EXIBIÇÃO --

    protected static bool $shouldShowGlobalSearch = true;

    // CORREÇÃO 2: Aponta para a coluna de busca correta (que agora existe)
    protected static ?string $recordTitleAttribute = 'searchable_names';

    protected static array $globallySearchableAttributes = [
        'searchable_names', // Coluna de busca principal
    ];

    /**
     * Sobrescreve o título do resultado da busca (para ser o nome limpo).
     */
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        // CORREÇÃO 2: Usa o accessor 'name' (do Model 1)
        return $record->name;
    }

    /**
     * Adiciona os detalhes (Tipo e Custo) abaixo do título no resultado da busca.
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        // CORREÇÃO 2: Lógica agnóstica
        $details = [
            'Jogo' => $record->tcg_name, // <-- A SUA SUGESTÃO (PERFEITA)
            'Tipo' => $record->type_line, // Usa o accessor
        ];

        // Adiciona custo apenas se existir
        if ($record->cost) {
            // Formata símbolos apenas para Magic
            if ($record->tcg_name === 'Magic: The Gathering') {
                $details['Custo'] = new HtmlString(static::convertManaSymbolsToHtml($record->cost));
            } else {
                $details['Custo'] = $record->cost;
            }
        }

        return $details;
    }
    
    /**
     * Sobrescreve o título principal do registro (para ser usado nos Breadcrumbs e Títulos de Página).
     */
    public static function getRecordTitle(?Model $record): \Illuminate\Contracts\Support\Htmlable|string|null
    {
        if (!$record) {
            return null;
        }
        // CORREÇÃO 2: Usa o accessor 'name'
        return $record->name;
    }

    /**
     * Força o LIKE e ignora filtros de scope para a busca global.
     * (LÓGICA ORIGINAL MANTIDA - AGORA FUNCIONA)
     */
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->when(
            request()->filled('global_search'),
            function (Builder $query) {
                $query->withoutGlobalScopes();

                $search = request('global_search');
                
                // Aplica a lógica de busca que funciona (na coluna correta)
                $query->where(function (Builder $query) use ($search) {
                    $query->where('searchable_names', 'like', "%{$search}%");
                });
            }
        );
    }
    
    //---------------------------------------------------------

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }

    /**
     * CORREÇÃO 4: Formulário dinâmico
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(1)->schema([
                    Forms\Components\Select::make('tcg_name')
                        ->label('TCG Name')
                        ->options(Game::class::pluck('name', 'name'))
                        ->required()
                        ->live()
                        ->searchable(),
                ]),

                // --- CAMPOS DE NOME DINÂMICOS ---
                // (Mostra apenas os campos do TCG selecionado)

                Section::make('Magic: The Gathering')
                    ->visible(fn (Get $get) => $get('tcg_name') === 'Magic: The Gathering')
                    ->schema([
                        Forms\Components\TextInput::make('mtg_name')->label('Nome (Oracle)')->required(),
                        // TODO: Adicionar outros campos de MTG se necessário
                    ]),
                
                Section::make('Battle Scenes')
                    ->visible(fn (Get $get) => $get('tcg_name') === 'Battle Scenes')
                    ->schema([
                        Forms\Components\TextInput::make('bs_name')->label('Nome')->required(),
                        Forms\Components\TextInput::make('bs_alter_ego')->label('Alter Ego'),
                    ]),

                Section::make('Pokémon TCG')
                    ->visible(fn (Get $get) => $get('tcg_name') === 'Pokémon TCG')
                    ->schema([
                        Forms\Components\TextInput::make('pk_name')->label('Nome')->required(),
                    ]),
                
                Section::make('Yu-Gi-Oh!')
                    ->visible(fn (Get $get) => $get('tcg_name') === 'Yu-Gi-Oh!')
                    ->schema([
                        Forms\Components\TextInput::make('ygo_name')->label('Nome')->required(),
                    ]),

                Section::make('One Piece Card Game')
                    ->visible(fn (Get $get) => $get('tcg_name') === 'One Piece Card Game')
                    ->schema([
                        Forms\Components\TextInput::make('op_name')->label('Nome')->required(),
                    ]),
                
                Section::make('Lorcana TCG')
                    ->visible(fn (Get $get) => $get('tcg_name') === 'Lorcana TCG')
                    ->schema([
                        Forms\Components\TextInput::make('lor_name')->label('Nome')->required(),
                        Forms\Components\TextInput::make('lor_title')->label('Título (Subnome)'),
                    ]),

                Section::make('Flesh and Blood')
                    ->visible(fn (Get $get) => $get('tcg_name') === 'Flesh and Blood')
                    ->schema([
                        Forms\Components\TextInput::make('fab_name')->label('Nome')->required(),
                    ]),
                
                Section::make('Star Wars: Unlimited')
                    ->visible(fn (Get $get) => $get('tcg_name') === 'Star Wars: Unlimited')
                    ->schema([
                        Forms\Components\TextInput::make('swu_name')->label('Nome')->required(),
                        Forms\Components\TextInput::make('swu_title')->label('Título (Subnome)'),
                    ]),
            ]);
    }

    /**
     * CORREÇÃO 3: Tabela de Listagem
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Coluna 1: Nome (Usa o Accessor, não é pesquisável diretamente)
                TextColumn::make('name')
                    ->label('Nome do Card'),

                // Coluna 2: Jogo (Para diferenciar)
                TextColumn::make('tcg_name')
                    ->label('Jogo')
                    ->badge()
                    ->sortable(),

                // Coluna 3: Tipo (Usa o Accessor)
                TextColumn::make('type_line')
                    ->label('Tipo'),
                
                // Coluna 4: Custo (Usa o Accessor)
                TextColumn::make('cost')
                    ->label('Custo')
                    ->html()
                    ->formatStateUsing(function (string $state, Model $record): HtmlString {
                        // Formata símbolos apenas para Magic
                        if ($record->tcg_name === 'Magic: The Gathering') {
                            return new HtmlString(static::convertManaSymbolsToHtml($state));
                        }
                        return new HtmlString(htmlspecialchars($state));
                    }),
                
                // Coluna 5: A Coluna de Busca Real (Como no seu original)
                TextColumn::make('searchable_names')
                    ->label('Busca (Hidden)')
                    ->searchable() // ATIVAMOS O SEARCH AQUI
                    ->hidden(),
            ])
            ->filters([
                // ... seus filtros
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->recordUrl(
                fn ($record) => static::getUrl('view', ['record' => $record])
            );
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CardsRelationManager::class,
            RelationManagers\StockItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCardFunctionalities::route('/'),
            'create' => Pages\CreateCardFunctionality::route('/create'),
            'edit' => Pages\EditCardFunctionality::route('/{record}/edit'),
            'view' => Pages\ViewCardFunctionality::route('/{record}/view'),
        ];
    }
    
    /**
     * Helper para converter símbolos de mana em texto (ex: {1}{W}) para HTML da fonte Mana.
     * (INTOCADO - Ainda é usado pela Tabela e Busca Global)
     */
    protected static function convertManaSymbolsToHtml(?string $text): string
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