<?php

namespace App\Filament\Resources\CardFunctionalities\Pages;

use App\Filament\Resources\CardFunctionalities\CardFunctionalityResource;
use App\Models\Card;
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

    public function mount(int | string $record): void
    {
        parent::mount($record);

        \Filament\Support\Facades\FilamentView::registerRenderHook('panels::body.end', fn (): string => Blade::render('<script>document.addEventListener(\'alpine:initialized\', () => { if (window.Alpine && Alpine.store(\'sidebar\')) { Alpine.store(\'sidebar\').close(); } }); if (window.Alpine && Alpine.store(\'sidebar\') && Alpine.store(\'sidebar\').isOpen) { Alpine.store(\'sidebar\').close(); } </script>'), scopes: static::class);

        // OTIMIZAÇÃO CRÍTICA: Não carregamos 'get()' em todos os prints para evitar estouro de memória em Terrenos Básicos.
        // Buscamos diretamente o print padrão (inglês ou o mais recente)
        
        $defaultPrint = $this->record->cards()
            ->with('set')
            ->where('mtg_language_code', 'en')
            ->orderBy('mtg_released_at', 'desc')
            ->first();

        // Se não tiver em inglês, pega qualquer um (o mais recente)
        if (!$defaultPrint) {
            $defaultPrint = $this->record->cards()
                ->with('set')
                ->orderBy('mtg_released_at', 'desc')
                ->first();
        }

        if ($defaultPrint) {
            $this->selectedCardPrint = $defaultPrint;
            $this->selectedLanguage = $defaultPrint->mtg_language_code;
            $this->selectedSetId = $defaultPrint->set_id;
            $this->selectedCollectionNumber = $defaultPrint->mtg_collection_number;
        }
        
        $this->recalculateAvailableLanguages();
    }

    protected function resetSelection()
    {
        $this->selectedCardPrint = null;
        $this->selectedLanguage = null;
        $this->selectedSetId = null;
        $this->selectedCollectionNumber = null;
        $this->availableLanguages = [];
    }

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
        
        $query = $this->record->cards()
            ->with('set')
            ->where('set_id', $this->selectedSetId)
            ->where('mtg_collection_number', $this->selectedCollectionNumber);

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
            ->where('mtg_collection_number', $this->selectedCollectionNumber)
            ->pluck('mtg_language_code')
            ->unique()
            ->sort()
            ->toArray();
    }

    public function getTitle(): string | Htmlable {
        return $this->selectedCardPrint?->mtg_printed_name ?? $this->record->mtg_name ?? 'Card View';
    }

    // --- HELPERS ---

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
        if (empty($matches[0])) { 
            return nl2br(e($text)); 
        } 
        
        $html = e($text); 
        foreach ($matches[0] as $symbol) { 
            $class = strtolower(trim($symbol, '{}')); 
            $class = str_replace(['/', 'p'], '', $class); 
            
            if ($class === 't') $class = 'tap'; 
            if ($class === 'q') $class = 'untap'; 
            
            $class = preg_replace('/[^a-z0-9\-]/', '', $class); 
            
            if (!empty($class)) { 
                // CSS Personalizado
                $iconHtml = "<i class=\"ms ms-{$class} ms-cost ms-shadow\" style=\"vertical-align: -0.05em; font-size: 0.95em;\"></i>"; 
                $html = str_replace($symbol, $iconHtml, $html);
            } 
        } 
        return nl2br($html); 
    }

    public function infolist(Schema $schema): Schema
    {
        $tcgName = 'Magic: The Gathering';

        // OTIMIZAÇÃO CRÍTICA: Limita a lista de impressões para evitar crash de memória em Basic Lands
        $allPrintGroups = $this->record->cards()
            ->with('set')
            ->orderBy('mtg_released_at', 'desc')
            ->limit(50) // Limita aos 50 prints mais recentes para não travar a página
            ->get()
            ->groupBy(function($card) {
                return $card->set_id . '_' . $card->mtg_collection_number;
            });

        // Schema Específico do Magic
        $magicSchema = [
            TextEntry::make('mtg_mana_cost') 
                ->label('Custo')
                ->state(fn() => $this->selectedCardPrint?->mtg_mana_cost ?? $this->record->mtg_mana_cost)
                ->html()
                ->formatStateUsing(fn (?string $state): HtmlString =>
                    new HtmlString("<span class=\"mana-cost text-xl\">" . static::convertManaSymbolsToHtml($state) . "</span>")
                )
                ->visible(fn() => !empty($this->selectedCardPrint?->mtg_mana_cost ?? $this->record->mtg_mana_cost)),
            
            TextEntry::make('mtg_cmc')
                ->label('CMC')
                ->state(fn() => $this->selectedCardPrint?->mtg_cmc ?? $this->record->mtg_cmc)
                ->visible(fn() => ($this->selectedCardPrint?->mtg_cmc ?? $this->record->mtg_cmc) > 0),

            TextEntry::make('power_toughness')
                ->label('Poder / Resistência')
                ->state(fn() => $this->getStatString(
                    $this->selectedCardPrint?->mtg_power ?? $this->record->mtg_power, 
                    $this->selectedCardPrint?->mtg_toughness ?? $this->record->mtg_toughness
                ))
                ->visible(fn() => $this->getStatString(
                    $this->selectedCardPrint?->mtg_power ?? $this->record->mtg_power, 
                    $this->selectedCardPrint?->mtg_toughness ?? $this->record->mtg_toughness
                ) !== null),
            
            TextEntry::make('mtg_loyalty')
                ->label('Lealdade')
                ->state(fn() => $this->selectedCardPrint?->mtg_loyalty ?? $this->record->mtg_loyalty)
                ->visible(fn($state) => !empty($state)),
                
            Section::make('Legalidade')
                ->schema([
                    ViewEntry::make('mtg_legalities')
                        ->hiddenLabel()
                        ->view('filament.infolists.components.legalities-view')
                        ->viewData(['legalities' => $this->record->mtg_legalities])
                ])
                ->collapsible()
                ->collapsed(true),
        ];

        // Schema Comum
        $commonSchema = [
            TextEntry::make('name')
                ->label('Nome')
                ->state(fn() => $this->selectedCardPrint?->mtg_printed_name ?? $this->record->mtg_name),
            
            TextEntry::make('type_line')
                ->label('Tipo')
                ->state(fn() => $this->selectedCardPrint?->mtg_printed_type_line ?? $this->record->mtg_type_line),
            
            TextEntry::make('rules_text')
                ->label('Texto de Regras')
                ->html()
                ->state(function() use ($tcgName) {
                    // Fallback para Oracle Text
                    $text = $this->selectedCardPrint?->mtg_printed_text;
                    if (empty($text)) {
                        $text = $this->record->mtg_rules_text;
                    }
                    $text = $text ?? ''; // Garante string

                    if ($tcgName === 'Magic: The Gathering') {
                        return new HtmlString(static::convertManaSymbolsToHtml($text));
                    }
                    return nl2br(e($text)); 
                }),

            TextEntry::make('artist')
                ->label('Artista')
                ->state(fn() => $this->selectedCardPrint?->mtg_artist)
                ->visible(fn() => !empty($this->selectedCardPrint?->mtg_artist)),

            TextEntry::make('flavor_text')
                ->label('Texto de Ambientação')
                ->extraAttributes(['class' => 'italic text-gray-600']) 
                ->state(fn() => $this->selectedCardPrint?->mtg_flavor_text)
                ->visible(fn() => !empty($this->selectedCardPrint?->mtg_flavor_text)),
        ];

        return $schema
            ->record($this->record)
            ->schema([
                Grid::make(3)->columnSpanFull()->schema([
                    
                    // COLUNA 1: IMAGEM
                    Section::make()
                        ->columnSpan(1)
                        ->schema([
                            ImageEntry::make('image_display')
                                ->hiddenLabel()
                                ->width('100%')
                                ->height('auto')
                                ->extraImgAttributes(['class' => 'rounded-lg shadow-md'])
                                ->state(function () {
                                    if ($this->selectedCardPrint && $this->selectedCardPrint->local_image_path_large) {
                                        return asset($this->selectedCardPrint->local_image_path_large);
                                    }
                                    if ($this->selectedCardPrint && $this->selectedCardPrint->mtg_image_url_api) {
                                        return $this->selectedCardPrint->mtg_image_url_api;
                                    }
                                    return 'https://placehold.co/600x850?text=No+Image';
                                }),

                            ViewEntry::make('languageButtons')
                                ->hiddenLabel()
                                ->view('filament.infolists.components.language-switcher-view')
                                ->viewData([
                                    'availableLanguages' => $this->availableLanguages,
                                    'selectedLanguage' => $this->selectedLanguage,
                                ]),
                        ]),

                    // COLUNA 2: DETALHES
                    Section::make('Detalhes')
                        ->columnSpan(1)
                        ->schema(array_merge($commonSchema, $magicSchema))
                        ->collapsible(),

                    // COLUNA 3: LISTA DE PRINTS
                    Section::make('Impressões')
                        ->columnSpan(1)
                        ->schema([
                            ViewEntry::make('allPrints')
                                ->hiddenLabel()
                                ->view('filament.infolists.components.print-list-view')
                                ->viewData([
                                    'allPrintGroups' => $allPrintGroups,
                                    'currentPrintId' => $this->selectedCardPrint?->id,
                                    'tcgName' => $tcgName
                                ])
                        ])
                ]),
            ]);
    }
}