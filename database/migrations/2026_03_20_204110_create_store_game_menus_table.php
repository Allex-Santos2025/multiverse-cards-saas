<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_game_menus', function (Blueprint $table) {
            $table->id();
            // A qual loja (tenant) esta configuração pertence
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            
            // Qual jogo (ex: 'mtg', 'pokemon', 'yugioh')
            $table->string('game'); 

            // Textos Customizáveis (Nomes dos Menus)
            $table->string('name_singles')->default('Cartas Avulsas');
            $table->string('name_sealed')->default('Produtos Selados');
            $table->string('name_accessories')->default('Acessórios');
            $table->string('name_latest')->default('Últimos Cadastrados');
            $table->string('name_all_sets')->default('Todos os Sets');

            // Visibilidade (Checkboxes liga/desliga)
            $table->boolean('show_singles')->default(true);
            $table->boolean('show_sealed')->default(true);
            $table->boolean('show_accessories')->default(true);
            $table->boolean('show_latest')->default(true);
            $table->boolean('show_all_sets')->default(true);

            // Controle Geral e Ordenação
            $table->boolean('is_active')->default(true);
            $table->integer('position')->default(0);

            $table->timestamps();

            // Garante que uma loja não cadastre "Magic" duas vezes
            $table->unique(['store_id', 'game']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_game_menus');
    }
};