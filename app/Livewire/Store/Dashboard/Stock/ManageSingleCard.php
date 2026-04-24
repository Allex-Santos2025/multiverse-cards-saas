<?php

namespace App\Livewire\Store\Dashboard\Stock;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Store;
use App\Models\Game;
use App\Models\Catalog\CatalogConcept;
use App\Models\Catalog\CatalogPrint;
use App\Models\StockItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Enums\StockExtra;

class ManageSingleCard extends Component
{
    use WithFileUploads;

    public $slug, $game_slug, $conceptSlug, $userStoreId, $nomePT;
    public $loja, $game, $concept; 

    // Variáveis exclusivas para isolar os Terrenos Básicos
    public $isBasicLand = false;
    public $basicNumber = null;

    // Variáveis exclusivas para isolar as Variantes de Arte (FEM, ALL, HML)
    public $isArtVariant = false;
    public $validPrintIds = [];

    public $showModal = false, $isEditing = false, $editingItemId, $keepOpen = false;
    public $searchPrint = '', $selectedPrintId;

    public $language = 'pt', $availableLanguages = [];
    public $quality = 'NM'; 
    public $selectedExtras = []; 
    public $availableExtras = [];
    public $disabledExtras = [];

    public $price, $quantity = 1, $comment, $realImage;
    public $discount_percent = 0, $discount_start, $discount_end;

    public $marketPrices = ['min' => 0, 'mid' => 0, 'max' => 0];
    public $currentPrintImage;

    public $isFoilSelected = false;

