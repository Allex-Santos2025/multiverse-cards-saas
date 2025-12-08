<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Limpeza preventiva de tabelas novas (caso existam de tentativas falhas)
        // NÃO apaga as tabelas antigas (mtg_cards, cards, etc)
        $tables = [
            'mtg_concepts', 'mtg_prints',
            'bs_concepts', 'bs_prints',
            'pk_concepts', 'pk_prints',
            'ygo_concepts', 'ygo_prints',
            'lor_concepts', 'lor_prints',
            'fab_concepts', 'fab_prints',
            'swu_concepts', 'swu_prints',
            'op_concepts', 'op_prints',
            'catalog_prints', 'catalog_concepts'
        ];
        
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }

        // =================================================================
        // 1. TABELAS MESTRAS (O "Hub" Universal)
        // =================================================================

        Schema::create('catalog_concepts', function (Blueprint $table) {
            $table->id();
            
            // FK com nome explícito
            $table->unsignedBigInteger('game_id');
            $table->foreign('game_id', 'fk_mvs_conc_game_id')->references('id')->on('games');

            $table->string('name')->index(); 
            $table->string('slug')->index(); 
            $table->json('search_names')->nullable(); 
            $table->nullableMorphs('specific'); 
            $table->timestamps();
        });

        Schema::create('catalog_prints', function (Blueprint $table) {
            $table->id();
            
            // FKs com nomes explícitos e únicos
            $table->unsignedBigInteger('concept_id');
            $table->foreign('concept_id', 'fk_mvs_prt_concept_id')
                  ->references('id')->on('catalog_concepts')
                  ->cascadeOnDelete();

            $table->unsignedBigInteger('set_id');
            $table->foreign('set_id', 'fk_mvs_prt_set_id')
                  ->references('id')->on('sets');
            
            $table->string('image_path')->nullable(); 
            
            $table->nullableMorphs('specific');
            $table->timestamps();
        });

        // =================================================================
        // 2. TABELAS ESPECÍFICAS (8 JOGOS)
        // =================================================================

        // --- 1. MAGIC: THE GATHERING ---
        Schema::create('mtg_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('oracle_id')->nullable()->index();
            $table->string('mana_cost')->nullable();
            $table->float('cmc')->default(0);
            $table->string('type_line')->nullable();
            $table->text('oracle_text')->nullable();
            $table->string('power')->nullable();
            $table->string('toughness')->nullable();
            $table->string('loyalty')->nullable();
            $table->json('colors')->nullable();
            $table->json('color_identity')->nullable();
            $table->json('keywords')->nullable();
            $table->json('legalities')->nullable();
            $table->timestamps();
        });

        Schema::create('mtg_prints', function (Blueprint $table) {
            $table->id();
            $table->string('scryfall_id')->nullable()->index();
            $table->string('rarity')->nullable();
            $table->string('artist')->nullable();
            $table->string('collector_number')->nullable();
            $table->string('language_code')->default('en'); 
            $table->text('flavor_text')->nullable();
            $table->json('finishes')->nullable(); 
            $table->timestamps();
        });

        // --- 2. BATTLE SCENES ---
        Schema::create('bs_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('alter_ego')->nullable();
            $table->string('type_line')->nullable(); 
            $table->string('affiliation')->nullable();
            $table->string('energy')->nullable(); 
            $table->string('shield')->nullable(); 
            $table->string('capacity')->nullable(); 
            $table->text('rules_text')->nullable();
            $table->json('powers')->nullable();
            $table->string('alignment')->nullable(); 
            $table->json('legalities')->nullable(); 
            $table->timestamps();
        });

        Schema::create('bs_prints', function (Blueprint $table) {
            $table->id();
            $table->string('rarity')->nullable();
            $table->string('artist')->nullable();
            $table->string('collection_number')->nullable();
            $table->text('flavor_text')->nullable();
            $table->string('language_code')->default('pt'); 
            $table->timestamps();
        });

        // --- 3. POKÉMON TCG ---
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
            $table->timestamps();
        });

        Schema::create('pk_prints', function (Blueprint $table) {
            $table->id();
            $table->string('rarity')->nullable();
            $table->string('artist')->nullable();
            $table->string('number')->nullable();
            $table->text('flavor_text')->nullable();
            $table->string('language_code')->default('en'); 
            $table->timestamps();
        });

        // --- 4. YU-GI-OH! ---
        Schema::create('ygo_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('konami_id')->nullable();
            $table->string('type')->nullable(); 
            $table->string('race')->nullable(); 
            $table->string('attribute')->nullable(); 
            $table->integer('atk')->nullable();
            $table->integer('def')->nullable();
            $table->integer('level')->nullable(); 
            $table->integer('scale')->nullable(); 
            $table->text('description')->nullable();
            $table->string('archetype')->nullable();
            $table->json('link_markers')->nullable();
            $table->json('banlist_info')->nullable();
            $table->timestamps();
        });

        Schema::create('ygo_prints', function (Blueprint $table) {
            $table->id();
            $table->string('set_code')->nullable(); 
            $table->string('rarity')->nullable();
            $table->string('language_code')->default('en'); 
            $table->timestamps();
        });

        // --- 5. LORCANA ---
        Schema::create('lor_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('type')->nullable(); 
            $table->integer('cost')->nullable();
            $table->boolean('inkable')->default(false);
            $table->string('color')->nullable(); 
            $table->integer('strength')->nullable();
            $table->integer('willpower')->nullable();
            $table->integer('lore')->nullable();
            $table->json('classifications')->nullable(); 
            $table->text('abilities_text')->nullable();
            $table->timestamps();
        });

        Schema::create('lor_prints', function (Blueprint $table) {
            $table->id();
            $table->string('rarity')->nullable();
            $table->string('artist')->nullable();
            $table->integer('collector_number')->nullable();
            $table->text('flavor_text')->nullable();
            $table->string('language_code')->default('en'); 
            $table->timestamps();
        });

        // --- 6. FLESH AND BLOOD ---
        Schema::create('fab_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('pitch')->nullable(); 
            $table->string('cost')->nullable();
            $table->string('power')->nullable();
            $table->string('defense')->nullable();
            $table->string('health')->nullable();
            $table->string('type')->nullable();
            $table->string('class')->nullable(); 
            $table->string('talent')->nullable();
            $table->json('keywords')->nullable();
            $table->text('text')->nullable();
            $table->timestamps();
        });

        Schema::create('fab_prints', function (Blueprint $table) {
            $table->id();
            $table->string('rarity')->nullable();
            $table->string('artist')->nullable();
            $table->string('identifier')->nullable();
            $table->string('language_code')->default('en'); 
            $table->timestamps();
        });

        // --- 7. STAR WARS: UNLIMITED ---
        Schema::create('swu_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('subtitle')->nullable();
            $table->boolean('is_unique')->default(false);
            $table->string('type')->nullable(); 
            $table->json('aspects')->nullable(); 
            $table->integer('cost')->nullable();
            $table->integer('power')->nullable();
            $table->integer('hp')->nullable();
            $table->string('arena')->nullable(); 
            $table->json('traits')->nullable();
            $table->json('keywords')->nullable();
            $table->text('ability_text')->nullable();
            $table->timestamps();
        });

        Schema::create('swu_prints', function (Blueprint $table) {
            $table->id();
            $table->string('rarity')->nullable();
            $table->string('artist')->nullable();
            $table->integer('card_number')->nullable();
            $table->text('flavor_text')->nullable();
            $table->string('language_code')->default('en'); 
            $table->timestamps();
        });

        // --- 8. ONE PIECE CARD GAME ---
        Schema::create('op_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('color')->nullable();
            $table->string('type')->nullable(); 
            $table->integer('cost')->nullable();
            $table->integer('power')->nullable();
            $table->integer('life')->nullable();
            $table->integer('counter')->nullable();
            $table->string('attribute')->nullable(); 
            $table->json('traits')->nullable(); 
            $table->text('effect')->nullable();
            $table->text('trigger_effect')->nullable();
            $table->timestamps();
        });

        Schema::create('op_prints', function (Blueprint $table) {
            $table->id();
            $table->string('rarity')->nullable();
            $table->string('artist')->nullable();
            $table->string('card_code')->nullable(); 
            $table->string('language_code')->default('en'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $prefixes = ['mtg', 'bs', 'pk', 'ygo', 'lor', 'fab', 'swu', 'op'];
        foreach ($prefixes as $prefix) {
            Schema::dropIfExists("{$prefix}_prints");
            Schema::dropIfExists("{$prefix}_concepts");
        }
        Schema::dropIfExists('catalog_prints');
        Schema::dropIfExists('catalog_concepts');
    }
};
