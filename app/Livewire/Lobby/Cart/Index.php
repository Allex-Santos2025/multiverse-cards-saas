<?php

namespace App\Livewire\Lobby\Cart;

use Livewire\Component;
use App\Models\Store;
use App\Models\CartItem;
use App\Models\StoreShippingSetting;
use Illuminate\Support\Facades\Session;

class Index extends Component
{
    // Apenas propriedades simples que precisam persistir
    public $cep = '';
    public $selectedShipping = [];
    public $descontoGeral = 0;
    
    // Contexto
    public $loja = null;
    public $isMarketplace = false;

    // Array para guardar as configurações de frete de cada loja no carrinho
    public $fretesPorLoja = [];

    public function mount($slug = null)
    {
        if ($slug) {
            $this->loja = Store::with('visual')->where('url_slug', $slug)->first();
            if (!$this->loja) abort(404);
            $this->isMarketplace = false;
        } else {
            $this->isMarketplace = true;
        }

        // Descobre qual guard está ativo e puxa o zip_code do endereço oficial do Player
        $playerId = null;
        if (auth('player')->check()) { 
            $playerId = auth('player')->id();
        } elseif (auth()->check()) {
            $playerId = auth()->id();
        }

        if ($playerId) {
            $endereco = \App\Models\PlayerAddress::where('player_user_id', $playerId)
                                ->where('is_official', true)
                                ->first();

            if (!$endereco) {
                $endereco = \App\Models\PlayerAddress::where('player_user_id', $playerId)
                                ->latest()
                                ->first();
            }

            $this->cep = $endereco->zip_code ?? '';
        }
    }

    public function incrementQuantity($itemId)
    {
        $cartItem = CartItem::with('stockItem')->where('session_id', Session::getId())->find($itemId);
        if ($cartItem) {
            $estoqueMaximo = $cartItem->stockItem->quantity ?? 1;
            if ($cartItem->quantity < $estoqueMaximo) {
                $cartItem->increment('quantity');
                $this->dispatch('cart-updated');
            }
        }
    }

    public function decrementQuantity($itemId)
    {
        $cartItem = CartItem::where('session_id', Session::getId())->find($itemId);
        if ($cartItem && $cartItem->quantity > 1) {
            $cartItem->decrement('quantity');
            $this->dispatch('cart-updated');
        }
    }

    public function removeItem($itemId)
    {
        $cartItem = CartItem::where('session_id', Session::getId())->find($itemId);
        if ($cartItem) {
            $cartItem->delete();
            $this->dispatch('cart-updated');
        }
    }

