<?php

namespace App\Filament\Resources\Changelogs;

use App\Filament\Resources\Changelogs\Pages\CreateChangelog;
use App\Filament\Resources\Changelogs\Pages\EditChangelog;
use App\Filament\Resources\Changelogs\Pages\ListChangelogs;
use App\Models\Changelog;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Forms; // Importação principal do Forms
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Schemas\Schema;
use Filament\Schemas\Components;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\DateTimePicker;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use BackedEnum;

class ChangelogResource extends Resource
{
    protected static ?string $model = Changelog::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationLabel = 'Novidades (Changelog)';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Components\Section::make('Identificação da Novidade')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->label('Título')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                    TextInput::make('slug')
                        ->label('Slug (Automático)')
                        ->disabled()
                        ->dehydrated()
                        ->required(),

                    Select::make('category')
                        ->label('Categoria')
                        ->options([
                            'Recurso' => 'Recurso (Estrela)',
                            'Melhoria' => 'Melhoria (Foguete)',
                            'Correção' => 'Correção (Chave)',
                        ])
                        ->required(),

                    TextInput::make('version')
                        ->label('Versão')
                        ->placeholder('v0.1.4')
                        ->required(),
                ]),

            Section::make('Conteúdo')
                ->schema([
                    TextInput::make('summary')
                        ->label('Resumo Curto (Sininho)')
                        ->helperText('O que o lojista lerá primeiro no dashboard.')
                        ->required()
                        ->maxLength(255),

                    MarkdownEditor::make('content')
                        ->label('Texto Completo (Markdown)')
                        ->required()
                        ->columnSpanFull(),
                ]),

            Components\Section::make('Status de Publicação')
                ->columns(2)
                ->schema([
                    Toggle::make('is_published')
                        ->label('Publicar Imediatamente')
                        ->default(true),

                    DateTimePicker::make('published_at')
                        ->label('Data e Hora')
                        ->default(now()),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('version')
                    ->label('Versão')
                    ->badge()
                    ->sortable(),

                Columns\TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),

                Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->color(fn (string $state): string => match ($state) {
                        'Recurso' => 'success', // Verde
                        'Melhoria' => 'info',    // Azul
                        'Correção' => 'warning', // Laranja
                        default => 'gray',
                    }),

                Columns\IconColumn::make('is_published')
                    ->label('Publicado')
                    ->boolean(),

                Columns\TextColumn::make('published_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChangelogs::route('/'),
            'create' => CreateChangelog::route('/create'),
            'edit' => EditChangelog::route('/{record}/edit'),
        ];
    }
}