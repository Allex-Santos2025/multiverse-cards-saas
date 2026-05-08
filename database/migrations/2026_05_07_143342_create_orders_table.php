<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_user_id')->constrained('player_users')->onDelete('cascade');
            
            // Controle de Valor
            $table->decimal('total_amount', 10, 2); // Subtotal + Fretes - Descontos
            
            // Controle de Pagamento (Gateway)
            $table->string('payment_method')->nullable(); // 'pix', 'credit_card'
            $table->string('payment_status')->default('pending'); // pending, paid, failed, refunded
            $table->string('gateway_transaction_id')->nullable(); // ID gerado pelo Mercado Pago/Pagar.me
            $table->text('gateway_payment_url')->nullable(); // Link do PIX copia e cola ou QrCode
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};