<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rulings', function (Blueprint $table) {
            $table->id();

            // Ligação com a tabela V5 que acabamos de popular
            $table->foreignId('mtg_concept_id')
                  ->constrained('mtg_concepts')
                  ->cascadeOnDelete();

            $table->string('source')->default('wotc');
            $table->date('published_at')->nullable();
            $table->text('comment');

            // Auxiliar para busca rápida na API
            $table->uuid('oracle_id')->index()->nullable();

            $table->timestamps();

            // Evita duplicar a mesma regra para a mesma carta
            $table->unique(['mtg_concept_id', 'source', 'published_at', 'comment'], 'ruling_unique_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rulings');
    }
};
