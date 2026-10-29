<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabela Principal de Decks
        Schema::create('player_decks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_user_id')->constrained('player_users')->onDelete('cascade');
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            
            $table->string('name', 120);
            $table->string('slug', 150)->index();
            $table->string('format', 50)->default('standard'); // commander, modern, standard, pioneer, etc.
            $table->text('description')->nullable();
            
            // Arte de destaque da capa do deck
            $table->string('cover_image_path')->nullable();
            $table->foreignId('cover_catalog_print_id')->nullable()->constrained('catalog_prints')->onDelete('set null');

            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->index(['player_user_id', 'game_id']);
        });

        // 2. Cartas do Deck (Com distinção explícita entre Físico Real e Virtual/Wishlist)
        Schema::create('player_deck_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_deck_id')->constrained('player_decks')->onDelete('cascade');
            $table->foreignId('catalog_print_id')->constrained('catalog_prints')->onDelete('cascade');
            
            // Vínculo com a carta física do acervo pessoal (NULL se for Wishlist de teste)
            $table->foreignId('player_collection_item_id')
                ->nullable()
                ->constrained('player_collection_items')
                ->onDelete('set null');

            $table->unsignedSmallInteger('quantity')->default(1);
            
            // Setor: mainboard, sideboard, commander, extra
            $table->string('board_type', 30)->default('mainboard');
            
            // Flag de distinção: TRUE = Wishlist/Proxy | FALSE = Carta física alocada
            $table->boolean('is_virtual')->default(false);

            $table->timestamps();

            // Índices de otimização para carregamento rápido no Deck Builder e no Simulador
            $table->index(['player_deck_id', 'board_type']);
            $table->index(['player_collection_item_id']);
            $table->index(['is_virtual']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_deck_cards');
        Schema::dropIfExists('player_decks');
    }
};
