<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('player_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_user_id')->constrained('player_users')->cascadeOnDelete();
            
            // Caso a transação seja vinculada a uma loja (ex: venda para a Olho de Leão)
            $table->unsignedBigInteger('store_id')->nullable(); 
            
            $table->enum('type', ['in', 'out']); // in = entrada, out = saída
            $table->decimal('amount', 15, 2);
            
            $table->string('description', 255); 
            
            // Polimorfismo simples para vincular a pedidos ou buylists reais
            $table->string('reference_type')->nullable(); 
            $table->unsignedBigInteger('reference_id')->nullable();
            
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('completed');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('player_transactions');
    }
};