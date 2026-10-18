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
        // 1. Tabela Mecânica / Conceito do One Piece
        Schema::create('op_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('card_code')->unique()->index(); // Ex: OP01-001, ST01-001, P-001
            $table->string('name'); // Nome canônico oficial (EN)
            $table->string('name_ja')->nullable(); // Nome original japonês
            $table->string('card_type')->index(); // Leader, Character, Event, Stage, DON!!
            $table->string('type_line')->nullable(); // Ex: Character - Straw Hat Crew
            $table->json('colors'); // Ex: ["Red"] ou ["Red", "Green"]
            $table->string('attribute')->nullable(); // Slash, Strike, Ranged, Special, Wisdom
            $table->integer('cost')->nullable(); // Custo em DON!!
            $table->integer('life')->nullable(); // Vida (específico de Leaders)
            $table->integer('power')->nullable(); // Ex: 5000, 6000
            $table->integer('counter')->nullable(); // Ex: 1000, 2000
            $table->text('oracle_text')->nullable(); // Efeito mecânico em inglês (fallback)
            $table->text('trigger_text')->nullable(); // Trigger effect em inglês (fallback)
            $table->json('keywords')->nullable(); // Ex: ["Blocker", "Rush", "Double Attack"]
            $table->json('families')->nullable(); // Ex: ["Straw Hat Crew", "Supernovas"]
            $table->json('legalities')->nullable(); // Formatos legais
            $table->timestamps();
        });

        // 2. Tabela de Impressão Física / Idioma do One Piece
        Schema::create('op_prints', function (Blueprint $table) {
            $table->id();
            $table->string('api_id')->nullable()->index(); // ID externo da Bandai / API
            $table->foreignId('concept_id')->constrained('op_concepts')->cascadeOnDelete();
            $table->string('collector_number')->index(); // Ex: OP01-001
            $table->string('rarity'); // L, C, UC, R, SR, SEC, SP, TR, Manga
            $table->string('artist')->nullable(); // Ilustrador oficial
            $table->string('language_code', 10)->default('en')->index(); // en, ja, zhs
            $table->text('flavor_text')->nullable(); // Texto de sabor traduzido
            $table->boolean('full_art')->default(false);
            $table->boolean('has_foil')->default(false);
            $table->boolean('textured')->default(false);
            $table->boolean('promo')->default(false);
            $table->boolean('reprint')->default(false);
            $table->string('variation')->nullable(); // Standard, Alternative Art, Manga, Promo
            $table->string('variation_of')->nullable(); // Referência se for variação de arte

            // Campos textuais traduzidos da carta física (isolados por idioma)
            $table->string('printed_name')->nullable(); // Nome impresso nesta língua
            $table->string('printed_type_line')->nullable(); // Tipo traduzido
            $table->text('printed_text')->nullable(); // Regras / Efeito nesta língua
            $table->text('printed_trigger_text')->nullable(); // Trigger nesta língua

            $table->string('image_status')->default('pending'); // pending, downloaded, missing
            $table->date('released_at')->nullable(); // Data desta impressão física
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('op_prints');
        Schema::dropIfExists('op_concepts');
    }
};