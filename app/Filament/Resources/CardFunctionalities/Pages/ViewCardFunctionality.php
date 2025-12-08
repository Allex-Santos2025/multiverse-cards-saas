<?php

namespace App\Filament\Resources\CardFunctionalities\Pages;

use App\Filament\Resources\CardFunctionalities\CardFunctionalityResource;
use App\Models\Card;
use App\Models\Set;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class ViewCardFunctionality extends ViewRecord
{
    protected static string $resource = CardFunctionalityResource::class;

    public ?Card $selectedCardPrint = null;
    public ?string $selectedLanguage = null;
    public array $availableLanguages = [];
    public ?int $selectedSetId = null;
    public ?string $selectedCollectionNumber = null;

    // --- VARIÁVEIS DE PAGINAÇÃO ---
    public int $printPage = 1;
    public int $perPage = 20; 

    // --- MÉTODOS DE PAGINAÇÃO ---
    
    public function nextPage()
    {
        $this->printPage++;
    }

    public function previousPage()
    {
        if ($this->printPage > 1) {
            $this->printPage--;
        }
    }

    // --- AÇÕES DO CARD ---

    public function changePrint(int $cardId): void 
    {
        $newPrint = Card::with('set')->find($cardId);
        if ($newPrint) {
            $this->selectedCardPrint = $newPrint;
            $this->selectedLanguage = $newPrint->mtg_language_code;
            $this->selectedSetId = $newPrint->set_id;
            $this->selectedCollectionNumber = $newPrint->mtg_collection_number;
            $this->recalculateAvailableLanguages();
        }
    }

    public function changeLanguage(string $lang): void {
        if (in_array($lang, $this->availableLanguages)) {
            $this->selectedLanguage = $lang;
            $this->loadSelectedCardPrint();
        }
    }

    protected function loadSelectedCardPrint(): void {
        if (!$this->selectedSetId) return;
        
        // Ajuste para buscar tanto por mtg_collection_number quanto bs_collection_number
        $query = $this->record->cards()
            ->with('set')
            ->where('set_id', $this->selectedSetId)
            ->where(function($q) {
                if ($this->selectedCollectionNumber) {
                    $q->where('mtg_collection_number', $this->selectedCollectionNumber)
                      ->orWhere('bs_collection_number', $this->selectedCollectionNumber);
                }
            });

        $newLanguageCard = (clone $query)->where('mtg_language_code', $this->selectedLanguage)->first();

        if ($newLanguageCard) {
            $this->selectedCardPrint = $newLanguageCard;
        } else {
            $fallbackPrint = (clone $query)->where('mtg_language_code', 'en')->first();
            if ($fallbackPrint) {
                $this->selectedCardPrint = $fallbackPrint;
                $this->selectedLanguage = 'en';
            }
        }
    }

    protected function recalculateAvailableLanguages(): void {
        if (!$this->selectedSetId) {
            $this->availableLanguages = [];
            return;
        }
        $this->availableLanguages = $this->record->cards()
            ->where('set_id', $this->selectedSetId)
            ->where(function($q) {
                if ($this->selectedCollectionNumber) {
                    $q->where('mtg_collection_number', $this->selectedCollectionNumber)
                      ->orWhere('bs_collection_number', $this->selectedCollectionNumber);
                }
            })
            ->pluck('mtg_language_code')
            ->unique()
            ->sort()
            ->toArray();
    }

    public function getTitle(): string | Htmlable {
        return $this->selectedCardPrint?->mtg_printed_name ?? $this->record->name ?? 'Card View';
    }

    protected function getStatString(?string $power, ?string $toughness): ?string { 
        if ($power !== null || $toughness !== null) { 
            if ((is_null($power) || $power === '') && (is_null($toughness) || $toughness === '')) { return null; } 
            return "{$power} / {$toughness}"; 
        } 
        return null; 
    }

    protected static function convertManaSymbolsToHtml(?string $text): string { 
        if (empty($text)) { return ''; } 
        preg_match_all('/({[^}]+})/', $text, $matches); 
        if (empty($matches[0])) { return nl2br(e($text)); } 
        $html = e($text); 
        foreach ($matches[0] as $symbol) { 
            $class = strtolower(trim($symbol, '{}')); 
            $class = str_replace(['/', 'p'], '', $class); 
            if ($class === 't') $class = 'tap'; 
            if ($class === 'q') $class = 'untap'; 
            $class = preg_replace('/[^a-z0-9\-]/', '', $class); 
            if (!empty($class)) { 
                $iconHtml = "<i class=\"ms ms-{$class} ms-cost ms-shadow\" style=\"vertical-align: -0.05em; font-size: 0.95em;\"></i>"; 
                $html = str_replace($symbol, $iconHtml, $html);
            } 
        } 
        return nl2br($html); 
    }

    public function infolist(Schema $schema): Schema
    {
        $tcgName = $this->record->game->name ?? 'Magic: The Gathering'; 

        // --- LÓGICA DE BUSCA PAGINADA ---
        $cardsQuery = $this->record->cards()
            ->with('set');

        $totalPrints = $cardsQuery->count();
        $totalPages = ceil($totalPrints / $this->perPage);

        $allPrintGroups = $cardsQuery
            ->forPage($this->printPage, $this->perPage)
            ->get()
            ->groupBy(function($card) {
                return $card->set_id . '_' . ($card->mtg_collection_number ?? $card->bs_collection_number);
            });

        // Schemas
        $statsSchema = [
            TextEntry::make('cost')->label('Custo')
                ->state(fn() => $this->selectedCardPrint?->mtg_mana_cost ?? $this->record->cost)
                ->html()->formatStateUsing(fn (?string $state): HtmlString => new HtmlString("<span class=\"mana-cost text-xl\">" . static::convertManaSymbolsToHtml($state) . "</span>"))
                ->visible(fn() => !empty($this->selectedCardPrint?->mtg_mana_cost ?? $this->record->cost)),
                
            TextEntry::make('mtg_cmc')->label('CMC')
                ->state(fn() => $this->selectedCardPrint?->mtg_cmc ?? $this->record->mtg_cmc)
                ->visible(fn() => ($this->selectedCardPrint?->mtg_cmc ?? $this->record->mtg_cmc) > 0 && $tcgName === 'Magic: The Gathering'),
                
            TextEntry::make('power_toughness')->label($tcgName === 'Battle Scenes' ? 'Energia / Escudo' : 'Poder / Resistência')
                ->state(fn() => $this->getStatString($this->selectedCardPrint?->mtg_power ?? $this->record->power, $this->selectedCardPrint?->mtg_toughness ?? $this->record->toughness))
                ->visible(fn() => $this->getStatString($this->selectedCardPrint?->mtg_power ?? $this->record->power, $this->selectedCardPrint?->mtg_toughness ?? $this->record->toughness) !== null),
            
            TextEntry::make('mtg_loyalty')->label('Lealdade')
                ->state(fn() => $this->selectedCardPrint?->mtg_loyalty ?? $this->record->mtg_loyalty)
                ->visible(fn($state) => !empty($state) && $tcgName === 'Magic: The Gathering'),
                
            Section::make('Legalidade')
                ->schema([ViewEntry::make('mtg_legalities')->hiddenLabel()->view('filament.infolists.components.legalities-view')->viewData(['legalities' => $this->record->mtg_legalities])])
                ->collapsible()->collapsed(true)
                ->visible(fn() => !empty($this->record->mtg_legalities)),
        ];

        $commonSchema = [
            // Usa 'name' accessor do Model
            TextEntry::make('name')->label('Nome')
                ->state(fn() => $this->selectedCardPrint ? ($this->selectedCardPrint->mtg_printed_name ?? $this->record->name) : $this->record->name),
                
            TextEntry::make('type_line')->label('Tipo')
                ->state(fn() => $this->selectedCardPrint ? ($this->selectedCardPrint->mtg_printed_type_line ?? $this->record->type_line) : $this->record->type_line),
                
            TextEntry::make('rules_text')->label('Texto de Regras')->html()->state(function() use ($tcgName) {
                if ($this->selectedCardPrint) {
                    $text = $this->selectedCardPrint->mtg_printed_text ?? $this->record->rules_text;
                } else {
                    $text = $this->record->rules_text;
                }
                
                $text = $text ?? '';
                
                if ($tcgName === 'Magic: The Gathering') {
                    return new HtmlString(static::convertManaSymbolsToHtml($text));
                }
                return nl2br(e($text)); 
            }),
            
            TextEntry::make('artist')->label('Artista')
                ->state(fn() => $this->selectedCardPrint?->bs_artist ?? $this->selectedCardPrint?->mtg_artist ?? $this->record->cards->first()?->bs_artist)
                ->visible(fn($state) => !empty($state)),
                
            TextEntry::make('flavor_text')->label('Texto de Ambientação')
                ->extraAttributes(['class' => 'italic text-gray-600'])
                ->state(fn() => $this->selectedCardPrint?->bs_flavor_text ?? $this->selectedCardPrint?->mtg_flavor_text ?? $this->record->bs_flavor_text)
                ->visible(fn($state) => !empty($state)),
        ];

        return $schema
            ->record($this->record)
            ->schema([
                Grid::make(3)->columnSpanFull()->schema([
                    Section::make()->columnSpan(1)->schema([
                        // --- PRIORIZAÇÃO DE IMAGEM ---
                        ImageEntry::make('image_display')->hiddenLabel()->width('100%')->height('auto')->extraImgAttributes(['class' => 'rounded-lg shadow-md'])
                            ->state(function () {
                                $print = $this->selectedCardPrint ?? $this->record->cards->first();
                                
                                if ($print) {
                                    // 1. Prioridade Máxima: Caminho Local do Battle Scenes (card_images/...)
                                    if (!empty($print->local_image_path)) {
                                        return asset($print->local_image_path);
                                    }
                                    
                                    // 2. Caminho Local do Magic
                                    if (!empty($print->local_image_path_large)) {
                                        return asset($print->local_image_path_large);
                                    }
                                    
                                    // 3. URLs remotas (fallback)
                                    if (!empty($print->bs_image_url)) return $print->bs_image_url;
                                    if (!empty($print->mtg_image_url_api)) return $print->mtg_image_url_api;
                                }
                                
                                return 'https://placehold.co/600x850?text=No+Image';
                            }),
                        ViewEntry::make('languageButtons')->hiddenLabel()->view('filament.infolists.components.language-switcher-view')->viewData(['availableLanguages' => $this->availableLanguages, 'selectedLanguage' => $this->selectedLanguage]),
                    ]),
                    Section::make('Detalhes')->columnSpan(1)->schema(array_merge($commonSchema, $statsSchema))->collapsible(),
                    
                    Section::make('Impressões')
                        ->columnSpan(1)
                        ->schema([
                            ViewEntry::make('allPrints')
                                ->hiddenLabel()
                                ->view('filament.infolists.components.print-list-view')
                                ->viewData([
                                    'allPrintGroups' => $allPrintGroups,
                                    'currentPrintId' => $this->selectedCardPrint?->id,
                                    'tcgName' => $tcgName,
                                    'currentPage' => $this->printPage,
                                    'totalPages' => $totalPages,
                                    'totalPrints' => $totalPrints
                                ])
                        ])
                ]),
            ]);
    }
}