    public function mount($slug, $game_slug, $conceptSlug)
    {
        $this->slug = $slug;
        $this->game_slug = $game_slug;
        $this->conceptSlug = $conceptSlug;
        $this->userStoreId = auth('store_user')->user()->current_store_id;

        $this->loja = Store::where('url_slug', $slug)->firstOrFail();
        $this->game = Game::where('url_slug', $game_slug)->firstOrFail();

        // =========================================================
        // 1) DETECTA SE O SLUG É DE TERRENO BÁSICO
        // =========================================================
        $isBasicLandSlug = preg_match(
            '/^(plains|island|swamp|mountain|forest)-(\d+)$/',
            $conceptSlug,
            $matches
        );

        if ($isBasicLandSlug) {
            $this->isBasicLand = true;
            $this->basicNumber = $matches[2];
            $basicTypeSlug   = $matches[1];
            
            $basicTypeMap = [
                'plains'   => 'Plains',
                'island'   => 'Island',
                'swamp'    => 'Swamp',
                'mountain' => 'Mountain',
                'forest'   => 'Forest',
            ];
            
            $englishBasicTypeName = $basicTypeMap[$basicTypeSlug] ?? null;

            if (!$englishBasicTypeName) abort(404, 'Tipo de terreno básico inválido no slug.');

            $basePrint = CatalogPrint::where('collector_number', $this->basicNumber)
                ->where('type_line', 'LIKE', '%Basic Land%')
                ->where('type_line', 'LIKE', '%' . $englishBasicTypeName . '%')
                ->whereHas('set', function ($q) {
                    $q->where('game_id', $this->game->id);
                })->first();

            if (!$basePrint) abort(404, 'Terreno básico não encontrado com este número.');

            $this->concept = $basePrint->concept;

            // Injeta o número no nome do conceito
            $displayEnglishName = sprintf('%s (#%s)', $englishBasicTypeName, $this->basicNumber);
            $this->concept->name = $displayEnglishName;

            $printPt = CatalogPrint::where('concept_id', $this->concept->id)
                ->whereIn('language_code', ['pt', 'pt-br', 'pt-BR'])
                ->whereNotNull('printed_name')->where('printed_name', '!=', '')->first();

            $this->nomePT = $printPt 
                ? sprintf('%s (#%s)', $printPt->printed_name, $this->basicNumber) 
                : $displayEnglishName;

        } else {
            // =====================================================
            // 2) LÓGICA PARA CARTA NORMAL & VARIANTES DE ARTE (BLINDADO)
            // =====================================================
            
            // Tenta achar com o sufixo de 4 caracteres exato
            $conceptFound = CatalogConcept::where('game_id', $this->game->id)
                ->where('slug', 'like', $conceptSlug . '-____')
                ->with('prints.set')->first();

            // Busca progressiva para variantes de arte (se não achou o base)
            if (!$conceptFound) {
                $parts = explode('-', $conceptSlug);
                array_pop($parts);
                
                while (count($parts) > 0) {
                    $testSlug = implode('-', $parts);
                    $conceptFound = CatalogConcept::where('game_id', $this->game->id)
                        ->where('slug', 'like', $testSlug . '-____')
                        ->with('prints.set')->first();
                    if ($conceptFound) break;
                    array_pop($parts);
                }
            }

            if (!$conceptFound) abort(404, 'Carta não encontrada no catálogo.');
            $this->concept = $conceptFound;

            $allConceptPrints = $this->concept->prints;
            $specificIds = $allConceptPrints->pluck('specific_id')->filter()->unique();
            $mtgPrintsData = DB::table('mtg_prints')->whereIn('id', $specificIds)->get()->keyBy('id');

            // --- LÓGICA DE CONTADOR DE ARTISTAS COM ISOLAMENTO POR SET ---
            $artistIndexesCache = [];
            $siblings = DB::table('catalog_prints')
                ->join('mtg_prints', 'catalog_prints.specific_id', '=', 'mtg_prints.id')
                ->where('catalog_prints.concept_id', $this->concept->id)
                ->where('catalog_prints.collector_number', 'REGEXP', '[a-zA-Z]')
                ->select('catalog_prints.collector_number', 'catalog_prints.set_id', 'mtg_prints.artist')
                ->orderBy('catalog_prints.collector_number', 'asc')
                ->get();
            
            foreach($siblings as $sib) {
                $art = trim($sib->artist ?: 'Artista Desconhecido');
                $sid = $sib->set_id;
                $cNum = strtolower(trim($sib->collector_number));

                if(!isset($artistIndexesCache[$sid][$art])) {
                    $artistIndexesCache[$sid][$art] = [];
                }
                
                if (!in_array($cNum, $artistIndexesCache[$sid][$art])) {
                    $artistIndexesCache[$sid][$art][] = $cNum;
                }
            }

            $matchingPrints = collect();
            $variantArtistLabel = null;

            foreach ($allConceptPrints as $print) {
                $printMtgData = $mtgPrintsData->get($print->specific_id);
                $rawArtist = trim($printMtgData->artist ?? 'Artista Desconhecido');
                
                $englishName = $this->concept->name;
                $setCode = strtoupper($print->set->code ?? '');
                $isVariantSet = in_array($setCode, ['FEM', 'ALL', 'HML']);
                $hasLetterInNumber = preg_match('/[a-zA-Z]/', $print->collector_number);
                $isPrintBasicLand = str_contains($print->type_line ?? '', 'Basic Land');

                if ($isVariantSet && $hasLetterInNumber && !$isPrintBasicLand) {
                    $nomeArtistaFinal = $rawArtist;
                    $sid = $print->set_id;

                    // Só aplica o número se houver mais de uma arte do mesmo artista NESTE set
                    if (isset($artistIndexesCache[$sid][$rawArtist]) && count($artistIndexesCache[$sid][$rawArtist]) > 1) {
                        $idx = array_search(strtolower(trim($print->collector_number)), $artistIndexesCache[$sid][$rawArtist]);
                        if ($idx !== false) {
                            $nomeArtistaFinal .= ' ' . ($idx + 1);
                        }
                    }

                    $virtualSlug = Str::slug($englishName . '-' . $nomeArtistaFinal);
                    $print->artist_virtual = $nomeArtistaFinal;
                    $print->is_art_variant = true;
                } else {
                    $virtualSlug = $this->conceptSlug; // URL base sem o sufixo
                    $print->artist_virtual = $rawArtist;
                    $print->is_art_variant = false;
                }

                if ($virtualSlug === $conceptSlug) {
                    $matchingPrints->push($print);
                    $variantArtistLabel = $print->artist_virtual;
                }
            }

            if ($matchingPrints->isNotEmpty() && $matchingPrints->first()->is_art_variant) {
                $this->isArtVariant = true;
                $this->validPrintIds = $matchingPrints->pluck('id')->toArray();
            }

            $printPt = CatalogPrint::where('concept_id', $this->concept->id)
                ->whereIn('language_code', ['pt', 'pt-br', 'pt-BR'])
                ->whereNotNull('printed_name')->where('printed_name', '!=', '')->first();

            $baseName = $printPt ? $printPt->printed_name : $this->concept->name;
            $this->nomePT = ($this->isArtVariant && $variantArtistLabel) ? $baseName . ' (' . $variantArtistLabel . ')' : $baseName;
        }

        try {
            $this->availableExtras = StockExtra::options();
        } catch (\Throwable $e) {
            $this->availableExtras = ['foil' => 'Foil', 'etched' => 'Etched', 'promo' => 'Promo'];
        }

        $initialPrints = $this->concept->prints;
        if ($this->isBasicLand && $this->basicNumber) {
            $initialPrints = $initialPrints->where('collector_number', $this->basicNumber);
        } elseif ($this->isArtVariant && !empty($this->validPrintIds)) {
            $initialPrints = $initialPrints->whereIn('id', $this->validPrintIds);
        }

        if($initialPrints->count() === 1) {
            $p = $initialPrints->first();
            $this->selectPrint($p->id, "#{$p->collector_number} - " . ($p->set->name_pt ?? $p->set->name));
        }
    }

