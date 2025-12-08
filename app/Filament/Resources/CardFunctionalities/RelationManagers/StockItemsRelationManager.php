<?php

namespace App\Filament\Resources\CardFunctionalities\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Card;
use Filament\Schemas\Schema; 
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid; // Importante para o layout
use Filament\Forms\Components\Section; // Importante para o visual
use Filament\Forms\Components\Hidden;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;

class StockItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockItems';

    // Ícone da aba na visualização da funcionalidade
    // CORREÇÃO: O tipo deve corresponder exatamente à classe pai (string | BackedEnum | null)
    protected static string | \BackedEnum | null $icon = 'heroicon-o-currency-dollar';

    protected static ?string $title = 'Meu Estoque deste Card';

    /**
     * Intercepta a criação para injetar o ID da Loja.
     * IMPORTANTE: No futuro, isso virá de auth()->user()->store_id
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // TODO: Trocar '1' pelo ID da loja do usuário logado quando tivermos Auth de Lojista
        $data['store_id'] = 1; 
        return $data;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Adicionar ao Estoque')
                    ->description('Defina as condições e o preço para venda.')
                    ->schema([
                        
                        // LINHA 1: Seleção do Card (O mais importante)
                        Select::make('card_id')
                            ->label('Selecione a Edição / Idioma')
                            ->options(function ($livewire) {
                                // Busca apenas os prints desta funcionalidade (ex: Todos os Shivan Dragon)
                                return Card::where('card_functionality_id', $livewire->ownerRecord->id)
                                    ->with('set') 
                                    ->orderBy('mtg_released_at', 'desc') // Mais recentes primeiro
                                    ->get()
                                    ->mapWithKeys(function ($card) {
                                        // Exibe: "Kamigawa: Neon Dynasty (PT) - #302"
                                        $label = "{$card->set->name} ({$card->languageCode}) - #{$card->collection_number}";
                                        return [$card->id => $label];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->reactive() // Reativo para liberar o Foil
                            ->columnSpanFull(), // Ocupa a largura toda

                        // LINHA 2: Preço e Quantidade (Foco comercial)
                        Grid::make(2)->schema([
                            TextInput::make('price')
                                ->label('Preço Unitário')
                                ->numeric()
                                ->prefix('R$')
                                ->placeholder('0,00')
                                ->required(),

                            TextInput::make('quantity')
                                ->label('Quantidade')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),
                        ]),

                        // LINHA 3: Detalhes Técnicos (Qualidade e Extras)
                        Grid::make(3)->schema([
                            Select::make('condition')
                                ->label('Qualidade')
                                ->options([
                                    'NM' => 'Near Mint (NM)',
                                    'SP' => 'Slightly Played (SP)',
                                    'MP' => 'Moderately Played (MP)',
                                    'HP' => 'Heavily Played (HP)',
                                    'D'  => 'Damaged (D)',
                                ])
                                ->default('NM')
                                ->required()
                                ->native(false),

                            Select::make('language')
                                ->label('Idioma (Override)')
                                ->options([
                                    'pt' => 'Português',
                                    'en' => 'Inglês',
                                    'ja' => 'Japonês',
                                    'cn' => 'Chinês',
                                    'it' => 'Italiano',
                                    'fr' => 'Francês',
                                    'de' => 'Alemão',
                                    'es' => 'Espanhol',
                                ])
                                ->helperText('Selecione se quiser forçar um idioma diferente do cadastro.'),

                            // Toggle Inteligente de Foil
                            Toggle::make('is_foil')
                                ->label('É Foil?')
                                ->inline(false)
                                ->hidden(function (callable $get, $livewire) {
                                    $cardId = $get('card_id');
                                    if (!$cardId) return true;
                                    
                                    $card = Card::find($cardId);
                                    if (!$card) return true;

                                    $tcgName = $livewire->ownerRecord->tcg_name;

                                    // Regra de Negócio: Só mostra Foil se a carta puder ser Foil
                                    return match ($tcgName) {
                                        'Magic: The Gathering' => !$card->mtg_has_foil, // Se NÃO tem foil, esconde
                                        'Star Wars: Unlimited' => !$card->swu_foil,
                                        default => false, // Mostra sempre para outros jogos por padrão
                                    };
                                }),
                        ]),
                        
                        // Campos Ocultos para garantir integridade
                        Hidden::make('is_active')->default(true),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->heading('Vendedores / Meu Estoque')
            ->modelLabel('Item')
            ->columns([
                // 1. Imagem do Card (Fundamental para vitrine)
                ImageColumn::make('card.imageUrl') // Usa o Accessor polimórfico que criamos!
                    ->label('Card')
                    ->square()
                    ->height(60),

                // 2. Edição e Nome
                TextColumn::make('card.set.name')
                    ->label('Edição')
                    ->description(fn (StockItem $record) => "#" . $record->card->collection_number)
                    ->sortable(),

                // 3. Idioma (Badge colorida)
                TextColumn::make('card.languageCode')
                    ->label('Lang')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pt' => 'success', // Verde
                        'en' => 'info',    // Azul
                        'ja' => 'danger',  // Vermelho
                        default => 'gray',
                    }),

                // 4. Qualidade (Badge com cores de semáforo)
                TextColumn::make('condition')
                    ->label('Cond.')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'NM' => 'success', // Perfeito
                        'SP' => 'info',    // Bom
                        'MP' => 'warning', // Atenção
                        'HP', 'D' => 'danger', // Cuidado
                        default => 'gray',
                    }),

                // 5. Foil (Ícone brilhante)
                IconColumn::make('is_foil')
                    ->label('Foil')
                    ->boolean()
                    ->trueIcon('heroicon-s-star')
                    ->trueColor('warning') // Dourado
                    ->falseIcon(''), // Não mostra nada se não for foil

                // 6. Quantidade e Preço
                TextColumn::make('quantity')
                    ->label('Qtd.')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),
            ])
            ->filters([
                // Filtro para ver só Foils
                Tables\Filters\Filter::make('is_foil')
                    ->label('Apenas Foil')
                    ->query(fn (Builder $query): Builder => $query->where('is_foil', true)),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Adicionar Estoque')
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}