<?php

namespace App\Livewire\Store\Dashboard;

use Livewire\Component;
use App\Models\Store;
use App\Models\StockItem;
use App\Models\StoreStockSnapshot;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

class Index extends Component
{
    // 1. DECLARAÇÃO DAS VARIÁVEIS
    public $slug;
    public $loja;
    public $gameSlug;
    public $inactiveSlug;

    // 2. MOUNT
    public function mount($slug)
    {
        $this->slug = $slug;
        $this->loja = Store::where('url_slug', $slug)->firstOrFail();
        $this->gameSlug = request()->query('game_slug', 'magic');
        $this->inactiveSlug = ($this->gameSlug === 'magic') ? 'pokemon' : 'magic';
    }

    // 3. A FUNÇÃO DO BOTÃO "Atualizar Gráficos"
    public function refreshStockData()
    {
        // A. Calcula Totais Gerais
        $stats = StockItem::where('store_id', $this->loja->id)
            ->selectRaw('SUM(quantity) as total_qty, SUM(quantity * COALESCE(price, 0)) as total_val')
            ->first();

        // B. Calcula Porcentagem por Jogo (Buscando Quantidade E Valor para o novo JS)
        $breakdown = StockItem::join('catalog_prints as cp', 'stock_items.catalog_print_id', '=', 'cp.id')
            ->join('catalog_concepts as cc', 'cp.concept_id', '=', 'cc.id')
            ->join('games as g', 'cc.game_id', '=', 'g.id')
            ->where('stock_items.store_id', $this->loja->id)
            ->selectRaw('g.name as game_name, SUM(stock_items.quantity) as qty, SUM(stock_items.quantity * COALESCE(stock_items.price, 0)) as value')
            ->groupBy('g.name')
            ->get()
            ->keyBy('game_name')
            ->toArray();

        // C. Tira a fotografia de hoje FORÇANDO a gravação do array (ignora bloqueios do Model)
        $snapshot = StoreStockSnapshot::firstOrNew([
            'store_id' => $this->loja->id, 
            'snapshot_date' => now()->format('Y-m-d')
        ]);
        
        $snapshot->total_items = $stats->total_qty ?? 0;
        $snapshot->total_value = $stats->total_val ?? 0;
        $snapshot->game_breakdown = $breakdown;
        $snapshot->save();

        return redirect()->route('store.dashboard', ['slug' => $this->slug]);
    }

    public function render()
    {
        // Pega os 14 registros mais recentes, invertidos para o gráfico
        $snapshots = StoreStockSnapshot::where('store_id', $this->loja->id)
            ->orderBy('snapshot_date', 'desc')
            ->take(14)
            ->get()
            ->reverse()
            ->values();

        return view('livewire.store.dashboard.index', [
            'snapshots' => $snapshots
        ])
        ->extends('layouts.dashboard') 
        ->section('content'); 
    }
}