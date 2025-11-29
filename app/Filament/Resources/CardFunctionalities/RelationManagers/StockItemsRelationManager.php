<?php

// CORREÇÃO 1: Namespace REVERTIDO para o singular original
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
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;

class StockItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockItems';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // ---- PASSO 1: ESCOLHER A EDIÇÃO (PRINT) ----
                Select::make('card_id')
                    ->label('Edição (Print)')
                    ->options(function ($livewire) {
                        return Card::where('card_functionality_id', $livewire->ownerRecord->id)
                            ->with('set') 
                            ->get()
                            ->mapWithKeys(function ($card) {
                                // CORREÇÃO 2: Lógica mantida (usando o accessor 'languageCode')
                                return [$card->id => "{$card->set->name} ({$card->languageCode})"];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->live(), 

                // ---- PASSO 2: O CAMPO FOIL INTELIGENTE ----
                Toggle::make('is_foil')
                    ->label('Foil')
                    // CORREÇÃO 3: Lógica mantida (TCG-aware)
                    ->hidden(function (callable $get, $livewire) {
                        $cardId = $get('card_id');
                        if (!$cardId) { return true; } 
                        
                        $card = Card::find($cardId);
                        if (!$card) { return true; }

                        $tcgName = $livewire->ownerRecord->tcg_name;

                        return match ($tcgName) {
                            'Magic: The Gathering' => !$card->mtg_has_foil,
                            'Star Wars: Unlimited' => !$card->swu_foil,
                            default => true, 
                        };
                    }),

                // ---- DEMAIS CAMPOS ----
                Select::make('condition')->label('Qualidade')->options(['NM' => 'Near Mint', 'SP' => 'Slightly Played', 'MP' => 'Moderately Played', 'HP' => 'Heavily Played', 'DM' => 'Damaged'])->required(),
                TextInput::make('quantity')->label('Estoque')->numeric()->default(1)->required(),
                TextInput::make('price')->label('Preço')->numeric()->prefix('R$')->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('card.set.name')->label('Edição')->searchable(),
                
                // CORREÇÃO 4: Lógica mantida (usando o accessor)
                TextColumn::make('card.languageCode')->label('Idioma')->badge(),

                TextColumn::make('condition')->label('Qualidade')->badge(),
                TextColumn::make('quantity')->label('Est.')->sortable(),
                TextColumn::make('price')->label('Preço')->money('BRL')->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()->label('Adicionar Novo Item'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}