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
use Filament\Schemas\Components\Section;
use Filament\Forms\Get;
use App\Models\Set; // Importamos o Set padrão
use App\Models\Catalog\CatalogPrint; // Importamos o novo Model de Print
use Illuminate\Database\Eloquent\Model;
use Filament\Tables;
use Filament\Forms;


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
        $gameId = (int) ($this->ownerRecord->game_id ?? 0);
        $data['concept_id'] = $this->ownerRecord->id;

        // Associa o Model polimórfico correto baseado no jogo do Concept
        if ($gameId === 1) {
            $specific = \App\Models\Games\Magic\MtgPrint::create();
            $data['specific_type'] = \App\Models\Games\Magic\MtgPrint::class;
            $data['specific_id'] = $specific->id;
        } elseif ($gameId === 2) {
            $specific = \App\Models\Games\Pokemon\PkPrint::create();
            $data['specific_type'] = \App\Models\Games\Pokemon\PkPrint::class;
            $data['specific_id'] = $specific->id;
        } elseif ($gameId === 4) {
            $specific = \App\Models\Games\BattleScenes\BsPrint::create();
            $data['specific_type'] = \App\Models\Games\BattleScenes\BsPrint::class;
            $data['specific_id'] = $specific->id;
        }

        return $data;
    }

    /**
     * CORREÇÃO: Formulário dinâmico (Polimórfico V4)
     */
    public function form(Schema $schema): Schema
    {
        $gameId = (int) ($this->ownerRecord->game_id ?? 0);

        return $schema
            ->schema([
                Forms\Components\Select::make('set_id')
                    ->label('Coleção (Set)')
                    ->relationship('set', 'name', fn ($query) => $query->where('game_id', $gameId))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('image_path')
                    ->label('Imagem da Carta')
                    ->image()
                    ->disk('public_root') // ou configurado para apontar para a pasta public do Laravel
                    ->directory(function (Get $get, $record) {
                        // Identifica o jogo
                        $gameName = match ((int) ($this->ownerRecord->game_id ?? 0)) {
                            1 => 'Magic',
                            2 => 'Pokemon',
                            4 => 'BattleScenes',
                            default => 'Outros',
                        };

                        // Identifica o código do set (ex: BSAQ, 2XM, etc.)
                        $setId = $get('set_id') ?? $record?->set_id;
                        $setCode = \App\Models\Set::find($setId)?->code ?? 'Geral';

                        // Identifica o idioma (pt, en, etc.)
                        $lang = $get('language_code') ?? $get('specific.language_code') ?? $record?->language_code ?? 'pt';

                        return "card_imagem/{$gameName}/{$setCode}/{$lang}";
                    })
                    ->columnSpanFull(),

                // Magic: The Gathering (ID 1)
                Section::make('Detalhes da Impressão (Magic)')
                    ->relationship('specific')
                    ->visible(fn () => $gameId === 1)
                    ->schema([
                        TextInput::make('printed_name')->label('Nome Impresso (em Português / Idioma do Card)'),
                        TextInput::make('language_code')->label('Código do Idioma')->default('pt')->required(),
                        TextInput::make('collector_number')->label('Nº da Coleção')->required(),
                        TextInput::make('rarity')->label('Raridade')->required(),
                        TextInput::make('artist')->label('Artista'),
                    ]),

                // Pokémon TCG (ID 2)
                Section::make('Detalhes da Impressão (Pokémon)')
                    ->relationship('specific')
                    ->visible(fn () => $gameId === 2)
                    ->schema([
                        TextInput::make('number')->label('Nº da Coleção')->required(),
                        TextInput::make('rarity')->label('Raridade')->required(),
                        TextInput::make('artist')->label('Artista'),
                        TextInput::make('language_code')->label('Código do Idioma')->default('pt')->required(),
                    ]),

                // Battle Scenes (ID 4)
                Section::make('Detalhes da Impressão (Battle Scenes)')
                    ->relationship('specific')
                    ->visible(fn () => $gameId === 4)
                    ->schema([
                        TextInput::make('number')->label('Nº da Coleção'),
                        TextInput::make('rarity')->label('Raridade')->required(),
                        TextInput::make('artist')->label('Artista'),
                        TextInput::make('language_code')->label('Código do Idioma')->default('pt')->required(),
                    ]),
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
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}