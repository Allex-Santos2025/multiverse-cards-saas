<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela configurável de Status de Pedidos / Pacotes
        if (!Schema::hasTable('order_statuses')) {
            Schema::create('order_statuses', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique(); // ex: pending_payment, separating, ready_for_pickup, shipped, delivered, canceled
                $table->string('name');            // ex: Aguardando Pagamento, Em Separação, Pronto p/ Retirada, Enviado, Entregue, Cancelado
                $table->string('color')->default('#64748b'); // Hexadecimal ou classe de cor
                $table->unsignedInteger('order_position')->default(1);
                $table->timestamps();
            });
        }

        // 2. Coluna de Origem (Canal de Venda) na tabela orders
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'channel')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('channel')->default('marketplace')->after('player_user_id'); // 'store' ou 'marketplace'
            });
        }

        // 3. Vínculo do Status Operacional na tabela order_shippings
        if (Schema::hasTable('order_shippings') && !Schema::hasColumn('order_shippings', 'order_status_id')) {
            Schema::table('order_shippings', function (Blueprint $table) {
                $table->foreignId('order_status_id')
                    ->nullable()
                    ->after('shipping_status')
                    ->constrained('order_statuses')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_shippings') && Schema::hasColumn('order_shippings', 'order_status_id')) {
            Schema::table('order_shippings', function (Blueprint $table) {
                $table->dropForeign(['order_status_id']);
                $table->dropColumn('order_status_id');
            });
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'channel')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('channel');
            });
        }

        Schema::dropIfExists('order_statuses');
    }
};
