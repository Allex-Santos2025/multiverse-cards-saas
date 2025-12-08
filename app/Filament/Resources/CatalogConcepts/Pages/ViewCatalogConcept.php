<?php

namespace App\Filament\Resources\CatalogConcepts\Pages;

use App\Filament\Resources\CatalogConcepts\CatalogConceptResource;
use App\Models\Catalog\CatalogPrint;
use App\Models\Games\Pokemon\PkConcept;
use App\Models\Games\Pokemon\PkPrint;
use App\Models\Games\Magic\MtgConcept; 
use App\Models\Games\Magic\MtgPrint;

use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema; 

// --- CORREÇÃO DE NAMESPACES (Layouts vêm de Schemas) ---
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group; 

// --- Entries continuam em Infolists (geralmente) ---
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\ViewEntry;

use Illuminate\Support\HtmlString;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Get; 
use Illuminate\Support\Arr;

class ViewCatalogConcept extends ViewRecord 
{
    protected static string $resource = \App\Filament\Resources\CatalogConcepts\CatalogConceptResource::class;

    // --- STATE MANAGEMENT ---
    public ?int $selectedPrintId = null;
    public ?string $selectedLanguage = null;
    public array $availableLanguages = [];
    public ?int $selectedSetId = null;
    public ?string $selectedNumber = null;

    public int $printPage = 1;
    public int $perPage = 20;

    // --- HELPER DE COLUNAS ---
    protected function getSpecificFields(): array
    {
        if ($this->record->game_id === 1) { // Magic
            return [
                'number' => 'collector_number', 
                'language' => 'language_code'   
            ];
        }
        
        return [
            'number' => 'number',
            'language' => 'language_code'
        ];
    }

    protected function getSpecificClass(): string
    {
        if ($this->record->game_id === 1) {
            return MtgPrint::class;
        }
        return PkPrint::class; 
    }

    // --- LIFECYCLE ---
    public function mount(int | string $record): void
    {
        parent::mount($record);

        $firstPrint = $this->record->prints()->with(['set', 'specific'])->latest()->first();
        if ($firstPrint) {
            $this->changePrint($firstPrint->id);
        }
    }

    // --- AÇÕES ---
    public function changePrint(int $printId): void 
    {
        $newPrint = CatalogPrint::with(['set', 'specific'])->find($printId);
        
        if ($newPrint && $newPrint->concept_id === $this->record->id) {
            $this->selectedPrintId = $newPrint->id;
            $this->selectedSetId = $newPrint->set_id;
            
            $specific = $newPrint->specific;
            $fields = $this->getSpecificFields();

            $this->selectedLanguage = $specific->{$fields['language']} ?? 'en';
            $this->selectedNumber = $specific->{$fields['number']} ?? null;
            
            $this->recalculateAvailableLanguages();
        }
    }

    public function changeLanguage(string $lang): void 
    {
        if (in_array($lang, $this->availableLanguages)) {
            $this->selectedLanguage = $lang;
            $this->loadSelectedPrintByLanguage();
        }
    }

    // --- LÓGICA INTERNA ---

