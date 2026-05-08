<?php

namespace App\Livewire\Lobby;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class MeusPedidos extends Component
{
    public $filtroAtivo = 'todos';
    public $pedidos = [];

    public function mount()
    {
        $this->carregarPedidos();
    }

    public function setFiltro($filtro)
    {
        $this->filtroAtivo = $filtro;
        $this->carregarPedidos();
    }

    public function carregarPedidos()
    {
        $playerId = Auth::guard('player')->id();
        
        // Eager load atualizado: agora puxa a loja e o 'visual' dela junto
        $orders = Order::with(['items.store.visual', 'shippings'])
            ->where('player_user_id', $playerId)
            ->orderBy('created_at', 'desc')
            ->get();

        $pedidosFormatados = [];

        foreach ($orders as $order) {
            $itensPorLoja = $order->items->groupBy('store_id');
            
            foreach ($itensPorLoja as $storeId => $itens) {
                $loja = $itens->first()->store;
                $visual = $loja->visual ?? null;
                $shipping = $order->shippings->where('store_id', $storeId)->first();
                
                $statusEnvio = $shipping ? $shipping->shipping_status : 'pending';
                
                if ($this->filtroAtivo === 'caminho' && $statusEnvio !== 'shipped') continue;
                if ($this->filtroAtivo === 'entregues' && $statusEnvio !== 'delivered') continue;

                if ($statusEnvio === 'shipped') {
                    $statusTexto = 'A Caminho';
                    $statusCor = 'text-blue-600';
                    $barraCor = 'bg-blue-500';
                    $progresso = '70%';
                } elseif ($statusEnvio === 'delivered') {
                    $statusTexto = 'Entregue';
                    $statusCor = 'text-emerald-600';
                    $barraCor = 'bg-emerald-500';
                    $progresso = '100%';
                } else { 
                    $statusTexto = 'Separando Pedido';
                    $statusCor = 'text-orange-500';
                    $barraCor = 'bg-orange-400';
                    $progresso = '30%';
                }

                $freteTotal = $shipping ? (float) $shipping->shipping_cost : 0;
                $totalLoja = $itens->sum(function($item) {
                    return $item->unit_price * $item->quantity;
                }) + $freteTotal;

                $itensArray = $itens->map(function($item) {
                    return [
                        'qtd' => $item->quantity,
                        'nome' => $item->item_name,
                        'edicao' => '-', 
                        'condicao' => '-', 
                        'preco' => $item->unit_price
                    ];
                })->toArray();

                // --- LÓGICA DE IDENTIDADE VISUAL ---
                $slug = $loja->url_slug ?? '';
                
                // Puxa o Avatar Quadrado (Prioridade)
                $avatarFile = $visual->avatar_marketplace ?? $visual->favicon ?? null;
                $avatarUrl = $avatarFile ? asset("store_images/{$slug}/{$avatarFile}") : null;

                // Puxa a Logo Retangular
                $logoFile = $visual->logo_marketplace ?? $visual->logo_main ?? null;
                $logoUrl = $logoFile ? asset("store_images/{$slug}/{$logoFile}") : null;

                // Cor Primária da Loja (Fallback para slate-900 se não existir)
                $corPrimariaHex = $visual->color_primary ?? '#0f172a';

                $pedidosFormatados[] = [
                    'id' => $order->id . '-' . $storeId,
                    'loja' => $loja->name ?? 'Loja Desconhecida',
                    'loja_cor_hex' => $corPrimariaHex, // Usa a cor real da loja
                    'loja_avatar' => $avatarUrl,
                    'loja_logo' => $logoUrl,
                    'loja_sigla' => strtoupper(substr($loja->name ?? 'TCG', 0, 2)), // Pega 2 letras pra caber bem
                    'codigo' => '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT), // Código limpo
                    'data' => $order->created_at->format('d/m/Y'),
                    'status_texto' => $statusTexto,
                    'status_cor' => $statusCor,
                    'barra_cor' => $barraCor,
                    'progresso' => $progresso,
                    'info_extra_1' => $shipping ? 'Frete: ' . $shipping->shipping_method_name : '',
                    'info_extra_2' => ($shipping && $shipping->tracking_code) ? 'Rastreio: ' . $shipping->tracking_code : '',
                    'total' => $totalLoja,
                    'qtd_itens' => $itens->sum('quantity'),
                    'itens' => $itensArray
                ];
            }
        }

        $this->pedidos = $pedidosFormatados;
    }

    public function render()
    {
        return view('livewire.lobby.meus-pedidos');
    }
}