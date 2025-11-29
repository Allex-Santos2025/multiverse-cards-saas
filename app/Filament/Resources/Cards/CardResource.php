<?php

// ***** CORREÇÃO: Namespace e Caminho Corretos *****
namespace App\Filament\Resources\Cards;

use App\Filament\Resources\Cards\Pages;
use App\Filament\Resources\CardFunctionalities\CardFunctionalityResource; // Importado para o ViewAction
use App\Models\Card;
use App\Models\Game;
use App\Models\Set;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema; // <-- CORRETO (Usando Schema)
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; 
use Filament\Actions\ViewAction; // <-- ADICIONADO
use Filament\Actions\EditAction; // <-- ADICIONADO
use Filament\Actions\BulkActionGroup; // <-- ADICIONADO
use Filament\Actions\DeleteBulkAction; // <-- ADICIONADO


class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    // ***** CORREÇÃO FINAL: Substituindo Propriedades por Funções (V4) *****
    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-identification';
    }
     
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)->schema([ // Usando o Grid do Schema
                    // --- COLUNA 1 & 2 (Larga) ---
                    Section::make('Dados Principais')
                        ->schema([
                            
                            // ***** CORREÇÃO: Removido o Select::make('game_id') que causava o erro *****
                            
                            // ***** CORREÇÃO: set_id agora é o campo principal *****
                            Select::make('set_id')
                                ->relationship('set', 'name') // A relação 'set' existe no Model Card
                                ->searchable()
                                ->required()
                                ->reactive() // Torna reativo
                                ->afterStateUpdated(function (callable $set, $state) {
                                    // Busca o Set selecionado para pegar o tcg_name
                                    $selectedSet = \App\Models\Set::find($state);
                                    if ($selectedSet) {
                                        $set('tcg_name', $selectedSet->tcg_name); // Preenche o campo tcg_name
                                    }
                                })
                                ->label('Set (Coleção)'),

                            TextInput::make('tcg_name')
                                ->label('Nome do TCG')
                                ->required()
                                ->maxLength(50)
                                ->disabled() // Desabilitado, pois é preenchido pelo Set
                                ->helperText('Preenchido automaticamente ao selecionar o Set.'),

                            // Nomes e IDs
                            TextInput::make('name') // Este 'name' deve vir do Model Card, não do Functionality
                                ->label('Nome (Conceito - ex: Black Lotus)')
                                ->required()
                                ->maxLength(255)
                                ->helperText('Este nome será usado para criar/vincular a Funcionalidade (Conceito)'),
                            
                            TextInput::make('printed_name')
                                ->label('Nome Impresso (Se diferente ou traduzido)')
                                ->maxLength(255),
                            
                            TextInput::make('alter_ego')
                                ->label('Alter Ego (ex: Tony Stark)')
                                ->maxLength(150),
                            
                            TextInput::make('language_code')
                                ->label('Idioma')
                                ->default('pt')
                                ->required()
                                ->maxLength(5),
                        ])->columns(2)->columnSpan(2), // Fim da Coluna 1 (Larga)

                    // --- COLUNA 3 (Estreita) ---
                    Section::make('Imagem e Coleção (Manual)')
                        ->schema([
                            FileUpload::make('custom_image_path')
                                ->label('Upload da Imagem (Fanmade)')
                                ->disk('public')
                                ->directory('card_images/Custom') 
                                ->image()
                                ->helperText('Use esta opção para sets Fanmade.'),
                            
                            TextInput::make('collection_number')
                                ->label('Nº de Colecionador')
                                ->maxLength(20),
                            TextInput::make('rarity')
                                ->label('Raridade')
                                ->maxLength(50),
                        ])->columnSpan(1), // Fim da Coluna 2 (Estreita)

                    // --- TEXTOS (Largura Total) ---
                    Section::make('Textos e Tipos')
                        ->schema([
                            Grid::make(3)->schema([
                                TextInput::make('type_main')
                                    ->label('Tipo Principal (ex: Personagem, Feitiço)')
                                    ->maxLength(50),
                                TextInput::make('type_sub')
                                    ->label('Sub-tipo (ex: Vingadores, Guerreiro)')
                                    ->maxLength(255),
                                TextInput::make('card_cost')
                                    ->label('Custo (Genérico)')
                                    ->maxLength(50),
                            ]),
                            Textarea::make('rules_text') // Este deve ser o 'printed_text'
                                ->label('Texto de Regras (Traduzido)')
                                ->rows(5),
                            Textarea::make('flavor_text')
                                ->label('Texto de Ambientação (Flavor)')
                                ->rows(3),
                        ])->columnSpanFull(),

                    // --- ESTATÍSTICAS GENÉRICAS (Largura Total) ---
                    Section::make('Estatísticas de Jogo (Genéricas)')
                        ->schema([
                            TextInput::make('stat_attack')
                                ->label('Ataque/Poder')
                                ->maxLength(20),
                            TextInput::make('stat_defense')
                                ->label('Defesa/Escudo/Toughness')
                                ->maxLength(20),
                            TextInput::make('stat_life_hp')
                                ->label('Vida/HP')
                                ->maxLength(20),
                            TextInput::make('stat_level_link_pitch')
                                ->label('Nível/Pitch/Link/Lore')
                                ->maxLength(20),
                        ])->columns(4)->columnSpanFull(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('custom_image_path') 
                    ->label('Imagem')
                    ->disk('public') 
                    ->defaultImageUrl(fn (Card $record): string => 
                        $record->local_image_path_large 
                        ? asset($record->local_image_path_large) 
                        : 'https://placehold.co/100x140/222/FFF?text=No+Image'
                    ),
                
                Tables\Columns\TextColumn::make('cardFunctionality.name') 
                    ->label('Nome (Conceito)')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('printed_name')
                    ->label('Nome (Impresso)')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), 

                Tables\Columns\TextColumn::make('tcg_name')
                    ->label('Jogo')
                    ->badge()
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('set.name')
                    ->label('Set')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tcg_name')
                    ->options(Game::pluck('name', 'name')) 
                    ->label('Filtrar por Jogo'),
            ])
            ->actions([
                ViewAction::make()->url(fn (Card $record): string => 
                    // Redireciona para o ViewCardFunctionality (o View detalhado)
                    // Proteção para caso o card_functionality_id ainda não exista (raro)
                    $record->card_functionality_id 
                        ? CardFunctionalityResource::getUrl('view', ['record' => $record->card_functionality_id])
                        : static::getUrl('edit', ['record' => $record]) // Manda para o edit se não tiver conceito
                ),
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
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCards::route('/'),
            'create' => Pages\CreateCard::route('/create'),
            'edit' => Pages\EditCard::route('/{record}/edit'),
        ];
    }    
}

