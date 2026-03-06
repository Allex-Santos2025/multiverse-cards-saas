<?php 

namespace App\Livewire\Store\Dashboard\Stock;

use Livewire\Component;
use App\Models\StockItem;
use Illuminate\Support\Facades\DB;

class InventoryExport extends Component
{
    public $exportSet = 'all';
    public $groupCards = 'no';
    public $exportSort = 'name';

    public $availableSets = [];

    public function mount()
    {
        $storeId = auth('store_user')->user()->current_store_id;

        $setIds = \App\Models\StockItem::where('store_id', $storeId)
            ->join('catalog_prints', 'stock_items.catalog_print_id', '=', 'catalog_prints.id')
            ->select('catalog_prints.set_id')
            ->distinct()
            ->pluck('set_id');

        $this->availableSets = \App\Models\Set::whereIn('id', $setIds)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
    }

    public function processExport()
    {
        $storeId = auth('store_user')->user()->current_store_id;

        // Mantemos a extensão .csv ou .txt, mas o conteúdo será texto corrido perfeito
        $fileName = $this->groupCards === 'yes'
            ? 'estoque_agrupado_' . date('Y-m-d_H-i') . '.txt'
            : 'estoque_detalhado_' . date('Y-m-d_H-i') . '.txt';

        return response()->streamDownload(function () use ($storeId) {
            $file = fopen('php://output', 'w');
            
            // Força o formato UTF-8 para não bugar acentos
            fputs($file, "\xEF\xBB\xBF");

            // ==========================================
            // OPÇÃO 1: MODO DETALHADO (Backup Completo)
            // ==========================================
            if ($this->groupCards === 'no') {
                
                $query = StockItem::with(['catalogPrint.set', 'catalogPrint.concept'])
                            ->where('store_id', $storeId);

                if ($this->exportSet !== 'all') {
                    $query->whereHas('catalogPrint', function($q) {
                        $q->where('set_id', $this->exportSet);
                    });
                }

                if ($this->exportSort === 'price') {
                    $query->orderBy('price', 'desc');
                } else {
                    $query->latest(); 
                }

                $query->chunk(500, function ($items) use ($file) {
                    foreach ($items as $item) {
                        // Monta os extras apenas se existirem (ex: "(Foil, Promo)")
                        $extrasStr = '';
                        if (!empty($item->extras) && is_array($item->extras)) {
                            $extrasStr = '(' . implode(', ', array_map('ucfirst', $item->extras)) . ')';
                        }

                        $cardName = $item->catalogPrint->printed_name ?? $item->catalogPrint->concept->name ?? 'Desconhecido';
                        $setCode = '[' . strtoupper($item->catalogPrint->set->code ?? 'UNK') . ']';
                        $cond = strtoupper($item->condition ?? 'NM');
                        $lang = strtoupper($item->language ?? 'EN');
                        $price = number_format($item->price, 2, '.', '');

                        // Monta a linha exata que a Regex do Import espera
                        $lineParts = [
                            $item->quantity,
                            $cardName,
                            $setCode,
                            $cond,
                            $lang
                        ];
                        
                        if (!empty($extrasStr)) {
                            $lineParts[] = $extrasStr;
                        }
                        
                        $lineParts[] = $price;

                        // Escreve a linha separada por espaços e pula de linha
                        fputs($file, implode(' ', $lineParts) . "\n");
                    }
                });

            } 
            // ==========================================
            // OPÇÃO 2: MODO AGRUPADO (Para Balanço)
            // ==========================================
            else {
                $query = StockItem::with(['catalogPrint.set', 'catalogPrint.concept'])
                            ->select('catalog_print_id', 'language', DB::raw('SUM(quantity) as total_quantity'))
                            ->where('store_id', $storeId)
                            ->groupBy('catalog_print_id', 'language');

                if ($this->exportSet !== 'all') {
                    $query->whereHas('catalogPrint', function($q) {
                        $q->where('set_id', $this->exportSet);
                    });
                }

                $query->chunk(500, function ($items) use ($file) {
                    foreach ($items as $item) {
                        $cardName = $item->catalogPrint->printed_name ?? $item->catalogPrint->concept->name ?? 'Desconhecido';
                        $setCode = '[' . strtoupper($item->catalogPrint->set->code ?? 'UNK') . ']';
                        $lang = strtoupper($item->language ?? 'EN');

                        // No modo agrupado, fixamos NM e 0.00 para que a Regex aceite a linha
                        $lineParts = [
                            $item->total_quantity,
                            $cardName,
                            $setCode,
                            'NM',
                            $lang,
                            '0.00'
                        ];

                        fputs($file, implode(' ', $lineParts) . "\n");
                    }
                });
            }

            fclose($file);
        }, $fileName, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function render()
    {
        return view('livewire.store.dashboard.stock.inventory-export');
    }
}