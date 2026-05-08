<?php

namespace App\Livewire\Lobby\Cart;

use Livewire\Component;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShipping;
use App\Models\CartItem;
use App\Models\Store;
use App\Models\StoreShippingSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class Checkout extends Component
{
    public $loja = null;
    public $isMarketplace = false;

    public $nome = '';
    public $email = '';
    public $cpf = '';

    public $enderecos = [];
    public $enderecoSelecionadoId = null;
    public $cepDestino = '';
    public $fretesPorLoja = [];
    public $selectedShipping = []; 

    public $cartByStore = [];
    public $subtotal = 0;
    public $shippingTotal = 0;
    public $totalGeral = 0;

    public $qrCodeBase64 = null;
    public $qrCodeCopiaCola = null;
    public $pedidoFinalizado = false;

    public function mount($slug = null)
    {
        if ($slug) {
            $this->loja = Store::with('visual')->where('url_slug', $slug)->first();
            if (!$this->loja) abort(404);
            $this->isMarketplace = false;
        } else {
            $this->isMarketplace = true;
        }

        $player = Auth::guard('player')->user();

        if ($player) {
            $this->nome  = trim($player->name . ' ' . $player->surname);
            $this->email = $player->email;
            $this->cpf   = $player->document_number;

            $this->enderecos = $player->addresses()->orderByDesc('is_official')->get();
            $this->selectedShipping = Session::get('checkout_shipping', []);

            if ($this->enderecos->isNotEmpty()) {
                $this->selecionarEndereco($this->enderecos->first()->id); 
            } else {
                $this->calcularFretes();
            }
        }
    }

    public function selecionarEndereco($id)
    {
        $this->enderecoSelecionadoId = $id;
        $end = $this->enderecos->firstWhere('id', $id);
        
        if ($end) {
            $this->cepDestino = $end->zip_code;
            $this->calcularFretes(); 
        }
    }

    public function irParaCadastroEndereco()
    {
        Session::put('redirect_after_address', request()->header('referer'));
        
        $params = ['secao' => 'enderecos'];

        if ($this->loja) {
            $params['slug'] = $this->loja->url_slug;
            if (Route::has('store.lobby.index')) {
                return redirect()->route('store.lobby.index', $params);
            }
        }

        return redirect()->route('lobby.index', $params);
    }

    public function calcularFretes()
    {
        $sessionId = Session::getId();
        $items = CartItem::with(['stockItem.catalogPrint.concept', 'stockItem.catalogPrint.set', 'stockItem.store'])
            ->where('session_id', $sessionId)
            ->get();

        if ($this->loja) {
            $items = $items->filter(fn($item) => ($item->stockItem->store->id ?? null) === $this->loja->id);
        }

        $this->cartByStore = [];
        $lojasNoCarrinhoIds = [];
        $this->subtotal = 0;

        foreach ($items as $item) {
            $storeId = $item->stockItem->store->id ?? 0;

            if (!isset($this->cartByStore[$storeId])) {
                $this->cartByStore[$storeId] = ['items' => collect(), 'total' => 0];
                $lojasNoCarrinhoIds[] = $storeId;
            }

            $this->cartByStore[$storeId]['items']->push($item);
            $valorItem = ($item->price * $item->quantity);
            $this->cartByStore[$storeId]['total'] += $valorItem;
            $this->subtotal += $valorItem;
        }

        $this->fretesPorLoja = [];

        if (!empty($lojasNoCarrinhoIds) && !empty($this->cepDestino)) {
            $regrasLojas = StoreShippingSetting::whereIn('store_id', $lojasNoCarrinhoIds)->get()->keyBy('store_id');

            foreach ($lojasNoCarrinhoIds as $idLoja) {
                $regrasDaLoja = $regrasLojas->get($idLoja);
                $opcoesDisponiveis = [];

                if ($regrasDaLoja) {
                    $totalProdutosLoja = $this->cartByStore[$idLoja]['total'] ?? 0;
                    $totalCardsLoja = $this->cartByStore[$idLoja]['items']->sum('quantity');

                    // 1. RETIRADA
                    if ($regrasDaLoja->is_active_retirada) {
                        $opcoesDisponiveis['retirada'] = [
                            'nome' => $regrasDaLoja->retirada_nome_exibicao,
                            'valor' => 0.00,
                            'descricao' => $regrasDaLoja->retirada_instrucoes
                        ];
                    }

                    // 2. CARTA REGISTRADA
                    if ($regrasDaLoja->is_active_carta_registrada && $totalCardsLoja <= $regrasDaLoja->cr_limite_cartas) {
                        $valorFixoCr = (float) $regrasDaLoja->cr_valor_fixo;
                        $valorSeguroCr = 0;
                        
                        $percentualSeguroCr = $regrasDaLoja->is_active_correios 
                            ? (float) $regrasDaLoja->taxa_seguro_percentual 
                            : (float) $regrasDaLoja->cr_taxa_percentual;

                        if ($percentualSeguroCr > 0) {
                            $valorSeguroCr = $totalProdutosLoja * ($percentualSeguroCr / 100);
                        }
                        
                        $valorFinalCr = $valorFixoCr + $valorSeguroCr;

                        $nomeAmigavelCr = $regrasDaLoja->cr_nome_exibicao;
                        if ($valorSeguroCr > 0) {
                            $nomeAmigavelCr .= " (SEGURO: R$ " . number_format($valorSeguroCr, 2, ',', '.') . ")";
                        }

                        $textosExtrasCr = [];
                        if ($regrasDaLoja->cr_prazo_dias > 0) {
                            $prazoTotalCr = $regrasDaLoja->cr_prazo_dias + $regrasDaLoja->prazo_manuseio_dias;
                            $textosExtrasCr[] = "Prazo estimado: " . $prazoTotalCr . " dias úteis.";
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

                    // 3. MOTOBOY
                    if ($regrasDaLoja->is_active_motoboy) {
                        $valorFixoMotoboy = (float) $regrasDaLoja->motoboy_valor_fixo;
                        $valorSeguroMotoboy = 0;
                        
                        if ($regrasDaLoja->motoboy_taxa_percentual > 0) {
                            $valorSeguroMotoboy = $totalProdutosLoja * ((float) $regrasDaLoja->motoboy_taxa_percentual / 100);
                        }
                        
                        $valorFinalMotoboy = $valorFixoMotoboy + $valorSeguroMotoboy;

                        $nomeAmigavelMotoboy = $regrasDaLoja->motoboy_nome_exibicao;
                        if ($valorSeguroMotoboy > 0) {
                            $nomeAmigavelMotoboy .= " (SEGURO: R$ " . number_format($valorSeguroMotoboy, 2, ',', '.') . ")";
                        }

                        $opcoesDisponiveis['motoboy'] = [
                            'nome' => $nomeAmigavelMotoboy,
                            'valor' => $valorFinalMotoboy,
                            'descricao' => $regrasDaLoja->motoboy_descricao
                        ];
                    }

                    // 4. UBER FLASH
                    if ($regrasDaLoja->is_active_uber_flash) {
                        $opcoesDisponiveis['uber_flash'] = [
                            'nome' => $regrasDaLoja->uber_flash_nome_exibicao,
                            'valor' => 0.00,
                            'descricao' => $regrasDaLoja->uber_flash_instrucoes
                        ];
                    }

                    // 5. CORREIOS PAC
                    if ($regrasDaLoja->is_active_correios && $regrasDaLoja->correios_pac) {
                        $valorFixoPac = 25.00; // Mock
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

                    // 6. CORREIOS SEDEX
                    if ($regrasDaLoja->is_active_correios && $regrasDaLoja->correios_sedex) {
                        $valorFixoSedex = 35.00; // Mock
                        $valorSeguroSedex = 0;
                        
                        if ($regrasDaLoja->taxa_seguro_percentual > 0) {
                            $valorSeguroSedex = $totalProdutosLoja * ((float) $regrasDaLoja->taxa_seguro_percentual / 100);
                        }

                        $valorFinalSedex = $valorFixoSedex + $valorSeguroSedex;

                        $nomeAmigavelSedex = $regrasDaLoja->correios_sedex_nome_exibicao;
                        if ($valorSeguroSedex > 0) {
                            $nomeAmigavelSedex .= " (SEGURO: R$ " . number_format($valorSeguroSedex, 2, ',', '.') . ")";
                        }

                        $textosExtrasSedex = [];
                        if ($regrasDaLoja->prazo_manuseio_dias > 0) {
                            $textosExtrasSedex[] = "+ " . $regrasDaLoja->prazo_manuseio_dias . " dia(s) de separação da loja.";
                        }
                        
                        $descricaoCompostaSedex = $regrasDaLoja->correios_sedex_descricao;
                        if (!empty($textosExtrasSedex)) {
                            $descricaoCompostaSedex .= " " . implode(' ', $textosExtrasSedex);
                        }

                        $opcoesDisponiveis['sedex'] = [
                            'nome' => $nomeAmigavelSedex,
                            'valor' => $valorFinalSedex,
                            'descricao' => trim($descricaoCompostaSedex)
                        ];
                    }

                    // 7. CORREIOS SEDEX 10
                    if ($regrasDaLoja->is_active_correios && $regrasDaLoja->correios_sedex10) {
                        $valorFixoSedex10 = 55.00; // Mock
                        $valorSeguroSedex10 = 0;
                        
                        if ($regrasDaLoja->taxa_seguro_percentual > 0) {
                            $valorSeguroSedex10 = $totalProdutosLoja * ((float) $regrasDaLoja->taxa_seguro_percentual / 100);
                        }

                        $valorFinalSedex10 = $valorFixoSedex10 + $valorSeguroSedex10;

                        $nomeAmigavelSedex10 = $regrasDaLoja->correios_sedex10_nome_exibicao;
                        if ($valorSeguroSedex10 > 0) {
                            $nomeAmigavelSedex10 .= " (SEGURO: R$ " . number_format($valorSeguroSedex10, 2, ',', '.') . ")";
                        }

                        $textosExtrasSedex10 = [];
                        if ($regrasDaLoja->prazo_manuseio_dias > 0) {
                            $textosExtrasSedex10[] = "+ " . $regrasDaLoja->prazo_manuseio_dias . " dia(s) de separação da loja.";
                        }
                        
                        $descricaoCompostaSedex10 = $regrasDaLoja->correios_sedex10_descricao;
                        if (!empty($textosExtrasSedex10)) {
                            $descricaoCompostaSedex10 .= " " . implode(' ', $textosExtrasSedex10);
                        }

                        $opcoesDisponiveis['sedex10'] = [
                            'nome' => $nomeAmigavelSedex10,
                            'valor' => $valorFinalSedex10,
                            'descricao' => trim($descricaoCompostaSedex10)
                        ];
                    }

                    // 8. CORREIOS MINI ENVIOS
                    if ($regrasDaLoja->is_active_correios && $regrasDaLoja->correios_mini_envios) {
                        $valorFixoMini = 18.00; // Mock
                        $valorSeguroMini = 0;
                        
                        if ($regrasDaLoja->taxa_seguro_percentual > 0) {
                            $valorSeguroMini = $totalProdutosLoja * ((float) $regrasDaLoja->taxa_seguro_percentual / 100);
                        }

                        $valorFinalMini = $valorFixoMini + $valorSeguroMini;

                        $nomeAmigavelMini = $regrasDaLoja->correios_mini_envios_nome_exibicao;
                        if ($valorSeguroMini > 0) {
                            $nomeAmigavelMini .= " (SEGURO: R$ " . number_format($valorSeguroMini, 2, ',', '.') . ")";
                        }

                        $textosExtrasMini = [];
                        if ($regrasDaLoja->prazo_manuseio_dias > 0) {
                            $textosExtrasMini[] = "+ " . $regrasDaLoja->prazo_manuseio_dias . " dia(s) de separação da loja.";
                        }
                        
                        $descricaoCompostaMini = $regrasDaLoja->correios_mini_envios_descricao;
                        if (!empty($textosExtrasMini)) {
                            $descricaoCompostaMini .= " " . implode(' ', $textosExtrasMini);
                        }

                        $opcoesDisponiveis['mini_envios'] = [
                            'nome' => $nomeAmigavelMini,
                            'valor' => $valorFinalMini,
                            'descricao' => trim($descricaoCompostaMini)
                        ];
                    }

                    // 9. IMPRESSO MÓDICO
                    if ($regrasDaLoja->is_active_correios && $regrasDaLoja->correios_impresso_modico) {
                        $valorFixoImpresso = 12.00; // Mock
                        $valorSeguroImpresso = 0;
                        
                        if ($regrasDaLoja->taxa_seguro_percentual > 0) {
                            $valorSeguroImpresso = $totalProdutosLoja * ((float) $regrasDaLoja->taxa_seguro_percentual / 100);
                        }

                        $valorFinalImpresso = $valorFixoImpresso + $valorSeguroImpresso;

                        $nomeAmigavelImpresso = $regrasDaLoja->correios_impresso_modico_nome_exibicao;
                        if ($valorSeguroImpresso > 0) {
                            $nomeAmigavelImpresso .= " (SEGURO: R$ " . number_format($valorSeguroImpresso, 2, ',', '.') . ")";
                        }

                        $textosExtrasImpresso = [];
                        if ($regrasDaLoja->prazo_manuseio_dias > 0) {
                            $textosExtrasImpresso[] = "+ " . $regrasDaLoja->prazo_manuseio_dias . " dia(s) de separação da loja.";
                        }
                        
                        $descricaoCompostaImpresso = $regrasDaLoja->correios_impresso_modico_descricao;
                        if (!empty($textosExtrasImpresso)) {
                            $descricaoCompostaImpresso .= " " . implode(' ', $textosExtrasImpresso);
                        }

                        $opcoesDisponiveis['impresso_modico'] = [
                            'nome' => $nomeAmigavelImpresso,
                            'valor' => $valorFinalImpresso,
                            'descricao' => trim($descricaoCompostaImpresso)
                        ];
                    }
                }

                $this->fretesPorLoja[$idLoja] = $opcoesDisponiveis;
                
                if (isset($this->selectedShipping[$idLoja])) {
                    $chave = $this->selectedShipping[$idLoja];
                    if (!array_key_exists($chave, $opcoesDisponiveis) && !empty($opcoesDisponiveis)) {
                        $this->selectedShipping[$idLoja] = array_key_first($opcoesDisponiveis);
                    }
                } else if (!empty($opcoesDisponiveis)) {
                    $this->selectedShipping[$idLoja] = array_key_first($opcoesDisponiveis);
                }
            }
        }

        $this->atualizarTotais();
    }

    public function updatedSelectedShipping()
    {
        $this->atualizarTotais();
    }

    public function atualizarTotais()
    {
        $this->shippingTotal = 0;

        foreach ($this->selectedShipping as $storeId => $chaveFrete) {
            if (isset($this->fretesPorLoja[$storeId][$chaveFrete])) {
                $this->shippingTotal += $this->fretesPorLoja[$storeId][$chaveFrete]['valor'];
            }
        }

        $this->totalGeral = $this->subtotal + $this->shippingTotal;
        Session::put('checkout_shipping', $this->selectedShipping);
    }

    public function processarPagamento()
    {
        $this->validate([
            'nome' => 'required', 'email' => 'required|email', 'cpf' => 'required',
            'enderecoSelecionadoId' => 'required', 'selectedShipping' => 'required|array|min:1'
        ]);

        DB::beginTransaction();
        try {
            // 1. CRIAÇÃO DO PEDIDO (ORDER)
            $order = Order::create([
                'player_user_id' => Auth::guard('player')->id(),
                'total_amount' => $this->totalGeral,
                'payment_method' => 'pix',
                'payment_status' => 'pending', 
                'gateway_transaction_id' => 'SIMULADO_' . strtoupper(uniqid()),
            ]);

            // 2. ITENS DO PEDIDO (ORDERITEM)
            foreach ($this->cartByStore as $storeId => $dadosLoja) {
                foreach ($dadosLoja['items'] as $cartItem) {
                    
                    // Monta o nome do item com fallback de segurança
                    $itemName = 'Carta Desconhecida';
                    if (isset($cartItem->nome_localizado) && !empty($cartItem->nome_localizado)) {
                        $itemName = $cartItem->nome_localizado;
                    } elseif (isset($cartItem->stockItem->catalogPrint->printed_name)) {
                        $itemName = $cartItem->stockItem->catalogPrint->printed_name;
                    } elseif (isset($cartItem->stockItem->catalogPrint->concept->name)) {
                        $itemName = $cartItem->stockItem->catalogPrint->concept->name;
                    }

                    // A MÁGICA ACONTECE AQUI: Garantindo que item_name e unit_price sejam enviados
                    OrderItem::create([
                        'order_id' => $order->id,
                        'store_id' => $storeId,
                        'stock_item_id' => $cartItem->stock_item_id,
                        'item_name' => substr($itemName, 0, 255), // Garante que não estoure o limite do banco
                        'unit_price' => $cartItem->price,
                        'quantity' => $cartItem->quantity,
                    ]);
                }

                // 3. REGISTRO DO FRETE POR LOJA (ORDERSHIPPING)
                $metodoChave = $this->selectedShipping[$storeId] ?? null;
                if ($metodoChave && isset($this->fretesPorLoja[$storeId][$metodoChave])) {
                    OrderShipping::create([
                        'order_id' => $order->id,
                        'store_id' => $storeId,
                        'shipping_method_key' => $metodoChave,
                        'shipping_method_name' => $this->fretesPorLoja[$storeId][$metodoChave]['nome'],
                        'shipping_cost' => $this->fretesPorLoja[$storeId][$metodoChave]['valor'],
                        'destination_zip_code' => $this->cepDestino,
                        'shipping_status' => 'pending'
                    ]);
                }
            }

            DB::commit();

            CartItem::where('session_id', Session::getId())->delete();
            Session::forget('checkout_shipping');

            $this->qrCodeBase64 = null;
            $this->qrCodeCopiaCola = '00020101021126580014br.gov.bcb.pix0136versus-tcg-split-mock-'.uniqid();
            $this->pedidoFinalizado = true;

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao finalizar pedido: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $layout = $this->loja ? 'layouts.template' : 'layouts.app';
        return view('livewire.lobby.cart.checkout')->layout($layout, ['loja' => $this->loja, 'isMarketplace' => $this->isMarketplace]);
    }
}