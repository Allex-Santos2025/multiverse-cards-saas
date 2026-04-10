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
        // TABELA DE CONCEITOS (Modo Agrupado)
        Schema::table('catalog_concepts', function (Blueprint $table) {
            // Trilho para: Filtro por Jogo + Filtro de Validade + Ordem Alfabética
            $table->index(['game_id', 'is_valid', 'name'], 'idx_concepts_full_browse');
        });

        // TABELA DE PRINTS (Modo Desagrupado - Onde está o maior volume)
        Schema::table('catalog_prints', function (Blueprint $table) {
            // 1. Trilho Alfabético: Validade + Nome (Resolve A-Z e Z-A)
            $table->index(['is_valid', 'printed_name'], 'idx_prints_alpha_browse');

            // 2. Trilho de Coleção: Validade + Número (Resolve 0-9 e 9-0)
            // Nota: Funciona melhor se os números forem tratados como strings fixas (001, 002)
            $table->index(['is_valid', 'collector_number'], 'idx_prints_number_browse');

            // 3. Trilho de Raridade: Validade + Raridade + Nome
            // Permite filtrar raridade e já entregar em ordem alfabética
            $table->index(['is_valid', 'rarity', 'printed_name'], 'idx_prints_rarity_browse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
