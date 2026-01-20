<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            
            // Relacionamentos
            $table->foreignId('store_user_id')->constrained('store_users')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('plans');
            
            // Controle de Assinatura
            $table->string('status')->default('pending'); // pending, active, canceled, trial
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            
            // Dados de Pagamento (Gateway)
            $table->string('gateway_id')->nullable(); // ID no Stripe/Pagar.me/MercadoPago
            $table->string('gateway_plan_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};