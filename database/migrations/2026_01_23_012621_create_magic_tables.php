<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela de Conceitos (Regras, Oracle, Tipos)
        Schema::create('mtg_concepts', function (Blueprint $table) {
            $table->id();

            // Identificador Único do Oracle (Scryfall)
            $table->uuid('oracle_id')->unique()->index();

            // Atributos de Jogo
            $table->string('mana_cost')->nullable();
            $table->float('cmc')->default(0);
            $table->string('type_line')->nullable();
            $table->text('oracle_text')->nullable();
            $table->string('power')->nullable();
            $table->string('toughness')->nullable();
            $table->string('loyalty')->nullable(); // Para Planeswalkers

            // JSONs (Arrays)
            $table->json('colors')->nullable();
            $table->json('color_identity')->nullable();
            $table->json('keywords')->nullable();
            $table->json('legalities')->nullable();
            $table->json('produced_mana')->nullable();

            // Ranks
            $table->integer('edhrec_rank')->nullable();
            $table->integer('penny_rank')->nullable();

            $table->timestamps();
        });

        // 2. Tabela de Prints (A carta física na coleção)
        Schema::create('mtg_prints', function (Blueprint $table) {
            $table->id();

            // Identificador Único do Print (Scryfall UUID)
            $table->uuid('api_id')->unique()->index(); // Scryfall ID

            // Dados de Coleção
            $table->string('rarity')->default('common');
            $table->string('collector_number');
            $table->string('artist')->nullable();
            $table->text('flavor_text')->nullable();
            $table->string('language_code')->default('en')->index();

            // Detalhes Visuais/Técnicos
            $table->string('frame')->nullable();
            $table->string('border_color')->nullable();
            $table->string('security_stamp')->nullable();
            $table->string('watermark')->nullable();

            // Flags Booleanas
            $table->boolean('full_art')->default(false);
            $table->boolean('textless')->default(false);
            $table->boolean('promo')->default(false);
            $table->boolean('reprint')->default(false);
            $table->boolean('digital')->default(false);

            // Acabamentos e Preços (JSON)
            $table->json('finishes')->nullable(); // foil, nonfoil, etched
            $table->json('prices')->nullable();
            $table->json('multiverse_ids')->nullable();

            $table->date('released_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mtg_prints');
        Schema::dropIfExists('mtg_concepts');
    }
};

