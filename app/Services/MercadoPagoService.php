<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MercadoPagoService
{
    protected $accessToken;

    public function __construct()
    {
        // Ele vai buscar o token no seu arquivo .env
        $this->accessToken = env('MERCADOPAGO_ACCESS_TOKEN');
    }

    public function criarPagamentoPix(Order $order, $email, $nome)
    {
        try {
            $url = 'https://api.mercadopago.com/v1/payments';

            $payload = [
                'transaction_amount' => (float) $order->total_amount,
                'description' => 'Pedido #' . $order->id . ' - Versus TCG',
                'payment_method_id' => 'pix',
                'payer' => [
                    'email' => $email,
                    'first_name' => $nome,
                ],
            ];

            $response = Http::withToken($this->accessToken)
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'status' => 'success',
                    'transaction_id' => $data['id'],
                    'qr_code_base64' => $data['point_of_interaction']['transaction_data']['qr_code_base64'],
                    'qr_code_copia_cola' => $data['point_of_interaction']['transaction_data']['qr_code'],
                ];
            }

            Log::error('Erro Mercado Pago PIX: ' . $response->body());

            return [
                'status' => 'error',
                'message' => 'Erro ao comunicar com o gateway de pagamento.',
            ];

        } catch (\Exception $e) {
            Log::error('Exceção Mercado Pago PIX: ' . $e->getMessage());

            return [
                'status' => 'error',
                'message' => 'Serviço de pagamento indisponível no momento.',
            ];
        }
    }
}