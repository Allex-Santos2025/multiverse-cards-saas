<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            // Adicionamos a coluna logo após o identificador da carta (ajuste o 'after' se necessário)
            $table->foreignId('catalog_product_id')
                  ->nullable()
                  ->after('store_id') 
                  ->constrained('catalog_products')
                  ->nullOnDelete(); // Se um produto for apagado do catálogo, não deleta o registro financeiro/estoque da loja
        });
    }

    public function down(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropForeign(['catalog_product_id']);
            $table->dropColumn('catalog_product_id');
        });
    }
};