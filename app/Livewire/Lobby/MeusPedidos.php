<?php

namespace App\Livewire\Lobby;

use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class MeusPedidos extends Component
{
    public $pedidos = [];
    public $filtroAtivo = 'todos';

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
        
        $orders = Order::with([
            'items.store.visual', 
            'items.stockItem.catalogPrint.concept', 
            'items.stockItem.catalogPrint.set',
            'shippings'
        ])
            ->where('player_user_id', $playerId)
            ->orderBy('created_at', 'desc')
            ->get();

        $pedidosFormatados = [];
        
        // Puxamos as opções localizadas do seu Enum para traduzir os extras
        $opcoesExtras = class_exists('\App\Enums\StockExtra') ? \App\Enums\StockExtra::options() : [];

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

                $itensArray = $itens->map(function($item) use ($opcoesExtras) {
                    $stock = $item->stockItem;
                    $print = $stock->catalogPrint ?? null;

                    $nome = $item->item_name;
                    $edicao = 'N/A';
                    $linguagem = 'PT';
                    $condicao = 'NM';
                    $foto = 'https://placehold.co/100x140';
                    
                    $isFoil = false;
                    $extrasFormatados = [];

                    if ($print) {
                        $nome = $print->printed_name ?? $print->concept->name ?? $item->item_name;
                        if (str_contains($print->type_line ?? '', 'Basic
                       
                        Land')) {
                            $nome .= ' (#' . ($print->collector_number ?? '') . ')';
                        }

                        $caminhoImagem = $print->image_url ?? $print->image_path ?? $print->concept->image_url ?? $print->concept->image_path ?? 'https://placehold.co/100x140';
                        $foto = filter_var($caminhoImagem, FILTER_VALIDATE_URL) ? $caminhoImagem : asset($caminhoImagem);
                        
                        $linguagem = $print->language_code ?? $print->language ?? $stock->language ?? 'PT';
                        $condicao = $stock->condition ?? $stock->quality_id ?? 'NM';
                        $edicao = $print->set->name ?? 'N/A'; 

                        // ==========================================
                        // LÓGICA DE EXTRAS COM O ENUM E TRADUÇÕES
                        // ==========================================
                        $isFoil = (bool)($stock->is_foil ?? $stock->foil ?? false);
                        
                        $rawExtras = $stock->extras ?? [];
                        if (is_string($rawExtras)) {
                            $rawExtras = json_decode($rawExtras, true) ?? [$rawExtras];
                        }
                        if (!is_array($rawExtras)) $rawExtras = [];

                        foreach ($rawExtras as $ex) {
                            $val = strtolower(trim($ex));
                            if (in_array($val, ['foil', 'foil_etched', 'etched'])) {
                                $isFoil = true;
                            }

                            $label = $opcoesExtras[$val] ?? ucfirst($val);
                            
                            $extrasFormatados[] = [
                                'key' => $val,
                                'label' => $label
                            ];
                        }

                        // Garante que Foil apareça se for true na coluna mas não estiver no array de extras
                        if ($isFoil && !in_array('foil', array_column($extrasFormatados, 'key')) && !in_array('foil_etched', array_column($extrasFormatados, 'key'))) {
                            $extrasFormatados[] = [
                                'key' => 'foil',
                                'label' => $opcoesExtras['foil'] ?? 'Foil'
                            ];
                        }
                    }

                    return [
                        'qtd' => $item->quantity,
                        'nome' => $nome,
                        'edicao' => $edicao, 
                        'linguagem' => strtoupper(substr($linguagem, 0, 2)), 
                        'condicao' => strtoupper($condicao), 
                        'preco' => $item->unit_price,
                        'foto' => $foto,
                        'is_foil' => $isFoil,
                        'extras' => $extrasFormatados
                    ];
                })->toArray();

                $slug = $loja->url_slug ?? '';
                $avatarFile = $visual->avatar_marketplace ?? $visual->favicon ?? null;
                $avatarUrl = $avatarFile ? asset("store_images/{$slug}/{$avatarFile}") : null;
                $logoFile = $visual->logo_marketplace ?? $visual->logo_main ?? null;
                $logoUrl = $logoFile ? asset("store_images/{$slug}/{$logoFile}") : null;
                $corPrimariaHex = $visual->color_primary ?? '#0f172a';

                $pedidosFormatados[] = [
                    'id' => $order->id . '-' . $storeId,
                    'loja' => $loja->name ?? 'Loja Desconhecida',
                    'loja_cor_hex' => $corPrimariaHex,
                    'loja_avatar' => $avatarUrl,
                    'loja_logo' => $logoUrl,
                    'loja_sigla' => strtoupper(substr($loja->name ?? 'TCG', 0, 2)),
                    'codigo' => '#' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
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