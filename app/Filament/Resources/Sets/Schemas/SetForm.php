<?php

namespace App\Filament\Resources\Sets\Schemas;

// Namespaces corretos (misturados) que você identificou:
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid; // <-- O 'Schema'
use Filament\Forms\Components\Select; // <-- O 'Forms'
use Filament\Forms\Components\TextInput; // <-- O 'Forms'
use Filament\Forms\Components\Toggle; // <-- O 'Forms'
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model; // Para o ignoreRecord (se estiver usando v2/Schema)

// ***** A CORREÇÃO DO ERRO 'TypeError' ESTÁ AQUI *****
// 1. Removemos o 'Rule' genérico
// 2. Adicionamos o 'Unique' específico que o Filament envia
use Illuminate\Validation\Rules\Unique;
use Closure; // Para a closure

class SetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)->schema([
                    
                    Select::make('game_id')
                        ->relationship('game', 'name')
                        ->required()
                        ->searchable()
                        ->label('Jogo')
                        ->columnSpanFull(),

                    TextInput::make('name')
                        ->required()
                        ->label('Nome Completo do Set')
                        ->maxLength(100)
                        ->columnSpanFull(),

                    TextInput::make('code')
                        ->required()
                        ->label('Código do Set (ex: MID, BS-CAD)')
                        ->maxLength(15)
                        ->unique(
                            table: 'sets',
                            column: 'code',
                            ignoreRecord: true,
                            // ***** A CORREÇÃO DO ERRO 'TypeError' ESTÁ AQUI *****
                            // 3. Mudamos o type-hint da variável de 'Rule' para 'Unique'
                            modifyRuleUsing: function (Unique $rule, callable $get) { 
                                return $rule->where('game_id', $get('game_id'));
                            }
                        ),

                    DatePicker::make('released_at')
                        ->label('Data de Lançamento')
                        ->required(),

                    TextInput::make('scryfall_id')
                        ->nullable()
                        ->label('Scryfall ID (Opcional)')
                        ->default(null),
                    
                    TextInput::make('set_type')
                        ->required()
                        ->label('Tipo (ex: expansion, scraped_set)')
                        ->default('expansion'),

                    TextInput::make('card_count')
                        ->required()
                        ->numeric()
                        ->label('Contagem de Cards')
                        ->default(0),

                    TextInput::make('icon_svg_uri')
                        ->label('URI do Ícone SVG (Opcional)')
                        ->default(null),

                    Toggle::make('digital')
                        ->required()
                        ->label('Set Digital')
                        ->default(false),
                    
                    Toggle::make('foil_only')
                        ->required()
                        ->label('Apenas Foil')
                        ->default(false),
                ])
            ]);
    }
}

