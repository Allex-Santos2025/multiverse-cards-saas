<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela Mecânica (Regras / Conceito)
        Schema::create('bs_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('alter_ego')->nullable();
            $table->string('type_line')->nullable();
            $table->string('affiliation')->nullable();
            $table->json('affiliations')->nullable();
            $table->integer('power')->nullable();
            $table->integer('toughness')->nullable();
            $table->integer('cost')->nullable();
            $table->text('rules_text')->nullable();
            $table->text('flavor_text')->nullable();
            $table->json('skills')->nullable();
            $table->timestamps();
        });

        // 2. Tabela de Impressão / Tiragem Física
        Schema::create('bs_prints', function (Blueprint $table) {
            $table->id();
            $table->string('rarity')->nullable();
            $table->string('artist')->nullable();
            $table->string('number')->nullable();
            $table->text('flavor_text')->nullable();
            $table->string('language_code', 5)->default('pt');
            $table->json('images')->nullable();
            $table->json('market_prices')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bs_prints');
        Schema::dropIfExists('bs_concepts');
    }
};
