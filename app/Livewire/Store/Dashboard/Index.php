<?php

namespace App\Livewire\Store\Dashboard;

use Livewire\Component;
use App\Models\Store;
use App\Models\StoreStockSnapshot;
use App\Models\OrderShipping;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    public $slug;
    public $loja;

    public function mount($slug)
    {
        $this->slug = $slug;
        $this->loja = Store::where('url_slug', $slug)->firstOrFail();
    }

    public function render()
    {
        $storeId = $this->loja->id;

        // 1. CARDS OPERACIONAIS: PENDÊNCIAS FÍSICAS (exclui delivered e canceled)
        // IDs dos status finais: 5 = delivered, 6 = canceled
        $basePendingQuery = OrderShipping::with('order')
            ->where('store_id', $storeId)
            ->whereNotIn('shipping_status', ['delivered', 'canceled'])
            ->where(function ($q) {
                $q->whereNull('order_status_id')
                  ->orWhereNotIn('order_status_id', [5, 6]);
            });

        // Card 1: Aguardando Envio (qualquer frete que NÃO seja retirada)
        $pendingShippings = (clone $basePendingQuery)
            ->where('shipping_method_key', '!=', 'retirada')
            ->get();

        $shippingCount = $pendingShippings->count();
        $shippingMktCount = $pendingShippings->filter(fn($s) => optional($s->order)->channel === 'marketplace')->count();
        $shippingStoreCount = $pendingShippings->filter(fn($s) => optional($s->order)->channel !== 'marketplace')->count();
        
        // Soma do valor dos itens pertencentes a essa loja nestes pedidos pendentes + o custo do frete
        $pendingShippingOrderIds = $pendingShippings->pluck('order_id')->unique();
        $shippingItemsTotal = OrderItem::where('store_id', $storeId)->whereIn('order_id', $pendingShippingOrderIds)->sum(DB::raw('unit_price * quantity'));
        $shippingFreightTotal = $pendingShippings->sum('shipping_cost');
        $shippingTotal = $shippingItemsTotal + $shippingFreightTotal;

        // Card 2: Retirada Balcão (apenas método 'retirada')
        $pendingPickups = (clone $basePendingQuery)
            ->where('shipping_method_key', 'retirada')
            ->get();

        $pickupCount = $pendingPickups->count();
        $pendingPickupOrderIds = $pendingPickups->pluck('order_id')->unique();
        $pickupTotal = OrderItem::where('store_id', $storeId)->whereIn('order_id', $pendingPickupOrderIds)->sum(DB::raw('unit_price * quantity'));

        // 2. DESEMPENHO DE VENDAS (ÚLTIMOS 7 DIAS)
        $chartCategories = [];
        $chartMarketplace = [];
        $chartStore = [];

        $diasSemanaMap = [
            'Monday' => 'Seg', 'Tuesday' => 'Ter', 'Wednesday' => 'Qua',
            'Thursday' => 'Qui', 'Friday' => 'Sex', 'Saturday' => 'Sab', 'Sunday' => 'Dom'
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayName = $diasSemanaMap[$date->format('l')] ?? $date->format('D');
            $chartCategories[] = $dayName;

            // Busca os shippings da loja criados neste dia com seus respectivos canais
            $shippingsDay = OrderShipping::with('order')
                ->where('store_id', $storeId)
                ->whereDate('created_at', $date->toDateString())
                ->get();

            $mktShippings = $shippingsDay->filter(fn($s) => optional($s->order)->channel === 'marketplace');
            $strShippings = $shippingsDay->filter(fn($s) => optional($s->order)->channel !== 'marketplace');

            // Contagem de pedidos por dia por canal
            $chartMarketplace[] = $mktShippings->pluck('order_id')->unique()->count();
            $chartStore[] = $strShippings->pluck('order_id')->unique()->count();
        }

        // 3. ÚLTIMOS PEDIDOS (TABELA LATERAL - TOP 5)
        $recentOrders = OrderShipping::with(['order.playerUser', 'status'])
            ->where('store_id', $storeId)
            ->latest('id')
            ->take(10)
            ->get()
            ->map(function ($shipping) use ($storeId) {
                $order = $shipping->order;
                $itemsSum = OrderItem::where('store_id', $storeId)->where('order_id', $shipping->order_id)->sum(DB::raw('unit_price * quantity'));
                $totalLoja = $itemsSum + $shipping->shipping_cost;

                return (object)[
                    'id' => $order ? $order->id : $shipping->id,
                    'channel' => $order ? ($order->channel ?? 'marketplace') : 'marketplace',
                    'created_at' => $shipping->created_at,
                    'total' => $totalLoja,
                    'status_name' => optional($shipping->status)->name ?? ($shipping->shipping_status === 'shipped' ? 'Enviado' : 'Processando'),
                    'status_color' => optional($shipping->status)->color ?? '#f97316',
                ];
            });

        // 4. EVOLUÇÃO DO ESTOQUE (14 DIAS)
        $snapshots = StoreStockSnapshot::where('store_id', $this->loja->id)
            ->orderBy('snapshot_date', 'desc')
            ->take(14)
            ->get()
            ->reverse()
            ->values();

        return view('livewire.store.dashboard.index', [
            'snapshots' => $snapshots,
            'shippingCount' => $shippingCount,
            'shippingTotal' => $shippingTotal,
            'shippingMktCount' => $shippingMktCount,
            'shippingStoreCount' => $shippingStoreCount,
            'pickupCount' => $pickupCount,
            'pickupTotal' => $pickupTotal,
            'chartCategories' => $chartCategories,
            'chartMarketplace' => $chartMarketplace,
            'chartStore' => $chartStore,
            'recentOrders' => $recentOrders,
        ])
        ->extends('layouts.dashboard')
        ->section('content');
    }
}
