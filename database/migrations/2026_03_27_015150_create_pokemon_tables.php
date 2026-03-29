<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Conceitos do Pokémon (PkConcept)
        Schema::create('pk_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('supertype')->nullable();
            $table->string('hp')->nullable();
            $table->string('level')->nullable();
            $table->json('types')->nullable();
            $table->json('subtypes')->nullable();
            $table->json('attacks')->nullable();
            $table->json('abilities')->nullable();
            $table->json('weaknesses')->nullable();
            $table->json('resistances')->nullable();
            $table->json('retreat_cost')->nullable();
            $table->string('evolves_from')->nullable();
            $table->json('evolves_to')->nullable();
            $table->text('rules_text')->nullable();
            $table->json('national_pokedex_numbers')->nullable();
            $table->json('legalities')->nullable();
            $table->string('regulation_mark')->nullable();
            $table->json('ancient_trait')->nullable();
            $table->timestamps();
        });

        // 2. Tabela de Impressões/Artes do Pokémon (PkPrint)
        Schema::create('pk_prints', function (Blueprint $table) {
            $table->id();
            $table->string('rarity')->nullable();
            $table->string('artist')->nullable();
            $table->string('number')->nullable();
            $table->text('flavor_text')->nullable();
            $table->string('language_code')->default('en')->nullable();
            $table->string('level')->nullable();
            $table->json('images')->nullable();
            $table->json('tcgplayer')->nullable();
            $table->json('cardmarket')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pk_prints');
        Schema::dropIfExists('pk_concepts');
    }
};