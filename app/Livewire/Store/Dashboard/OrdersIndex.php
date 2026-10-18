<?php

namespace App\Livewire\Store\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\OrderShipping;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class OrdersIndex extends Component
{
    use WithPagination;

    public $slug;
    public $loja;
    public $tab = 'all'; // all, shipping, pickup, preorder

    // Busca Simples
    public $search = '';

    // Busca Avançada
    public $advancedOpen = false;
    public $filterOrderId = '';
    public $filterClient = '';
    public $filterStatusId = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $filterPaymentMethod = '';
    public $filterShippingMethod = '';
    public $orderBy = 'id';
    public $orderDirection = 'desc';

    protected $queryString = [
        'tab' => ['except' => 'all'],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount($slug = null)
    {
        $this->slug = $slug ?? request()->route('slug');
        if (!$this->slug) {
            $host = request()->getHost();
            $this->slug = explode('.', $host)[0];
        }

        $this->loja = Store::where('url_slug', $this->slug)->first();

        if (request()->has('tab')) {
            $this->tab = request()->query('tab');
        }
    }

    public function setTab($tabName)
    {
        $this->tab = $tabName;
        $this->resetPage();
    }

    public function toggleAdvanced()
    {
        $this->advancedOpen = !$this->advancedOpen;
    }

    public function clearFilters()
    {
        $this->reset([
            'search', 'filterOrderId', 'filterClient', 'filterStatusId',
            'filterDateFrom', 'filterDateTo', 'filterPaymentMethod', 'filterShippingMethod'
        ]);
        $this->resetPage();
    }

    public function render()
    {
        $storeId = $this->loja ? $this->loja->id : null;

        $query = OrderShipping::with(['order.playerUser', 'status'])
            ->where('store_id', $storeId);

        // Filtro por Aba
        if ($this->tab === 'shipping') {
            $query->where('shipping_method_key', '!=', 'retirada')
                  ->whereNotIn('shipping_status', ['delivered', 'canceled'])
                  ->where(function ($q) {
                      $q->whereNull('order_status_id')
                        ->orWhereNotIn('order_status_id', [5, 6]);
                  });
        } elseif ($this->tab === 'pickup') {
            $query->where('shipping_method_key', 'retirada');
        } elseif ($this->tab === 'preorder') {
            $query->whereHas('order.items', function($q) use ($storeId) {
                $q->where('store_id', $storeId)
                  ->where(function($sq) {
                      $sq->where('is_preorder', 1)
                         ->orWhere('is_pre_order', 1);
                  });
            });
        }

        // Busca Simples
        if (!empty($this->search)) {
            $term = trim($this->search);
            $query->where(function($q) use ($term) {
                $cleanId = ltrim($term, '#');
                if (is_numeric($cleanId)) {
                    $q->orWhere('order_id', $cleanId);
                }
                $q->orWhereHas('order.playerUser', function($userQuery) use ($term) {
                    $userQuery->where('name', 'like', "%{$term}%")
                              ->orWhere('email', 'like', "%{$term}%");
                });
                $q->orWhereHas('status', function($statusQuery) use ($term) {
                    $statusQuery->where('name', 'like', "%{$term}%");
                });
            });
        }

        // Filtros da Busca Avançada
        if (!empty($this->filterOrderId)) {
            $query->where('order_id', ltrim($this->filterOrderId, '#'));
        }
        if (!empty($this->filterClient)) {
            $cTerm = $this->filterClient;
            $query->whereHas('order.playerUser', function($q) use ($cTerm) {
                $q->where('name', 'like', "%{$cTerm}%")
                  ->orWhere('email', 'like', "%{$cTerm}%");
            });
        }
        if (!empty($this->filterStatusId)) {
            $query->where('order_status_id', $this->filterStatusId);
        }
        if (!empty($this->filterDateFrom)) {
            $query->whereDate('created_at', '>=', $this->filterDateFrom);
        }
        if (!empty($this->filterDateTo)) {
            $query->whereDate('created_at', '<=', $this->filterDateTo);
        }
        if (!empty($this->filterPaymentMethod)) {
            $query->whereHas('order', function($q) {
                $q->where('payment_method', $this->filterPaymentMethod);
            });
        }
        if (!empty($this->filterShippingMethod)) {
            $query->where('shipping_method_name', 'like', "%{$this->filterShippingMethod}%");
        }

        // Ordenação
        if ($this->orderBy === 'id') {
            $query->orderBy('order_id', $this->orderDirection);
        } else {
            $query->orderBy($this->orderBy, $this->orderDirection);
        }

        $shippings = $query->paginate(20);

        // Mapeia totais e canais de cor
        $shippings->getCollection()->transform(function ($shipping) use ($storeId) {
            $order = $shipping->order;
            $itemsSum = OrderItem::where('store_id', $storeId)
                ->where('order_id', $shipping->order_id)
                ->sum(DB::raw('unit_price * quantity'));
            $totalLoja = $itemsSum + $shipping->shipping_cost;

            $shipping->formatted_id = $order ? $order->id : $shipping->order_id;
            $shipping->formatted_channel = $order ? ($order->channel ?? 'marketplace') : 'marketplace';
            $shipping->formatted_total = $totalLoja;
            $shipping->formatted_status_name = optional($shipping->status)->name ?? ($shipping->shipping_status === 'shipped' ? 'Enviado' : 'Processando');
            $shipping->formatted_status_color = optional($shipping->status)->color ?? '#f97316';
            $shipping->client_name = optional(optional($order)->playerUser)->name ?? 'Cliente Não Identificado';
            $shipping->payment_name = optional($order)->payment_method ?? 'Não informado';

            return $shipping;
        });

        $statuses = OrderStatus::orderBy('name')->get();

        return view('livewire.store.dashboard.orders-index', [
            'shippings' => $shippings,
            'statuses' => $statuses,
            'loja' => $this->loja,
        ])
        ->extends('layouts.dashboard')
        ->section('content');
    }
}
