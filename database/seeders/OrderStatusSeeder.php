<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderStatus;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'slug' => 'pending_payment',
                'name' => 'Aguardando Pagamento',
                'color' => '#eab308',
                'order_position' => 1,
            ],
            [
                'slug' => 'separating',
                'name' => 'Em Separação',
                'color' => '#f97316',
                'order_position' => 2,
            ],
            [
                'slug' => 'ready_for_pickup',
                'name' => 'Pronto p/ Retirada',
                'color' => '#3b82f6',
                'order_position' => 3,
            ],
            [
                'slug' => 'shipped',
                'name' => 'Enviado',
                'color' => '#a855f7',
                'order_position' => 4,
            ],
            [
                'slug' => 'delivered',
                'name' => 'Entregue',
                'color' => '#10b981',
                'order_position' => 5,
            ],
            [
                'slug' => 'canceled',
                'name' => 'Cancelado',
                'color' => '#ef4444',
                'order_position' => 6,
            ],
        ];

        foreach ($statuses as $st) {
            OrderStatus::updateOrCreate(
                ['slug' => $st['slug']],
                $st
            );
        }
    }
}