    public function selectPrint($id, $label)
    {
        $this->selectedPrintId = $id;
        $this->searchPrint = $label;
        $mainPrint = CatalogPrint::find($id);
        if (!$mainPrint) return;

        $this->availableLanguages = CatalogPrint::where('set_id', $mainPrint->set_id)
            ->where('collector_number', $mainPrint->collector_number)
            ->get()
            ->map(function($p) {
                return ['id' => $p->id, 'code' => $p->language_code, 'label' => $this->formatLanguageLabel($p->language_code)];
            })->toArray();

        $this->language = $mainPrint->language_code;
        $this->autoDetectTreatment($mainPrint);
        $this->updateMarketData();
    }

    private function autoDetectTreatment($print)
    {
        if ($this->isEditing) return;

        $mtgData = DB::table('mtg_prints')->where('id', $print->specific_id)->first();
        $this->selectedExtras = [];
        $this->disabledExtras = [];

        if (!$mtgData || empty($mtgData->prices)) {
            $this->selectedExtras = array_values(array_filter($this->selectedExtras, fn($e) => strtolower($e) !== 'foil_etched'));
            return;
        }

        $prices = is_string($mtgData->prices) ? json_decode($mtgData->prices, true) : (array)$mtgData->prices;
        $hasNormal = !empty($prices['usd']);
        $hasFoil   = !empty($prices['usd_foil']);
        $hasEtched = !empty($prices['usd_etched']);

        if ($hasNormal && !$hasFoil && !$hasEtched) { $this->disabledExtras = ['foil', 'foil_etched']; return; }
        if ($hasNormal && $hasFoil && !$hasEtched) { $this->disabledExtras = ['foil_etched']; return; }
        if ($hasEtched && !$hasNormal && !$hasFoil) { $this->selectedExtras = ['foil_etched']; $this->disabledExtras = ['foil', 'foil_etched']; return; }
        if ($hasFoil && !$hasNormal && !$hasEtched) { $this->selectedExtras = ['foil']; $this->disabledExtras = ['foil', 'foil_etched']; return; }

        $this->selectedExtras = array_values(array_filter($this->selectedExtras, fn($e) => strtolower($e) !== 'foil_etched'));
    }

    public function updatedSelectedExtras() 
    { 
        $extras = array_map('strtolower', $this->selectedExtras);
        if (in_array('foil', $extras) && in_array('etched', $extras)) {
            $lastAdded = strtolower(end($this->selectedExtras));
            if ($lastAdded === 'etched') {
                $this->selectedExtras = array_values(array_filter($this->selectedExtras, fn($e) => strtolower($e) !== 'foil'));
            } else {
                $this->selectedExtras = array_values(array_filter($this->selectedExtras, fn($e) => strtolower($e) !== 'etched'));
            }
        }
        $this->updateMarketData(); 
    }

    public function updatedLanguage($value)
    {
        $newPrint = collect($this->availableLanguages)->firstWhere('code', $value);
        if ($newPrint) {
            $this->selectedPrintId = $newPrint['id'];
            $this->currentPrintImage = CatalogPrint::find($this->selectedPrintId)?->image_path;
            $this->updateMarketData();
        }
    }

    public function updatedQuality() { $this->updateMarketData(); }

