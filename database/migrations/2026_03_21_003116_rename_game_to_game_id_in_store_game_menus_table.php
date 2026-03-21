<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_game_menus', function (Blueprint $table) {
            $table->renameColumn('game', 'game_id');
        });

        Schema::table('store_game_menus', function (Blueprint $table) {
            $table->unsignedBigInteger('game_id')->change();
        });
    }

    public function down(): void
    {
        Schema::table('store_game_menus', function (Blueprint $table) {
            $table->renameColumn('game_id', 'game');
        });
    }
}; // <--- ESSE PONTO E VÍRGULA AQUI É O QUE ESTÁ FALTANDO!