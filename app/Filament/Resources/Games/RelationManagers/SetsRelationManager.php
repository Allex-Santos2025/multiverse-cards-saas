<?php

    namespace App\Filament\Resources\Games\RelationManagers;

    use App\Filament\Resources\Sets\SetResource;
    use Filament\Actions\AssociateAction;
    use Filament\Actions\BulkActionGroup;
    use Filament\Actions\CreateAction;
    use Filament\Actions\DeleteAction;
    use Filament\Actions\DeleteBulkAction;
    use Filament\Actions\DissociateAction;
    use Filament\Actions\DissociateBulkAction;
    use Filament\Actions\EditAction;
    use Filament\Actions\Action;
    use Filament\Forms\Components\TextInput;
    use Filament\Resources\RelationManagers\RelationManager;
    use Filament\Schemas\Schema;
    use Filament\Tables\Columns\TextColumn;
    use App\Models\Set;
    use Filament\Tables\Table;

    class SetsRelationManager extends RelationManager
    {
        protected static string $relationship = 'sets';

        public function form(Schema $schema): Schema
        {
            return $schema
                ->components([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                ]);
        }
    
        public function table(Table $table): Table
        {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Nome da Coleção')->searchable(),
                TextColumn::make('code')->label('Código')->searchable(),
                TextColumn::make('release_date')->label('Lançamento')->date()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(), // Descomente se quiser um botão "Criar Set" aqui
            ])
            ->actions([
                Action::make('view_cards')
                    ->label('Ver Cards')
                    // CORREÇÃO: MUDAMOS DE 'view' PARA 'edit' (ou 'index')
                    ->url(fn (Set $record): string => SetResource::getUrl('edit', ['record' => $record])), 
            ]);
        }
    }
