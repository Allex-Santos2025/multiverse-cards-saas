<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_stock_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->date('snapshot_date');
            $table->integer('total_items')->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->json('game_breakdown')->nullable(); // Para guardar a % de cada jogo
            $table->timestamps();

            // Regra de ouro: Uma loja só pode ter 1 snapshot por dia. Se atualizar no mesmo dia, sobrescreve.
            $table->unique(['store_id', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_stock_snapshots');
    }
};
