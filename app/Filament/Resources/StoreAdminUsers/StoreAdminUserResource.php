<?php

namespace App\Filament\Resources\StoreAdminUsers;

use App\Filament\Resources\StoreAdminUsers\Pages;
use App\Filament\Resources\StoreAdminUsers\Pages\CreateUser;
use App\Filament\Resources\StoreAdminUsers\Pages\EditUser;
use App\Filament\Resources\StoreAdminUsers\Pages\ListUsers;
use App\Models\StoreAdminUser;
use App\Models\Store;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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


class StoreAdminUserResource extends Resource
{
    protected static ?string $model = StoreAdminUser::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                // VINCULO CRUCIAL COM A LOJA (FK)
                Select::make('store_id')
                    ->label('Loja Vinculada')
                    ->options(Store::all()->pluck('name', 'id'))
                    ->nullable() // Permite NULL (status "à deriva" / demitido)
                    ->helperText('Obrigatório se este funcionário está ativo.'),

                // DADOS BÁSICOS
                TextInput::make('name')->label('Nome')->required()->maxLength(100),
                TextInput::make('surname')->label('Sobrenome')->required()->maxLength(100),
                TextInput::make('login')->label('Login')->required()->unique(ignoreRecord: true)->maxLength(100),
                TextInput::make('email')->label('Email')->required()->email()->unique(ignoreRecord: true)->maxLength(100),
                
                // SEGURANÇA
                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->hiddenOn('edit'),
                
                // GESTÃO INTERNA
                TextInput::make('permissions_json')->label('Permissões JSON')->helperText('Ex: {"can_edit_prices": true}')->nullable(),
                DatePicker::make('hired_date')->label('Data de Contratação')->nullable(),

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
                TextColumn::make('store.name')->label('Loja')->sortable(),
                TextColumn::make('hired_date')->label('Contratação')->date()->sortable(),
                IconColumn::make('is_active')->label('Ativo')->boolean()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
            \Filament\Actions\DeleteBulkAction::make()
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
        return 'Staff da Loja';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Gestão de Clientes e Lojas';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoreAdminUsers::route('/'),
            'create' => Pages\CreateStoreAdminUser::route('/create'),
            'edit' => Pages\EditStoreAdminUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user(); // O usuário logado

        // Regra: Se o usuário logado NÃO é um SuperAdmin/Admin do Sistema
        if ($user instanceof \App\Models\StoreUser || $user instanceof \App\Models\StoreAdminUser) {
            
            // 1a. Obtém a Store ID à qual o usuário está vinculado.
            //     (StoreUser usa current_store_id; StoreAdminUser usa store_id)
            $storeId = $user->store_id ?? $user->current_store_id;

            // 1b. Aplica o filtro de segurança CRÍTICO:
            if ($storeId) {
                // O Lojista/Staff SÓ PODE ver registros de StoreAdminUsers que pertencem à sua própria Loja.
                $query->where('store_id', $storeId); 
            } else {
                // Se o usuário logado não tem loja vinculada, ele não deve ver nenhum funcionário.
                $query->whereRaw('1 = 0'); 
            }
        }
        
        // SuperAdmin e AdminSystem veem tudo.
        return $query;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        // 1. Permissão Negada para o PRÓPRIO Funcionário da Loja (StoreAdminUser)
        if ($user instanceof \App\Models\StoreAdminUser) {
            return false; // Um Staff da Loja NÃO PODE criar outro Staff.
        }

        // 2. Permissão para Lojista (StoreUser):
        if ($user instanceof \App\Models\StoreUser) {
            // Um lojista SÓ pode criar se estiver vinculado a uma loja.
            return (bool)($user->store_id ?? $user->current_store_id); 
        }

        // 3. Permissão para SuperUser (Model User) e AdminUser (Staff do Sistema):
        // Se o usuário não se encaixa nas regras acima, ele é um usuário de nível superior.
        return true;
    }
}