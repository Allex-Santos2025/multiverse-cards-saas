<?php

// CORREÇÃO 1: Namespace corrigido para o plural 'CardFunctionalities'
namespace App\Filament\Resources\CardFunctionalities\RelationManagers;

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

// Imports Adicionados para o formulário dinâmico
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Set as CardSet; // Renomeado para evitar conflito com Set (do form)

class CardsRelationManager extends RelationManager
{
    protected static string $relationship = 'cards';

    /**
     * Adiciona o tcg_name do "pai" (CardFunctionality)
     * automaticamente antes de criar o "filho" (Card).
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['tcg_name'] = $this->ownerRecord->tcg_name;
        return $data;
    }

    /**
     * CORREÇÃO 2: Formulário dinâmico
     */
    public function form(Schema $schema): Schema
    {
        $tcgName = $this->ownerRecord->tcg_name;

        return $schema
            ->schema([
                Forms\Components\Select::make('set_id')
                    ->label('Coleção (Set)')
                    ->relationship('set', 'name') // Usa a Relação
                    ->searchable()
                    ->required(),

                // --- Seção Dinâmica de Magic ---
                Section::make('Detalhes da Impressão (Magic)')
                    ->visible(fn () => $tcgName === 'Magic: The Gathering')
                    ->schema([
                        TextInput::make('mtg_printed_name')->label('Nome Impresso'),
                        TextInput::make('mtg_language_code')->label('Idioma (ex: pt)')->required()->default('pt'),
                        TextInput::make('mtg_rarity')->label('Raridade')->required(),
                        TextInput::make('mtg_collection_number')->label('Nº Coleção')->required(),
                        TextInput::make('mtg_artist')->label('Artista'),
                    ]),

                // --- Seção Dinâmica de Battle Scenes ---
                Section::make('Detalhes da Impressão (Battle Scenes)')
                    ->visible(fn () => $tcgName === 'Battle Scenes')
                    ->schema([
                        TextInput::make('bs_language_code')->label('Idioma (ex: pt)')->required()->default('pt'),
                        TextInput::make('bs_rarity')->label('Raridade')->required(),
                        TextInput::make('bs_collection_number')->label('Nº Coleção')->required(),
                        TextInput::make('bs_artist')->label('Artista'),
                    ]),
                
                // ... Adicionar seções para os outros 6 TCGs quando necessário ...
            ]);
    }

    /**
     * CORREÇÃO 3: Tabela (removido recordTitleAttribute)
     */
    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('name') // CORREÇÃO: Removido, pois 'name' não existe no Model 'Card'
            ->columns([
                // Esta coluna agora usa o accessor 'image_url' (Arquivo 2)
                ImageColumn::make('image_url')
                    ->label('Arte')
                    ->height(80)
                    ->square(),

                // Esta coluna usa a relação 'set' (Arquivo 2)
                TextColumn::make('set.name')
                    ->label('Coleção')
                    ->searchable()
                    ->sortable(),

                // Esta coluna agora usa o accessor 'rarity' (Arquivo 2)
                TextColumn::make('rarity')
                    ->label('Raridade')
                    ->badge()
                    ->color(fn (string $state): string => match (strtolower($state)) {
                        'common' => 'gray',
                        'uncommon' => 'info',
                        'rare' => 'warning',
                        'mythic' => 'danger',
                        default => 'primary',
                    })
                    ->sortable(),

                // Esta coluna agora usa o accessor 'collection_number' (Arquivo 2)
                TextColumn::make('collection_number')
                    ->label('Nº'),
                
                // Esta coluna agora usa o accessor 'languageCode' (Arquivo 2)
                TextColumn::make('languageCode')
                    ->label('Idioma')
                    ->badge(),
            ]);
    }
}