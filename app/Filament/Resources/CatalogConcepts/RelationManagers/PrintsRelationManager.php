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
use Filament\Forms\Get;
use App\Models\Set; // Importamos o Set padrão
use App\Models\Catalog\CatalogPrint; // Importamos o novo Model de Print
use Illuminate\Database\Eloquent\Model;

class PrintsRelationManager extends RelationManager
{
    // Relação no Model CatalogConcept (o Pai)
    protected static string $relationship = 'prints'; 
    protected static ?string $title = 'Impressões e Edições';

    /**
     * CORREÇÃO: Mutate para o novo CatalogPrint (só deve preencher o game_id)
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // O campo 'game_id' está no Model CatalogPrint, e o nome do TCG está no Model Game.
        // O ownerRecord é o CatalogConcept.
        $data['game_id'] = $this->ownerRecord->game_id; 
        
        // Se o seu CatalogPrint tiver campos diretos (como language_code), eles devem ser tratados aqui.
        
        return $data;
    }

    /**
     * CORREÇÃO: Formulário dinâmico (Polimórfico V4)
     */
    public function form(Schema $schema): Schema
    {
        // Pega o nome do TCG do Conceito Pai
        $tcgName = $this->ownerRecord->game->name ?? 'N/A';
        $gameId = $this->ownerRecord->game_id;

        return $schema
            ->schema([
                // Campo Set ID (Obrigatório)
                Forms\Components\Select::make('set_id')
                    ->label('Coleção (Set)')
                    ->relationship('set', 'name')
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                // --- DADOS ESPECÍFICOS: PK Print / MTG Print ---
                // Aplicamos a lógica dinâmica diretamente na relação 'specific'
                
                // Magic: The Gathering (ID 1)
                Section::make('Detalhes Impressão Magic')
                    ->relationship('specific') // Edita mtg_prints
                    ->visible(fn () => $gameId === 1)
                    ->schema([
                        TextInput::make('mtg_printed_name')->label('Nome Impresso'),
                        TextInput::make('mtg_language_code')->label('Idioma (ex: en)')->required()->default('en'),
                        TextInput::make('mtg_rarity')->label('Raridade')->required(),
                        TextInput::make('mtg_collection_number')->label('Nº Coleção')->required(),
                        TextInput::make('mtg_artist')->label('Artista'),
                    ]),

                // Pokémon TCG (ID 2)
                Section::make('Detalhes Impressão Pokémon')
                    ->relationship('specific') // Edita pk_prints
                    ->visible(fn () => $gameId === 2)
                    ->schema([
                        TextInput::make('number')->label('Nº Coleção')->required(),
                        TextInput::make('rarity')->label('Raridade')->required(),
                        TextInput::make('artist')->label('Artista'),
                        TextInput::make('language_code')->label('Idioma (ex: en)')->required()->default('en'),
                    ]),
                
                // TODO: Adicionar outros 6 TCGs aqui...
            ]);
    }

    /**
     * Tabela de Impressões (Prints)
     */
    public function table(Table $table): Table
    {
        return $table
            // Carregamos a coluna de imagem e os dados específicos
            ->recordTitleAttribute('specific.number') 
            ->columns([
                // Imagem (Vindo do caminho local da CatalogPrint)
                ImageColumn::make('image_path')
                    ->label('Arte')
                    ->height(80)
                    ->checkFileExistence(false)
                    ->square(),

                // Nome Impresso (Vindo do campo 'printed_name' do CatalogPrint)
                TextColumn::make('printed_name')
                    ->label('Nome Impresso')
                    ->searchable()
                    ->sortable()
                    ->default(fn (CatalogPrint $record) => $record->concept->name ?? 'N/A')
                    ->toggleable(),
                
                // Coleção
                TextColumn::make('set.name')
                    ->label('Coleção')
                    ->searchable()
                    ->sortable(),

                // Número (Polimórfico - do PkPrint/MtgPrint)
                TextColumn::make('specific.number')
                    ->label('Nº Coleção'),
                
                // Raridade (Polimórfico)
                TextColumn::make('specific.rarity')
                    ->label('Raridade')
                    ->badge()
                    ->sortable(),

                // Idioma (Polimórfico)
                TextColumn::make('specific.language_code')
                    ->label('Idioma')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}