    protected function recalculateAvailableLanguages(): void 
    {
        if (!$this->selectedSetId || !$this->selectedNumber) {
            $this->availableLanguages = [];
            return;
        }

        $fields = $this->getSpecificFields();
        $targetClass = $this->getSpecificClass();

        $query = $this->record->prints()
            ->where('set_id', $this->selectedSetId);

        $query->whereHasMorph('specific', [$targetClass], function ($q) use ($fields) {
            $q->where($fields['number'], $this->selectedNumber);
        });

        $this->availableLanguages = $query->with('specific')
            ->get()
            ->map(fn ($p) => $p->specific->{$fields['language']} ?? 'en')
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    protected function loadSelectedPrintByLanguage(): void 
    {
        if (!$this->selectedSetId || !$this->selectedNumber) return;

        $fields = $this->getSpecificFields();
        $targetClass = $this->getSpecificClass();

        $query = $this->record->prints()
            ->where('set_id', $this->selectedSetId)
            ->whereHasMorph('specific', [$targetClass], function ($q) use ($fields) {
                $q->where($fields['number'], $this->selectedNumber)
                  ->where($fields['language'], $this->selectedLanguage);
            });

        $newPrint = $query->first();

        if ($newPrint) {
            $this->selectedPrintId = $newPrint->id;
        } else {
            $fallback = $this->record->prints()
                ->where('set_id', $this->selectedSetId)
                ->whereHasMorph('specific', [$targetClass], function($q) use ($fields) {
                    $q->where($fields['language'], 'en')
                      ->where($fields['number'], $this->selectedNumber);
                })
                ->first();
            
            if ($fallback) {
                $this->selectedPrintId = $fallback->id;
                $this->selectedLanguage = 'en';
            }
        }
    }

    // --- GETTERS & HELPERS ---

    public function getSelectedPrintProperty(): ?CatalogPrint
    {
        return CatalogPrint::with(['set', 'specific'])->find($this->selectedPrintId);
    }

    public function nextPage() { $this->printPage++; }
    public function previousPage() { if ($this->printPage > 1) return $this->printPage--; }

    public function getTitle(): string | Htmlable 
    {
        return $this->record->name ?? 'Visualizar Carta';
    }
    
    protected static function convertManaSymbolsToHtml(?string $text): string
    {
        return \App\Filament\Resources\CatalogConcepts\CatalogConceptResource::convertManaSymbolsToHtml($text);
    }
    
    // --- LAYOUT INFOLIST (Schema) ---
    public function infolist(Schema $schema): Schema
    {
        $gameName = $this->record->game->name ?? 'Unknown';
        $isPokemon = $this->record->game_id === 2;
        $isMagic = $this->record->game_id === 1;

        $print = $this->selectedPrint;

        $printsQuery = $this->record->prints()->with(['set', 'specific']);
        $totalPrints = $printsQuery->count();
        $totalPages = ceil($totalPrints / $this->perPage);
        
        $allPrintGroups = $printsQuery
            ->forPage($this->printPage, $this->perPage)
            ->get()
            ->groupBy('set_id');

        // --- SCHEMAS DE DADOS ---
        
        $commonSchema = [
            TextEntry::make('name')
                ->label('Nome')
                ->size('large') 
                ->weight('bold')
                ->state(fn() => $print?->printed_name ?? $this->record->name), 
            
            TextEntry::make('type_line')
                ->label('Tipo')
                ->state(fn() => $print?->specific?->type_line 
                              ?? Arr::get($print?->specific, 'supertype') 
                              ?? $this->record->specific?->type_line
                              ?? Arr::get($this->record->specific, 'supertype')),

            TextEntry::make('rules_text')
                ->label('Texto de Regras')
                ->html()
                ->state(function() use ($isMagic, $print) {
                    $text = Arr::get($print?->specific, 'oracle_text') 
                         ?? Arr::get($print?->specific, 'rules_text') 
                         ?? Arr::get($this->record->specific, 'oracle_text') 
                         ?? Arr::get($this->record->specific, 'rules_text');
                    
                    $text = $text ?? '';
                    if ($isMagic) {
                        return new HtmlString(self::convertManaSymbolsToHtml($text));
                    }
                    return nl2br(e($text)); 
                })
                ->columnSpanFull(),
                
            TextEntry::make('artist')
                ->label('Artista')
                ->state(fn() => Arr::get($print?->specific, 'artist') ?? '-')
                ->visible(fn() => !empty(Arr::get($print?->specific, 'artist'))),
                
            TextEntry::make('flavor_text')
                ->label('Texto de Ambientação')
                ->extraAttributes(['class' => 'italic text-gray-600'])
                ->state(fn() => Arr::get($print?->specific, 'flavor_text') ?? '-')
                ->visible(fn() => !empty(Arr::get($print?->specific, 'flavor_text'))),
        ];

        $statsSchema = [
            // Agora usando Group corretamente com o namespace do Schema
            Group::make()->schema([
                TextEntry::make('cost')
                    ->label('Custo')
                    ->state(fn() => Arr::get($print?->specific, 'mana_cost') ?? '-')
                    ->html()
                    ->formatStateUsing(fn (?string $state) => new HtmlString("<span class=\"mana-cost text-xl\">" . self::convertManaSymbolsToHtml($state) . "</span>"))
                    ->visible($isMagic),

                TextEntry::make('hp')
                    ->label('HP')
                    ->state(fn() => Arr::get($print?->specific, 'hp') ?? '-')
                    ->badge()->color('danger')
                    ->visible($isPokemon),
                
                Section::make('Legalidade')
                    ->schema([
                        ViewEntry::make('legalities')
                            ->hiddenLabel()
                            ->view('filament.infolists.components.legalities-view')
                            ->viewData([
                                'legalities' => Arr::get($this->record->specific, 'legalities'),
                                'tcgName' => $gameName
                            ])
                    ])
                    ->collapsible()->collapsed(true)
                    ->visible(fn() => !empty(Arr::get($this->record->specific, 'legalities'))),
            ]),

            ViewEntry::make('attacks_view')
                ->hiddenLabel()
                ->view('filament.infolists.components.pokemon-attacks-view')
                ->viewData(['attacks' => Arr::get($print?->specific, 'attacks') ?? []])
                ->visible($isPokemon && !empty(Arr::get($print?->specific, 'attacks'))),
        ];

        return $schema
            ->record($this->record)
            ->schema([
                Grid::make(3)->schema([
                    Section::make()->columnSpan(1)->schema([
                        ImageEntry::make('image_display')
                            ->hiddenLabel()
                            ->width('100%')
                            ->height('auto')
                            ->extraImgAttributes(['class' => 'rounded-lg shadow-md'])
                            ->state(function () use ($print) {
                                if ($print) {
                                    if (!empty($print->image_path)) return asset($print->image_path);
                                    if (!empty($print->specific->image_url)) return $print->specific->image_url;
                                }
                                return 'https://placehold.co/600x850?text=No+Image';
                            }),

                        ViewEntry::make('languageButtons')
                            ->hiddenLabel()
                            ->view('filament.infolists.components.language-switcher-view')
                            ->viewData([
                                'availableLanguages' => $this->availableLanguages, 
                                'selectedLanguage' => $this->selectedLanguage
                            ]),
                    ]),

                    Section::make('Detalhes')->columnSpan(1)->schema(array_merge($commonSchema, $statsSchema)),

                    Section::make('Impressões')->columnSpan(1)->schema([
                        ViewEntry::make('allPrints')
                            ->hiddenLabel()
                            ->view('filament.infolists.components.print-list-view')
                            ->viewData([
                                'allPrintGroups' => $allPrintGroups,
                                'currentPrintId' => $this->selectedPrintId,
                                'tcgName' => $gameName,
                                'currentPage' => $this->printPage,
                                'totalPages' => $totalPages,
                                'totalPrints' => $totalPrints
                            ])
                    ]),
                ]),
            ]);
    }
}