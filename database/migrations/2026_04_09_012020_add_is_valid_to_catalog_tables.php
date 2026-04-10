<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('catalog_prints', function (Blueprint $table) {
            // Adiciona a coluna, define como true por padrão e CRIA UM ÍNDICE (isso é a chave da velocidade)
            $table->boolean('is_valid')->default(true)->index();
        });

        Schema::table('catalog_concepts', function (Blueprint $table) {
            $table->boolean('is_valid')->default(true)->index();
        });
    }

    public function down()
    {
        Schema::table('catalog_prints', function (Blueprint $table) {
            $table->dropColumn('is_valid');
        });

        Schema::table('catalog_concepts', function (Blueprint $table) {
            $table->dropColumn('is_valid');
        });
    }
};