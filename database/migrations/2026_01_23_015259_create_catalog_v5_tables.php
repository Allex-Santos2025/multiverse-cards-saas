<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela Pai de Conceitos (Agnóstica)
        Schema::create('catalog_concepts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_id')->constrained()->cascadeOnDelete();

            $table->string('name'); // Nome principal (ex: Black Lotus)
            $table->string('slug')->index(); // Slug para URL

            // Polimorfismo para a tabela filha (ex: mtg_concepts, pk_concepts)
            $table->nullableMorphs('specific'); 

            $table->timestamps();

            // Índices para performance
            $table->index(['game_id', 'slug']);
        });

        // 2. Tabela Pai de Prints (Agnóstica)
        Schema::create('catalog_prints', function (Blueprint $table) {
            $table->id();

            // Ligações
            $table->foreignId('concept_id')->nullable()->constrained('catalog_concepts')->cascadeOnDelete();
            $table->foreignId('set_id')->constrained('sets')->cascadeOnDelete();

            // Dados Visuais e Básicos
            $table->string('image_path')->nullable(); // Caminho local da imagem
            $table->string('printed_name')->nullable(); // Nome impresso na carta (ex: Loto Negro)
            $table->string('language_code', 10)->default('en');

            // Polimorfismo para a tabela filha (ex: mtg_prints, pk_prints)
            $table->nullableMorphs('specific');

            $table->timestamps();

            // Índices
            $table->index(['concept_id', 'language_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_prints');
        Schema::dropIfExists('catalog_concepts');
    }
};