    private function updateMarketData()
    {
        if (!$this->selectedPrintId) return;
        $currentPrint = CatalogPrint::find($this->selectedPrintId);
        if (!$currentPrint) return;

        $this->currentPrintImage = $currentPrint->image_path;
        $englishPrint = CatalogPrint::where('set_id', $currentPrint->set_id)
            ->where('collector_number', $currentPrint->collector_number)
            ->where('language_code', 'en')
            ->first() ?? $currentPrint;

        $mtgData = DB::table('mtg_prints')->where('id', $englishPrint->specific_id)->first();
        $usd = 0;
        $extrasLower = array_map('strtolower', $this->selectedExtras);
        $isEtched = in_array('etched', $extrasLower) || in_array('foil_etched', $extrasLower); 
        $isFoil = in_array('foil', $extrasLower);

        if ($mtgData && !empty($mtgData->prices)) {
            $pricesArray = is_string($mtgData->prices) ? json_decode($mtgData->prices, true) : (array)$mtgData->prices;
            if ($isEtched) { $usd = $pricesArray['usd_etched'] ?? 0; }
            elseif ($isFoil) { $usd = $pricesArray['usd_foil'] ?? 0; }
            else { $usd = $pricesArray['usd'] ?? 0; }
        }

        $this->marketPrices['mid'] = (float)$usd * 5.50;
        $stats = $this->getMarketStats($currentPrint, $isFoil, $isEtched);
        $this->marketPrices['min'] = (float)($stats['min'] ?? 0);
        $this->marketPrices['max'] = (float)($stats['max'] ?? 0);
    }

    private function getMarketStats($print, $isFoil, $isEtched)
    {
        $fallbackOrders = [
            'M'  => [['M'], ['NM'], ['SP'], ['MP'], ['HP'], ['D']],
            'NM' => [['NM'], ['M'], ['SP'], ['MP'], ['HP'], ['D']],
            'SP' => [['SP'], ['NM', 'M'], ['MP'], ['HP'], ['D']],
            'MP' => [['MP'], ['SP', 'HP'], ['NM', 'M'], ['D']],
            'HP' => [['HP'], ['MP', 'D'], ['SP'], ['NM', 'M']],
            'D'  => [['D'], ['HP'], ['MP'], ['SP'], ['NM', 'M']],
        ];
        $steps = $fallbackOrders[$this->quality] ?? [[$this->quality]];

        foreach ($steps as $qualities) {
            $res = $this->queryVersusPrices($print, $isFoil, $isEtched, $qualities, true);
            if ($res->min_p > 0) return ['min' => $res->min_p, 'max' => $res->max_p];
        }
        foreach ($steps as $qualities) {
            $res = $this->queryVersusPrices($print, $isFoil, $isEtched, $qualities, false);
            if ($res->min_p > 0) return ['min' => $res->min_p, 'max' => $res->max_p];
        }
        return ['min' => 0, 'max' => 0];
    }

    private function queryVersusPrices($print, $isFoil, $isEtched, $qualities, $onlyActive = true)
    {
        $query = StockItem::withoutGlobalScopes()
            ->whereHas('catalogPrint', fn($q) => $q->where('set_id', $print->set_id)->where('collector_number', $print->collector_number))
            ->whereIn('condition', $qualities);

        if ($onlyActive) $query->where('quantity', '>', 0);
        $query->where(function($q) use ($isFoil, $isEtched) {
            if ($isEtched) { $q->where('extras', 'like', '%etched%'); }
            elseif ($isFoil) { $q->where('extras', 'like', '%foil%')->where('extras', 'not like', '%etched%'); }
            else { $q->where('extras', 'not like', '%foil%')->where('extras', 'not like', '%etched%'); }
        });
        return $query->selectRaw('MIN(price) as min_p, MAX(price) as max_p')->first();
    }

    private function formatLanguageLabel($code)
    {
        $langs = [
            'en'  => 'Inglês', 'pt'  => 'Português', 'pt-br' => 'Português', 'pt-BR' => 'Português',
            'ja'  => 'Japonês', 'it'  => 'Italiano', 'fr'  => 'Francês', 'de'  => 'Alemão',
            'es'  => 'Espanhol', 'ru'  => 'Russo', 'ko'  => 'Coreano',
            'zhs' => 'Chinês Simplificado', 'zht' => 'Chinês Tradicional',
        ];
        return $langs[strtolower($code)] ?? strtoupper($code);
    }

