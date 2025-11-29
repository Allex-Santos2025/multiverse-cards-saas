<?php

namespace App\Filament\Resources\PlayerUsers; // <-- Namespace correto

use App\Filament\Resources\PlayerUsers\Pages;
// Removidas as referências de 'CreateUser', 'EditUser', 'ListUsers' que não são padrão
use App\Models\PlayerUser;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms; // Importação principal do Forms
use Filament\Schemas\Schema; // Importação principal do Schema
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables; // Importação principal do Tables
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder; // Importado para o relationship

// ***** CORREÇÃO: O nome da classe deve ser PlayerUserResource *****
class PlayerUserResource extends Resource 
{
    protected static ?string $model = PlayerUser::class;
    
    // ***** CORREÇÃO: Funções de navegação do seu arquivo original *****
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-users'; // Ícone correto para Jogadores
    }
    
    public static function getModelLabel(): string
    {
        return 'Jogador';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Jogadores';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Gestão de Clientes e Lojas'; // O grupo que você quer criar
    }
    // ***** FIM DA CORREÇÃO *****

    public static function form(Schema $schema): Schema // <--- A sintaxe correta
    {
        return $schema
            ->schema([
                // DADOS BÁSICOS
                TextInput::make('name')->label('Nome')->required()->maxLength(100),
                TextInput::make('surname')->label('Sobrenome')->required()->maxLength(100),
                TextInput::make('login')->label('Login')->required()->unique(ignoreRecord: true)->maxLength(100),
                TextInput::make('email')->label('Email')->required()->email()->unique(ignoreRecord: true)->maxLength(100),
                
                // SEGURANÇA E AUTENTICAÇÃO
                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->hiddenOn('edit'),
                
                // DOCUMENTOS (Adicionados por SQL)
                TextInput::make('document_number')->label('CPF/CNPJ')->unique(ignoreRecord: true)->maxLength(20)->nullable(),
                TextInput::make('id_document_number')->label('RG/ID')->unique(ignoreRecord: true)->maxLength(20)->nullable(),
                
                // DADOS PESSOAIS E FIDELIDADE
                TextInput::make('phone_number')->label('Telefone')->maxLength(20)->nullable(),
                DatePicker::make('birth_date')->label('Data de Nascimento')->nullable(),
                TextInput::make('loyalty_points')->label('Pontos de Fidelidade')->numeric()->default(0),
                
                // STATUS
                Toggle::make('is_active')->label('Ativo')->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->sortable()->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('loyalty_points')->label('Pontos')->sortable(),
                TextColumn::make('document_number')->label('CPF/CNPJ')->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')->label('Ativo')->boolean()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlayerUsers::route('/'),
            'create' => Pages\CreatePlayerUser::route('/create'),
            'edit' => Pages\EditPlayerUser::route('/{record}/edit'),
        ];
    }
}

