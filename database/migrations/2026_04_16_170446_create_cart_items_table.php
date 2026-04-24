<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            
            // O Dono do Carrinho
            $table->string('session_id')->index()->nullable(); // Para visitantes (anônimos)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Para clientes logados
            
            // A Origem (Fundamental para o Split Order no futuro)
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            
            // O Item do Estoque
            $table->foreignId('stock_item_id')->constrained('stock_items')->onDelete('cascade');
            
            // Detalhes da Intenção de Compra
            $table->integer('quantity')->default(1);
            
            // Preço Congelado (Importante para avisar o cliente se o lojista alterar o valor depois)
            $table->decimal('price', 12, 2); 

            $table->timestamps();

            // Index para busca rápida de carrinhos ativos
            $table->index(['session_id', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};