    public function render()
    {
        $sessionId = Session::getId();
        $items = CartItem::with(['stockItem.catalogPrint.concept', 'stockItem.catalogPrint.set', 'stockItem.store']) 
            ->where('session_id', $sessionId)
            ->get();

        if ($this->loja) {
            $items = $items->filter(function ($item) {
                return ($item->stockItem->store->id ?? null) === $this->loja->id;
            });
        }

        $cartByStore = [];
        $totalItems = 0;
        $subtotalGeral = 0;
        $fretesGeral = 0;
        $lojasNoCarrinhoIds = [];

        $items->each(function ($item) use (&$cartByStore, &$totalItems, &$subtotalGeral, &$lojasNoCarrinhoIds) {
            $stock = $item->stockItem;
            $print = $stock->catalogPrint ?? null;
            
            if ($print) {
                $nome = $print->printed_name ?? $print->concept->name ?? 'Carta Desconhecida';
                if (str_contains($print->type_line ?? '', 'Basic Land')) {
                    $nome .= ' (#' . ($print->collector_number ?? '') . ')';
                }
                $item->nome_localizado = $nome;

                $caminhoImagem = $print->image_url ?? $print->image_path ?? $print->concept->image_url ?? $print->concept->image_path ?? 'https://placehold.co/100x140';
                $item->imagem_final = filter_var($caminhoImagem, FILTER_VALIDATE_URL) ? $caminhoImagem : asset($caminhoImagem);
                
                $item->condicao = strtoupper($stock->condition ?? 'NM');
                $item->idioma = strtoupper($stock->language ?? $print->language_code ?? 'PT');
                $item->edicao = strtoupper($print->set->code ?? 'N/A');
                $item->estoque_maximo = $stock->quantity ?? 1;
            }

            $store = $stock->store ?? null;
            $storeId = $store ? $store->id : 0;
            
            if (!isset($cartByStore[$storeId])) {
                $cartByStore[$storeId] = ['store' => $store, 'items' => collect(), 'total' => 0];
                $lojasNoCarrinhoIds[] = $storeId;
            }

            $cartByStore[$storeId]['items']->push($item);
            $cartByStore[$storeId]['total'] += ($item->price * $item->quantity);
            $totalItems += $item->quantity;
            $subtotalGeral += ($item->price * $item->quantity);
        });

        // ==============================================================
        // CÁLCULO DE FRETES (DINÂMICO E REATIVO)
        // ==============================================================
        // Zera as opções para forçar o recálculo do seguro sempre que a qtd de itens mudar
        $this->fretesPorLoja = []; 

        if (!empty($lojasNoCarrinhoIds)) {
            $regrasLojas = StoreShippingSetting::whereIn('store_id', $lojasNoCarrinhoIds)->get()->keyBy('store_id');
            
            foreach ($lojasNoCarrinhoIds as $idLoja) {
                $regrasDaLoja = $regrasLojas->get($idLoja);
                $opcoesDisponiveis = [];

                if ($regrasDaLoja) {
                    
                    // Base de cálculo para o seguro: Valor total dinâmico dos produtos
                    $totalProdutosLoja = $cartByStore[$idLoja]['total'] ?? 0;

                    // 1. RETIRADA
                    if ($regrasDaLoja->is_active_retirada) {
                        $opcoesDisponiveis['retirada'] = [
                            'nome' => $regrasDaLoja->retirada_nome_exibicao,
                            'valor' => 0.00,
                            'descricao' => $regrasDaLoja->retirada_instrucoes
                        ];
                    }
                    
                    // 2. CARTA REGISTRADA
                    if ($regrasDaLoja->is_active_carta_registrada) {
                        $valorFixoCr = (float) $regrasDaLoja->cr_valor_fixo;
                        $valorSeguroCr = 0;
                        
                        if ($regrasDaLoja->cr_taxa_percentual > 0) {
                            $valorSeguroCr = $totalProdutosLoja * ((float) $regrasDaLoja->cr_taxa_percentual / 100);
                        }
                        
                        $valorFinalCr = $valorFixoCr + $valorSeguroCr;

                        $nomeAmigavelCr = $regrasDaLoja->cr_nome_exibicao;
                        if ($valorSeguroCr > 0) {
                            $nomeAmigavelCr .= " (SEGURO: R$ " . number_format($valorSeguroCr, 2, ',', '.') . ")";
                        }

                        $textosExtrasCr = [];
                        if ($regrasDaLoja->cr_prazo_dias > 0) {
                            $textosExtrasCr[] = "Prazo estimado: " . $regrasDaLoja->cr_prazo_dias . " dias úteis.";
                        }
                        
                        $descricaoCompostaCr = $regrasDaLoja->cr_descricao;
                        if (!empty($textosExtrasCr)) {
                            $descricaoCompostaCr .= " — " . implode(' ', $textosExtrasCr);
                        }

                        $opcoesDisponiveis['carta_registrada'] = [
                            'nome' => $nomeAmigavelCr,
                            'valor' => $valorFinalCr,
                            'descricao' => trim($descricaoCompostaCr)
                        ];
                    }

                    // 3. CORREIOS PAC
                    if ($regrasDaLoja->is_active_correios && $regrasDaLoja->correios_pac) {
                        $valorFixoPac = 25.00; // Mock temporário para a API futura
                        $valorSeguroPac = 0;
                        
                        if ($regrasDaLoja->taxa_seguro_percentual > 0) {
                            $valorSeguroPac = $totalProdutosLoja * ((float) $regrasDaLoja->taxa_seguro_percentual / 100);
                        }

                        $valorFinalPac = $valorFixoPac + $valorSeguroPac;

                        $nomeAmigavelPac = $regrasDaLoja->correios_pac_nome_exibicao;
                        if ($valorSeguroPac > 0) {
                            $nomeAmigavelPac .= " (SEGURO: R$ " . number_format($valorSeguroPac, 2, ',', '.') . ")";
                        }

                        $textosExtrasPac = [];
                        if ($regrasDaLoja->prazo_manuseio_dias > 0) {
                            $textosExtrasPac[] = "+ " . $regrasDaLoja->prazo_manuseio_dias . " dia(s) de separação da loja.";
                        }
                        
                        $descricaoCompostaPac = $regrasDaLoja->correios_pac_descricao;
                        if (!empty($textosExtrasPac)) {
                            $descricaoCompostaPac .= " " . implode(' ', $textosExtrasPac);
                        }

                        $opcoesDisponiveis['pac'] = [
                            'nome' => $nomeAmigavelPac,
                            'valor' => $valorFinalPac,
                            'descricao' => trim($descricaoCompostaPac)
                        ];
                    }
                }
                
                $this->fretesPorLoja[$idLoja] = $opcoesDisponiveis;
            }
        }

        // ==============================================================
        // SOMA DOS FRETES SELECIONADOS AO TOTAL
        // ==============================================================
        foreach ($this->selectedShipping as $storeId => $chaveFrete) {
            if (isset($cartByStore[$storeId]) && isset($this->fretesPorLoja[$storeId][$chaveFrete])) {
                $valorFrete = $this->fretesPorLoja[$storeId][$chaveFrete]['valor'];
                
                $fretesGeral += $valorFrete;
                $cartByStore[$storeId]['total'] += $valorFrete;
            }
        }

        $totalGeral = $subtotalGeral + $fretesGeral - $this->descontoGeral;

        $layout = $this->loja ? 'layouts.template' : 'layouts.app';
        
        return view('livewire.lobby.cart.index', [
            'cartByStore' => $cartByStore,
            'totalItems' => $totalItems,
            'subtotalGeral' => $subtotalGeral,
            'fretesGeral' => $fretesGeral,
            'totalGeral' => $totalGeral,
            'fretesPorLoja' => $this->fretesPorLoja
        ])->layout($layout, ['loja' => $this->loja, 'isMarketplace' => $this->isMarketplace]);
    }
}