<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();

            // 1. Relacionamentos (Quem vende e Qual carta é)
            // Ajuste 'stores' e 'catalog_prints' se os nomes das suas tabelas forem diferentes
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('catalog_print_id')->constrained('catalog_prints')->onDelete('cascade');

            // 2. Dados de Venda (O básico)
            $table->decimal('price', 10, 2);
            $table->integer('quantity')->default(0);
            
            // 3. Detalhes da Carta (Front valida opções, Banco guarda a sigla)
            $table->string('condition', 10)->default('NM'); // ex: NM, SP, MP...
            $table->string('language', 5)->default('en');   // ex: en, pt, jp...
            
            // 4. Extras Dinâmicos (Gerenciados via Filament)
            // Guarda o JSON das opções marcadas: { "foil": true, "signed": true }
            $table->json('extras')->nullable();

            // 5. Diferenciais da sua Plataforma (Mídia e Obs)
            $table->text('comments')->nullable(); // ex: "Leve amassado na ponta superior"
            $table->json('real_photos')->nullable(); // Array de paths: ["storage/x.jpg", "storage/y.jpg"]

            // 6. Auditoria e Segurança
            $table->timestamps();
            $table->softDeletes(); // Para lixeira/recuperação

            // 7. Índices de Performance
            // Busca rápida: "A loja X tem a carta Y?"
            $table->index(['store_id', 'catalog_print_id']); 
            // Ordenação: "Listar cartas da loja X por preço"
            $table->index(['store_id', 'price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};