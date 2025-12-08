<?php

namespace App\Filament\Resources\CatalogConcepts\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use App\Models\Catalog\CatalogPrint; // Usamos o novo Print
use App\Models\StockItem; // O Model que gerencia o estoque
use App\Models\Card; // Se necessário
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StockItemsRelationManager extends RelationManager
{
    // Relação no Model CatalogConcept (o Pai)
    protected static string $relationship = 'stockItems'; 

    protected static string | \BackedEnum | null $icon = 'heroicon-o-currency-dollar';
    protected static ?string $title = 'Meu Estoque deste Conceito';

    /**
     * Intercepta a criação para injetar o ID da Loja.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // O StockItem é ligado à Loja e ao Print (catalog_print_id)
        $data['store_id'] = 1; // TODO: Trocar '1' pelo ID da loja do usuário logado
        return $data;
    }

    public function form(Schema $schema): Schema
    {
        $tcgName = $this->ownerRecord->game->name ?? 'N/A';

        return $schema
            ->schema([
                Section::make('Adicionar ao Estoque')
                    ->description('Defina as condições e o preço para venda.')
                    ->schema([
                        // LINHA 1: Seleção do Print (O mais importante)
                        Forms\Components\Select::make('catalog_print_id') // Mudança do card_id
                            ->label('Selecione a Edição / Idioma')
                            ->options(function ($livewire) {
                                // Busca apenas os Prints deste Conceito Pai
                                return $livewire->ownerRecord->prints()
                                    ->with(['set', 'specific'])
                                    ->orderBy('set_id', 'desc') 
                                    ->get()
                                    ->mapWithKeys(function (CatalogPrint $print) {
                                        $set = $print->set->name ?? 'Set N/A';
                                        $lang = $print->specific->language_code ?? 'en';
                                        $num = $print->specific->number ?? '?';
                                        
                                        // Exibe: "Set Name (EN) - #123"
                                        $label = "{$set} ({$lang}) - #{$num}";
                                        return [$print->id => $label];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->reactive() 
                            ->columnSpanFull(),

                        // LINHA 2: Preço e Quantidade
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
                                ->options([ /* Suas opções de idioma aqui */ ]) // Usar Options do seu sistema
                                ->helperText('Selecione se quiser forçar um idioma diferente do cadastro.'),

                            // Toggle Inteligente de Foil (Oculto se o Print não tiver foil_only no Set)
                            Toggle::make('is_foil')
                                ->label('É Foil?')
                                ->inline(false)
                                ->hidden(function (callable $get) {
                                    $printId = $get('catalog_print_id');
                                    if (!$printId) return true;
                                    
                                    $print = CatalogPrint::with('set')->find($printId);
                                    
                                    // Regra de Negócio: Se o SET não permite Foil, esconde.
                                    // Assumimos que o campo 'foil_only' está na tabela 'sets'
                                    return !$print->set->foil_only; 
                                }),
                        ]),
                        
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
                // 1. Imagem do Card (Acesso ao Print Relacionado)
                ImageColumn::make('catalogPrint.image_path')
                    ->label('Card')
                    ->height(60)
                    ->square(),

                // 2. Edição e Nome (Acesso ao Print e Set)
                TextColumn::make('catalogPrint.set.name')
                    ->label('Edição')
                    ->description(fn (StockItem $record) => "#" . $record->catalogPrint->specific->number ?? '?')
                    ->sortable(),

                // 3. Idioma (Acesso ao Specific)
                TextColumn::make('catalogPrint.specific.language_code')
                    ->label('Lang')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pt' => 'success', 'en' => 'info', 'ja' => 'danger', default => 'gray',
                    }),

                // 4. Qualidade
                TextColumn::make('condition')
                    ->label('Cond.')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'NM' => 'success', 'SP' => 'info', 'MP' => 'warning', 'HP', 'D' => 'danger', default => 'gray',
                    }),

                // 5. Foil
                Tables\Columns\IconColumn::make('is_foil')
                    ->label('Foil')
                    ->boolean()
                    ->trueIcon('heroicon-s-star')
                    ->trueColor('warning') 
                    ->falseIcon(''),

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
                Tables\Filters\Filter::make('is_foil')
                    ->label('Apenas Foil')
                    ->query(fn (Builder $query): Builder => $query->where('is_foil', true)),
            ])
            ->headerActions([
                CreateAction::make()->label('Adicionar Estoque'),
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