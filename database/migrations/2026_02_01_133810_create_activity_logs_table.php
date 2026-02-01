<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            
            // Qual loja gerou esse log?
            $table->foreignId('store_id')->constrained()->onDelete('cascade');

            // Quem fez a ação? (Pode ser null se for uma ação do sistema)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_guard')->nullable(); // store_user, store_admin, etc.

            // O "Coração" do Log Específico (Polimorfismo)
            // Isso permite vincular o log a um Card, um Pedido, um Cliente, etc.
            $table->nullableMorphs('subject'); 
            // ^ Cria automaticamente: subject_id e subject_type

            $table->string('action');      // ex: 'price_updated', 'card_sold', 'login'
            $table->string('module');      // ex: 'inventory', 'security', 'sales'
            $table->text('description');   // Texto legível: "Preço alterado de R$10 para R$20"
            
            // Dados brutos (Antes e Depois)
            $table->json('properties')->nullable(); 

            // Dados de rastreio
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps(); // created_at servirá como a data do evento
            
            // Índices para buscas rápidas (essencial para filtrar por loja e data)
            $table->index(['store_id', 'module']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
