<?php

namespace App\Filament\Resources\AdminUsers;

use App\Filament\Resources\AdminUsers\Pages;
use App\Filament\Resources\AdminUsers\Pages\CreateUser;
use App\Filament\Resources\SAdminUsers\Pages\EditUser;
use App\Filament\Resources\AdminUsers\Pages\ListUsers;
use App\Models\AdminUser;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdminUserResource extends Resource
{
    protected static ?string $model = AdminUser::class;
    
    public static function form(Schema $schema): Schema
    {
        // ... (Implementação do Formulário - Simples)
        return $schema
            ->schema([
                TextInput::make('name')->required()->maxLength(100),
                TextInput::make('email')->required()->email()->unique(ignoreRecord: true)->maxLength(100),
                TextInput::make('password')->password()->required(fn (string $operation): bool => $operation === 'create')->hiddenOn('edit'),
                Toggle::make('is_active')->label('Ativo')->default(true),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->sortable()->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                IconColumn::make('is_active')->label('Ativo')->boolean()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    // MÉTODOS DE NAVEGAÇÃO
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-briefcase';
    }
    
    public static function getModelLabel(): string
    {
        return 'Staff do Sistema';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Gestão de Clientes e Lojas';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdminUsers::route('/'),
            'create' => Pages\CreateAdminUser::route('/create'),
            'edit' => Pages\EditAdminUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // 1. Obtém a query base
        $query = parent::getEloquentQuery();

        // 2. Exclui o SuperUser logado (que está na tabela 'users') da listagem.
        // Assumimos que o SuperUser logado é o único com acesso ao Admin.
        $superAdminId = auth()->id(); 

        if ($superAdminId) {
            // Exclui o registro com o mesmo ID do SuperUser (para evitar que a tabela 'admin_users'
            // mostre o SuperUser por engano, ou que o SuperUser possa se editar nesta interface).
            $query->where('id', '<>', $superAdminId); 
        }

        return $query;
    }

    public static function canCreate(): bool
    {
        // Apenas o SuperUser logado (que é o único User) pode criar.
        return auth()->user() instanceof \App\Models\User; 
    }
    
    // Sobrescreve as permissões de Delete/Bulk Delete
    public static function canDelete(Model $record): bool
    {
        // NENHUM AdminUser pode se deletar. Apenas o SuperUser pode deletar qualquer um.
        return auth()->user() instanceof \App\Models\User;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user() instanceof \App\Models\User;
    }
}
