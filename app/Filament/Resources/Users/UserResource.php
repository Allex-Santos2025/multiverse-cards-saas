<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;

// Componentes de Formulário 
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;

// Colunas, Filtros e Ações
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // Métodos auxiliares que definem a UX do menu (Reintroduzidos)
    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-users'; // Ícone base de usuário
    }
    
    public static function getModelLabel(): string
    {
        return 'Administrador do Sistema';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Configurações de Plataforma';
    }
    
    // MÉTODO CRÍTICO: Desliga a checagem de permissões do Spatie
    public static function shouldRegisterNavigation(): bool
    {
        // Se o usuário está logado na tabela 'users', ele é o SuperAdmin e tem acesso.
        return auth()->check(); 
    }
    
    public static function form(Schema $schema): Schema 
    {
        return $schema
            ->schema([
                // ----------------------------------------------------
                // 1. DADOS BÁSICOS
                // ----------------------------------------------------
                TextInput::make('name')
                    ->label('Nome Completo')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),

                // ----------------------------------------------------
                // 2. SEGURANÇA (Senha)
                // ----------------------------------------------------
                TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                    ->dehydrated(fn (string $state): bool => filled($state))
                    ->maxLength(255)
                    ->confirmed()
                    ->columnSpanFull(), 

                TextInput::make('password_confirmation')
                    ->label('Confirmar Senha')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(false)
                    ->maxLength(255)
                    ->columnSpanFull(),
                    
                // REMOVEMOS: Select::make('roles') (Não usamos mais Spatie)
                // REMOVEMOS: Select::make('store_id') (Não é um StoreUser)

                // ----------------------------------------------------
                // 3. STATUS
                // ----------------------------------------------------
                Toggle::make('is_protected')
                    ->label('Usuário de Proteção (Root Supremo)')
                    ->helperText('Se ativo, este usuário não pode ser excluído.')
                    ->disabled() 
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('email')->label('Email')->searchable(),
                // REMOVEMOS: TextColumn::make('roles.name') (Sem Spatie)
                // REMOVEMOS: TextColumn::make('store.name') (Sem StoreUser)
                
                IconColumn::make('is_protected')->label('Protegido')->tooltip('Usuário que não pode ser excluído do sistema.')->boolean(),
            ])
            ->filters([
                // REMOVEMOS: Filtros baseados em Roles (Spatie)
                TernaryFilter::make('is_protected')->label('Apenas Root Supremo')->indicator('Root Supremo'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()->visible(fn (User $record): bool => !$record->is_protected),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->before(function (DeleteBulkAction $action, \Illuminate\Support\Collection $records) {
                        if ($records->contains('is_protected', true)) {
                            \Filament\Notifications\Notification::make()->title('Ação Bloqueada')->body('Não é possível excluir um Usuário Root Supremo.')->danger()->send();
                            $action->cancel();
                        }
                    }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}