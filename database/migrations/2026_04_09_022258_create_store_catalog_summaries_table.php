<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Executa as alterações (Sobe para o banco).
     */
    public function up(): void
    {
        Schema::create('store_catalog_summaries', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com a loja
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            
            // Polimorfia: Aceita CatalogPrint, CatalogConcept ou qualquer outro jogo no futuro
            $table->string('catalog_type'); 
            $table->unsignedBigInteger('catalog_id');
            
            // Dados pré-calculados para leitura instantânea
            $table->integer('total_qty')->default(0);
            $table->decimal('lowest_price', 12, 2)->nullable();
            $table->decimal('final_price', 12, 2)->nullable(); // Menor preço já com o desconto aplicado
            $table->integer('max_discount')->default(0);
            $table->boolean('has_foil')->default(false);
            $table->boolean('is_valid')->default(true);

            // ÍNDICES: O segredo da velocidade "Nível Liga"
            // Esse índice único garante que NUNCA teremos duas linhas para a mesma carta na mesma loja
            $table->unique(['store_id', 'catalog_type', 'catalog_id'], 'unique_store_catalog');
            
            // Índice para busca de preço rápida (order by price)
            $table->index(['store_id', 'final_price'], 'idx_store_price'); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverte as alterações (Desfaz o que foi feito).
     */
    public function down(): void
    {
        // Se precisar desfazer, a gente simplesmente apaga a tabela
        Schema::dropIfExists('store_catalog_summaries');
    }
};