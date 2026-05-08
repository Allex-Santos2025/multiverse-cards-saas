<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('store_shipping_settings', function (Blueprint $table) {
            $table->string('correios_sedex_nome_exibicao')->default('Correios Sedex');
            $table->text('correios_sedex_descricao')->nullable();
            
            $table->string('correios_sedex10_nome_exibicao')->default('Correios Sedex 10');
            $table->text('correios_sedex10_descricao')->nullable();
            
            $table->string('correios_mini_envios_nome_exibicao')->default('Correios Mini Envios');
            $table->text('correios_mini_envios_descricao')->nullable();
            
            $table->string('correios_impresso_modico_nome_exibicao')->default('Impresso Módico');
            $table->text('correios_impresso_modico_descricao')->nullable();
        });
    }

    public function down()
    {
        Schema::table('store_shipping_settings', function (Blueprint $table) {
            $table->dropColumn([
                'correios_sedex_nome_exibicao', 
                'correios_sedex_descricao',
                'correios_sedex10_nome_exibicao', 
                'correios_sedex10_descricao',
                'correios_mini_envios_nome_exibicao', 
                'correios_mini_envios_descricao',
                'correios_impresso_modico_nome_exibicao', 
                'correios_impresso_modico_descricao',
            ]);
        });
    }
};