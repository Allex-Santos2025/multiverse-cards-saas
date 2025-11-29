<?php

namespace App\Filament\Resources\StoreUsers; // Ajuste o namespace se necessário

use App\Filament\Resources\StoreUsers\Pages;
use App\Filament\Resources\StoreUsers\Pages\CreateUser;
use App\Filament\Resources\StoreUsers\Pages\EditUser;
use App\Filament\Resources\StoreUsers\Pages\ListUsers;
use App\Models\StoreUser;
use App\Models\Store; // NOVO: Para o Select de Loja
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema; // MANTIDO: O que o seu form usa
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;


class StoreUserResource extends Resource
{
    protected static ?string $model = StoreUser::class;
        
    public static function form(Schema $schema): Schema // <--- A sintaxe correta
    {
        return $schema
            ->schema([
                // VINCULO CRUCIAL COM A LOJA (FK)
                Select::make('store_id')
                    ->label('Loja Vinculada')
                    ->options(Store::all()->pluck('name', 'id'))
                    ->nullable() // Permite NULL, para transferência de loja
                    ->helperText('Obrigatório se este usuário é um Lojista ativo.'),
                
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
                
                // DADOS DO LOJISTA (NEGÓCIO)
                TextInput::make('document_number')->label('CPF/CNPJ')->unique(ignoreRecord: true)->maxLength(20)->nullable(),
                TextInput::make('phone_number')->label('Telefone')->maxLength(20)->nullable(),
                                
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
                // Relacionamento com a Loja
                TextColumn::make('store.name')->label('Loja')->sortable(),
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
            'index' => Pages\ListStoreUsers::route('/'),
            'create' => Pages\CreateStoreUser::route('/create'),
            'edit' => Pages\EditStoreUser::route('/{record}/edit'),
        ];
    }
    
    // MÉTODOS AUXILIARES (Para o menu aparecer)
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-building-storefront';
    }
    
    public static function getModelLabel(): string
    {
        return 'Lojista';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Gestão de Clientes e Lojas';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user(); // O usuário logado

        // Regra 1: Se o usuário logado NÃO é um SuperAdmin/Admin do Sistema
        if ($user instanceof \App\Models\StoreUser || $user instanceof \App\Models\StoreAdminUser) {
            
            // 1a. Obtém a Store ID à qual o usuário está vinculado no momento.
            //     (StoreUser usa current_store_id; StoreAdminUser usa store_id)
            $storeId = $user->store_id ?? $user->current_store_id;

            // 1b. Aplica o filtro de segurança CRÍTICO:
            if ($storeId) {
                // O Lojista/Staff SÓ PODE ver registros de StoreUsers que pertencem à sua própria Loja.
                $query->where('store_id', $storeId); 
            } else {
                // Se o usuário logado não está vinculado a nenhuma loja, ele não deve ver nada.
                $query->whereRaw('1 = 0'); // Query que sempre retorna falso
            }
        }
        
        // Se o usuário for um SuperUser (ou AdminUser, que é Staff do Sistema), ele verá tudo (query sem filtro)
        return $query;
    }
}

