<?php

namespace App\Livewire\Store\Dashboard;

use Livewire\Component;
use App\Models\OrderShipping;
use App\Models\OrderItem;
use App\Models\OrderStatus;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderDetail extends Component
{
    public $slug;
    public $loja;
    public $orderId;

    public $newStatusId;
    public $trackingCode;
    public $newComment = '';
    public $successMessage = '';

    // Mensagens com o cliente
    public $newMessageText = '';
    public $sendEmailCopy = true;

    public function mount($orderId, $slug = null)
    {
        $this->orderId = $orderId;
        $this->slug = $slug ?? request()->route('slug');
        if (!$this->slug) {
            $host = request()->getHost();
            $this->slug = explode('.', $host)[0];
        }

        $this->loja = Store::where('url_slug', $this->slug)->firstOrFail();

        $shipping = OrderShipping::where('store_id', $this->loja->id)
            ->where('order_id', $this->orderId)
            ->first();

        if ($shipping) {
            $this->newStatusId = $shipping->order_status_id;
            $this->trackingCode = $shipping->tracking_code;
        }
    }

    public function updateStatus()
    {
        $shipping = OrderShipping::where('store_id', $this->loja->id)
            ->where('order_id', $this->orderId)
            ->firstOrFail();

        $oldStatusId = $shipping->order_status_id;

        if ($oldStatusId != $this->newStatusId) {
            $shipping->update([
                'order_status_id' => $this->newStatusId
            ]);

            $currentUser = auth('store_user')->user();
            $actorName = $currentUser ? ($currentUser->name ?? 'Lojista') : 'Lojista';

            DB::table('order_status_histories')->insert([
                'order_id' => $this->orderId,
                'order_shipping_id' => $shipping->id,
                'store_id' => $this->loja->id,
                'old_status_id' => $oldStatusId,
                'new_status_id' => $this->newStatusId,
                'action_by_type' => 'store_user',
                'action_by_id' => $currentUser ? $currentUser->id : null,
                'actor_name' => $actorName,
                'comment' => $this->newComment ? trim($this->newComment) : 'Situação alterada manualmente pelo painel.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->newComment = '';
            $this->successMessage = 'Situação do pedido atualizada e registrada no histórico!';
        } else {
            $this->successMessage = 'Nenhuma alteração de situação detectada.';
        }
    }

    public function updateTracking()
    {
        $shipping = OrderShipping::where('store_id', $this->loja->id)
            ->where('order_id', $this->orderId)
            ->firstOrFail();

        $shipping->update([
            'tracking_code' => trim($this->trackingCode)
        ]);

        $currentUser = auth('store_user')->user();
        DB::table('order_status_histories')->insert([
            'order_id' => $this->orderId,
            'order_shipping_id' => $shipping->id,
            'store_id' => $this->loja->id,
            'old_status_id' => $shipping->order_status_id,
            'new_status_id' => $shipping->order_status_id,
            'action_by_type' => 'store_user',
            'action_by_id' => $currentUser ? $currentUser->id : null,
            'actor_name' => $currentUser ? ($currentUser->name ?? 'Lojista') : 'Lojista',
            'comment' => 'Código de rastreamento atualizado para: ' . trim($this->trackingCode),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->successMessage = 'Código de rastreamento salvo!';
    }

    public function markAsShipped()
    {
        $shipping = OrderShipping::where('store_id', $this->loja->id)
            ->where('order_id', $this->orderId)
            ->firstOrFail();

        $oldStatusId = $shipping->order_status_id;
        $shippedStatus = OrderStatus::where('name', 'like', '%enviado%')->first();
        $newStatus = $shippedStatus ? $shippedStatus->id : $oldStatusId;

        $shipping->update([
            'shipping_status' => 'shipped',
            'tracking_code' => trim($this->trackingCode),
            'order_status_id' => $newStatus,
        ]);

        $this->newStatusId = $newStatus;

        $currentUser = auth('store_user')->user();
        DB::table('order_status_histories')->insert([
            'order_id' => $this->orderId,
            'order_shipping_id' => $shipping->id,
            'store_id' => $this->loja->id,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatus,
            'action_by_type' => 'store_user',
            'action_by_id' => $currentUser ? $currentUser->id : null,
            'actor_name' => $currentUser ? ($currentUser->name ?? 'Lojista') : 'Lojista',
            'comment' => 'Pedido marcado como Enviado. Rastreio: ' . (trim($this->trackingCode) ?: 'Não informado'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->successMessage = 'Pedido marcado como Enviado e registrado no histórico!';
    }

    public function confirmPixPayment()
    {
        $shipping = OrderShipping::where('store_id', $this->loja->id)
            ->where('order_id', $this->orderId)
            ->firstOrFail();

        $order = $shipping->order;
        if ($order) {
            $order->update(['payment_status' => 'pago']);
        }

        $paidStatus = OrderStatus::where('name', 'like', '%pago%')
            ->orWhere('name', 'like', '%aprova%')
            ->orWhere('name', 'like', '%separa%')
            ->first();

        $oldStatusId = $shipping->order_status_id;
        $newStatusId = $paidStatus ? $paidStatus->id : $oldStatusId;

        $shipping->update(['order_status_id' => $newStatusId]);
        $this->newStatusId = $newStatusId;

        $currentUser = auth('store_user')->user();
        DB::table('order_status_histories')->insert([
            'order_id' => $this->orderId,
            'order_shipping_id' => $shipping->id,
            'store_id' => $this->loja->id,
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'action_by_type' => 'store_user',
            'action_by_id' => $currentUser ? $currentUser->id : null,
            'actor_name' => $currentUser ? ($currentUser->name ?? 'Lojista') : 'Lojista',
            'comment' => 'Pagamento via PIX conferido e confirmado manualmente pelo lojista.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->successMessage = 'Pagamento PIX confirmado e registrado no histórico com sucesso!';
    }

    public function addMessage()
    {
        if (empty(trim($this->newMessageText))) {
            return;
        }

        $currentUser = auth('store_user')->user();
        $senderName = $currentUser ? ($currentUser->name ?? 'Lojista') : ($this->loja->name ?? 'Lojista');

        $emailDispatched = false;
        if ($this->sendEmailCopy) {
            $shipping = OrderShipping::with('order.playerUser')->where('store_id', $this->loja->id)->where('order_id', $this->orderId)->first();
            $playerUser = optional(optional($shipping)->order)->playerUser;
            if ($playerUser && !empty($playerUser->email)) {
                // Notificação de e-mail registrada
                $emailDispatched = true;
            }
        }

        DB::table('order_messages')->insert([
            'order_id' => $this->orderId,
            'store_id' => $this->loja->id,
            'sender_type' => 'store_user',
            'sender_id' => $currentUser ? $currentUser->id : null,
            'sender_name' => $senderName,
            'message' => trim($this->newMessageText),
            'email_sent' => $emailDispatched,
            'is_customer_blocked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->newMessageText = '';
        $this->successMessage = 'Mensagem adicionada com sucesso!';
    }

    public function toggleReviewStoreVisibility($reviewId)
    {
        $review = DB::table('order_reviews')
            ->where('id', $reviewId)
            ->where('store_id', $this->loja->id)
            ->first();

        if ($review) {
            $newVal = !$review->is_visible_on_store;
            DB::table('order_reviews')->where('id', $reviewId)->update([
                'is_visible_on_store' => $newVal,
                'updated_at' => now()
            ]);

            $this->successMessage = $newVal ? 'Avaliação agora está visível na sua loja!' : 'Avaliação ocultada da sua loja.';
        }
    }

    public function render()
    {
        $storeId = $this->loja->id;

        $shipping = OrderShipping::with(['order.playerUser', 'status'])
            ->where('store_id', $storeId)
            ->where('order_id', $this->orderId)
            ->firstOrFail();

        $order = $shipping->order;
        $playerUser = optional($order)->playerUser;

        // Endereço de entrega
        $address = null;
        if ($playerUser) {
            $address = DB::table('player_addresses')
                ->where('player_user_id', $playerUser->id)
                ->orderByDesc('is_official')
                ->first();
        }

        // Itens deste pedido
        $items = OrderItem::with(['stockItem.catalogPrint.concept', 'stockItem.catalogPrint.set'])
            ->where('store_id', $storeId)
            ->where('order_id', $this->orderId)
            ->get();

        $subtotalItens = $items->sum(function($item) {
            return $item->unit_price * $item->quantity;
        });

        $totalGeral = $subtotalItens + ($shipping->shipping_cost ?? 0);
        $statuses = OrderStatus::orderBy('name')->get();

        // Histórico de alterações deste pedido
        $histories = DB::table('order_status_histories')
            ->leftJoin('order_statuses as old_st', 'order_status_histories.old_status_id', '=', 'old_st.id')
            ->leftJoin('order_statuses as new_st', 'order_status_histories.new_status_id', '=', 'new_st.id')
            ->where('order_status_histories.order_id', $this->orderId)
            ->where('order_status_histories.store_id', $storeId)
            ->select(
                'order_status_histories.*',
                'old_st.name as old_status_name',
                'new_st.name as new_status_name'
            )
            ->orderByDesc('order_status_histories.created_at')
            ->get();

        // Mensagens trocadas com o cliente
        $messages = DB::table('order_messages')
            ->where('order_id', $this->orderId)
            ->where('store_id', $storeId)
            ->orderBy('created_at', 'asc')
            ->get();

        // Avaliação deixada pelo cliente
        $review = DB::table('order_reviews')
            ->where('order_id', $this->orderId)
            ->where('store_id', $storeId)
            ->first();

        return view('livewire.store.dashboard.order-detail', [
            'shipping' => $shipping,
            'order' => $order,
            'playerUser' => $playerUser,
            'address' => $address,
            'items' => $items,
            'subtotalItens' => $subtotalItens,
            'totalGeral' => $totalGeral,
            'statuses' => $statuses,
            'histories' => $histories,
            'messages' => $messages,
            'review' => $review,
            'loja' => $this->loja,
        ])
        ->extends('layouts.dashboard')
        ->section('content');
    }
}
