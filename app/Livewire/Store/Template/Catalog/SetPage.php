<?php

namespace App\Livewire\Store\Template\Catalog;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Store;
use App\Models\Game;
use App\Models\Set;
use App\Models\Catalog\CatalogPrint;

class SetPage extends Component
{
    use WithPagination;

    // Variáveis da Rota
    public $slug;
    public $gameSlug;
    public $setCode;

    // Variáveis da Página
    public $loja;
    public $game;
    public $set;

    // Filtros da Tela
    public $sortOrder = 'number_asc';
    public $raridade = 'todas';
    public $cor = 'todas';
    public $perPage = 30;
    public $com_estoque = false;

    public function mount($slug, $gameSlug, $setCode)
    {
        $this->slug = $slug;
        $this->loja = Store::with('visual')->where('url_slug', $slug)->firstOrFail();

        $this->gameSlug = $gameSlug;
        $this->game = Game::where('url_slug', $gameSlug)->firstOrFail();

        $this->setCode = $setCode;
        $this->set = Set::where('game_id', $this->game->id)
                        ->where('code', $setCode)
                        ->firstOrFail();
    }

    public function updated($propertyName)
    {
        $this->resetPage();
    }

    public function render()
    {
        // 1. O GATEKEEPER: A loja trabalha com esse Set?
        $lojaTrabalhaComOSet = \App\Models\StockItem::where('store_id', $this->loja->id)
            ->whereHas('catalogPrint', function ($q) {
                $q->where('set_id', $this->set->id);
            })->exists();

        if (!$lojaTrabalhaComOSet) {
            $cartas = collect([]); 
        } 
        else {
            // A MINI-BUSCA DO NOME EM PORTUGUÊS
            $nomePtSubquery = \App\Models\Catalog\CatalogPrint::from('catalog_prints as cp_pt')
                ->select('cp_pt.printed_name')
                ->whereColumn('cp_pt.set_id', 'catalog_prints.set_id')
                ->whereColumn('cp_pt.collector_number', 'catalog_prints.collector_number')
                ->whereIn('cp_pt.language_code', ['pt', 'PT', 'pt-br', 'pt-BR'])
                ->limit(1);

            // ==========================================
            // FILTRANDO OS IDs BASE (Excluindo variantes A-)
            // ==========================================
            $queryIds = \App\Models\Catalog\CatalogPrint::where('set_id', $this->set->id)
                ->where('language_code', 'en')
                ->where('printed_name', 'NOT LIKE', 'A-%');

            if ($this->set->card_count > 0) {
                $queryIds->whereRaw('CAST(collector_number AS UNSIGNED) <= ?', [$this->set->card_count]);
            }

            // ==========================================
            // A INTELIGÊNCIA HÍBRIDA (Cores vs Tipos)
            // ==========================================
            if ($this->cor !== 'todas') {
                
                // Se for Artefato ou Terreno, não precisa olhar pra tabela de Magic, o type_line resolve!
                if ($this->cor === 'A') {
                    $queryIds->where('type_line', 'LIKE', '%Artifact%');
                } 
                elseif ($this->cor === 'L') {
                    $queryIds->where('type_line', 'LIKE', '%Land%');
                } 
                // Se for as Cores de Magic (W, U, B, R, G, Multicolor ou Colorless)
                else {
                    // Fazemos a ponte: catalog_prints -> catalog_concepts -> mtg_concepts
                    $queryIds->join('catalog_concepts as cc', 'catalog_prints.concept_id', '=', 'cc.id')
                             ->join('mtg_concepts as mc', 'cc.specific_id', '=', 'mc.id');

                    if (in_array($this->cor, ['W', 'U', 'B', 'R', 'G'])) {
                        // Carta de cor exata e NÃO multicolorida
                        $queryIds->where('mc.colors', 'LIKE', '%"' . $this->cor . '"%')
                                 ->where('mc.colors', 'NOT LIKE', '%,%');
                    } 
                    elseif ($this->cor === 'M') {
                        // Multicolor: Se tem vírgula no array de cores
                        $queryIds->where('mc.colors', 'LIKE', '%,%');
                    } 
                    elseif ($this->cor === 'C') {
                        // Incolor (Eldrazi, Ugin, etc) -> Sem cor, E não é artefato, E não é terreno
                        $queryIds->where(function($q) {
                            $q->whereNull('mc.colors')
                              ->orWhere('mc.colors', '[]')
                              ->orWhere('mc.colors', '');
                        })
                        ->where('catalog_prints.type_line', 'NOT LIKE', '%Artifact%')
                        ->where('catalog_prints.type_line', 'NOT LIKE', '%Land%');
                    }
                }
            }

            // CORREÇÃO AQUI: Não agrupamos mais por concept_id. Cada Print (arte/número) é uma carta única.
            $printIds = $queryIds->pluck('catalog_prints.id');

            // ==========================================
            // 2. A TABELA VIRTUAL DE ESTOQUE (Performance Absoluta)
            // ==========================================
            $estoqueSubquery = \App\Models\StockItem::select(
                'stock_items.catalog_print_id', // CORREÇÃO AQUI: Agrupando pela carta física, não pelo conceito
                \DB::raw('SUM(stock_items.quantity) as total_estoque'),
                \DB::raw('MIN(CASE WHEN stock_items.quantity > 0 THEN stock_items.price END) as menor_preco'),
                \DB::raw('MIN(stock_items.price) as ultimo_preco'),
                \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.extras END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_extras"),
                \DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(CASE WHEN stock_items.quantity > 0 THEN stock_items.discount_percent END ORDER BY stock_items.price ASC SEPARATOR '|||'), '|||', 1) as menor_preco_desconto")
            )
            ->join('catalog_prints as cp', 'stock_items.catalog_print_id', '=', 'cp.id')
            ->where('stock_items.store_id', $this->loja->id)
            ->where('cp.set_id', $this->set->id)
            ->groupBy('stock_items.catalog_print_id'); // CORREÇÃO AQUI: Agrupando pela carta física

            // ==========================================
            // 3. A QUERY MESTRA
            // ==========================================
            $query = \App\Models\Catalog\CatalogPrint::select(
                    'catalog_prints.*',
                    \DB::raw('COALESCE(estoque.total_estoque, 0) as total_estoque'),
                    'estoque.menor_preco',
                    'estoque.ultimo_preco',
                    'estoque.menor_preco_extras',
                    'estoque.menor_preco_desconto'
                )
                ->selectSub($nomePtSubquery, 'nome_pt_banco')
                ->with(['concept'])
                ->whereIn('catalog_prints.id', $printIds)
                ->leftJoinSub($estoqueSubquery, 'estoque', function ($join) {
                    // CORREÇÃO AQUI: Ligando diretamente pelo Print ID
                    $join->on('catalog_prints.id', '=', 'estoque.catalog_print_id');
                });

            // ==========================================
            // 4. APLICAÇÃO DOS FILTROS (Raridade e Estoque)
            // ==========================================
            if ($this->raridade !== 'todas') {
                $query->where('catalog_prints.rarity', $this->raridade); 
            }

            if ($this->com_estoque) {
                $query->where('total_estoque', '>', 0);
            }

            // ==========================================
            // 5. ORDENAÇÃO DINÂMICA
            // ==========================================
            switch ($this->sortOrder) {
                case 'price_asc':
                    $query->orderByRaw('estoque.menor_preco IS NULL')->orderBy('estoque.menor_preco', 'asc');
                    break;
                case 'price_desc':
                    $query->orderByRaw('estoque.menor_preco IS NULL')->orderBy('estoque.menor_preco', 'desc');
                    break;
                case 'name_asc':
                    $query->orderBy('catalog_prints.printed_name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('catalog_prints.printed_name', 'desc');
                    break;
                case 'number_desc':
                    $query->orderByRaw('CAST(catalog_prints.collector_number AS UNSIGNED) DESC');
                    break;
                case 'number_asc':
                default:
                    $query->orderByRaw('CAST(catalog_prints.collector_number AS UNSIGNED) ASC');
                    break;
            }

            // 6. PAGINAÇÃO
            $cartas = $query->paginate($this->perPage)->onEachSide(0);

            // 7. TRANSFORMANDO DADOS (Blade)
            $cartas->getCollection()->transform(function ($carta) {
                $total = (int) ($carta->total_estoque ?? 0);
                $precoBase = (float) ($carta->menor_preco ?? 0);
                $ultimoPreco = (float) ($carta->ultimo_preco ?? 0);
                $percentualDesconto = (float) ($carta->menor_preco_desconto ?? 0);

                $referenciaParaCalculo = ($total > 0) ? $precoBase : $ultimoPreco;

                $carta->preco_final = ($percentualDesconto > 0) 
                    ? $referenciaParaCalculo * (1 - ($percentualDesconto / 100))
                    : $referenciaParaCalculo;

                $carta->total_estoque = $total;
                $carta->menor_preco = $precoBase;
                $carta->desconto = $percentualDesconto;
                
                $carta->is_foil = (bool) str_contains(strtolower($carta->menor_preco_extras ?? ''), 'foil');
                
                $carta->nome_localizado = $carta->nome_pt_banco ?? $carta->concept->name ?? $carta->printed_name; 
                $carta->name = $carta->concept->name ?? $carta->printed_name;
                
                $imagemBruta = $carta->image_url 
                            ?? $carta->image_path 
                            ?? $carta->concept->image_url 
                            ?? $carta->concept->image_path 
                            ?? 'https://placehold.co/250x350/eeeeee/999999?text=Sem+Imagem';

                $carta->imagem_final = filter_var($imagemBruta, FILTER_VALIDATE_URL) 
                                     ? $imagemBruta 
                                     : asset($imagemBruta);
                
                $carta->foil = false; 
                
                return $carta;
            });
        }

        return view('livewire.store.template.catalog.set-page', [
            'cartas' => $cartas
        ])->layout('layouts.template', ['loja' => $this->loja]);
    }
}