    public function edit($id)
    {
        $item = StockItem::withoutGlobalScopes()->where('store_id', $this->userStoreId)->findOrFail($id);
        $this->isEditing = true;
        $this->editingItemId = $id;
        $p = $item->catalogPrint;
        $label = "#{$p->collector_number} - " . ($p->set->name_pt ?? $p->set->name);
        $this->selectPrint($item->catalog_print_id, $label);
        $this->language = $item->language; $this->quality = $item->condition; $this->price = $item->price;
        $this->quantity = $item->quantity; $this->selectedExtras = is_array($item->extras) ? $item->extras : [];
        $this->comment = $item->comments; $this->discount_percent = $item->discount_percent;
        $this->discount_start = $item->discount_start ? \Carbon\Carbon::parse($item->discount_start)->format('Y-m-d') : null;
        $this->discount_end = $item->discount_end ? \Carbon\Carbon::parse($item->discount_end)->format('Y-m-d') : null;
        $this->showModal = true;
    }

    public function deleteItem(int $id): void
    {
        StockItem::withoutGlobalScopes()->findOrFail($id)->delete();
        $this->dispatch('notify', type: 'success', message: 'Card removido do estoque!');
    }

    public function resetForm()
    {
        $this->reset(['isEditing', 'editingItemId', 'price', 'quantity', 'comment', 'realImage', 'selectedExtras', 'discount_percent', 'discount_start', 'discount_end', 'selectedPrintId', 'searchPrint', 'availableLanguages', 'quality']);
        $this->quality = 'NM'; $this->showModal = false; $this->currentPrintImage = null;
        $this->marketPrices = ['min' => 0, 'mid' => 0, 'max' => 0];
    }

    public function save()
    {
        $this->validate(['selectedPrintId' => 'required', 'price' => 'required|numeric|min:0', 'quantity' => 'required|integer|min:0']);
        $data = [
            'catalog_print_id' => $this->selectedPrintId, 'price' => $this->price, 'quantity' => $this->quantity,
            'language' => $this->language, 'condition' => $this->quality, 'extras' => $this->selectedExtras,
            'comments' => $this->comment, 'discount_percent' => $this->discount_percent,
            'discount_start' => $this->discount_start, 'discount_end' => $this->discount_end,
        ];
        if ($this->isEditing) { StockItem::withoutGlobalScopes()->find($this->editingItemId)->update($data); }
        else { StockItem::create(array_merge($data, ['store_id' => $this->userStoreId])); }

        $this->dispatch('notify', type: 'success', message: $this->isEditing ? 'Atualizado!' : 'Cadastrado!');
        if (!$this->keepOpen) $this->resetForm();
    }

    public function render()
    {
        $conceptPrints = $this->concept->prints;
        if ($this->isBasicLand && $this->basicNumber) {
            $conceptPrints = $conceptPrints->where('collector_number', $this->basicNumber);
        } elseif ($this->isArtVariant && !empty($this->validPrintIds)) {
            $conceptPrints = $conceptPrints->whereIn('id', $this->validPrintIds);
        }
        
        $printIds = $conceptPrints->pluck('id')->toArray();
        $stockItems = StockItem::where('store_id', $this->userStoreId)->whereIn('catalog_print_id', $printIds) 
            ->with(['catalogPrint.set'])->get()->map(function($item) {
                $item->nome_da_edicao = $item->catalogPrint->set->name_pt ?? $item->catalogPrint->set->name ?? 'Edição n/a';
                return $item;
            });

        $availablePrints = $conceptPrints->groupBy(fn($p) => $p->set_id . '-' . $p->collector_number)
            ->map(fn($group) => $group->firstWhere('language_code', 'en') ?? $group->first())
            ->map(function($p) {
                $p->label_dropdown = "#{$p->collector_number} - " . ($p->set->name_pt ?? $p->set->name ?? 'Edição n/a');
                return $p;
            })->filter(function($p) {
                if (empty($this->searchPrint) || $this->searchPrint === $p->label_dropdown) return true;
                $term = strtolower($this->searchPrint);
                return str_contains(strtolower($p->label_dropdown), $term) || str_contains(strtolower($p->set->code), $term);
            })->values();

        return view('livewire.store.dashboard.stock.manage-single-card', [
            'stockItems' => $stockItems,
            'availablePrints' => $availablePrints
        ])->extends('layouts.dashboard')->section('content');
    }
}