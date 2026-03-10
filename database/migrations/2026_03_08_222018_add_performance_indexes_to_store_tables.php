<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Otimiza a busca principal da loja (Catálogo)
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->index(['game_id', 'type', 'is_active'], 'idx_products_game_type_active');
        });

        // 2. Conserta o bug estrutural e cria os índices de velocidade no Estoque
        Schema::table('stock_items', function (Blueprint $table) {
            // Permite que selados/acessórios sejam salvos sem exigir uma carta
            $table->unsignedBigInteger('catalog_print_id')->nullable()->change();

            // Índices Compostos (O "Sumário" que vai deixar tudo instantâneo)
            $table->index(['store_id', 'catalog_product_id'], 'idx_stock_store_product');
            $table->index(['store_id', 'catalog_print_id'], 'idx_stock_store_print');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->dropIndex('idx_products_game_type_active');
        });

        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropIndex('idx_stock_store_product');
            $table->dropIndex('idx_stock_store_print');
            
            // Reverte a coluna para NOT NULL caso precise dar rollback
            $table->unsignedBigInteger('catalog_print_id')->nullable(false)->change();
        });
    }
};