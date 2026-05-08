<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('store_shipping_settings', function (Blueprint $table) {
            // Motoboy
            $table->boolean('is_active_motoboy')->default(false);
            $table->string('motoboy_nome_exibicao')->default('Entrega via Motoboy');
            $table->text('motoboy_descricao')->nullable();
            $table->decimal('motoboy_valor_fixo', 8, 2)->default(15.00);
            $table->decimal('motoboy_taxa_percentual', 5, 2)->default(0.00);
            $table->boolean('motoboy_apenas_local')->default(true);

            // Uber Flash
            $table->boolean('is_active_uber_flash')->default(false);
            $table->string('uber_flash_nome_exibicao')->default('Uber Flash / 99 Entrega');
            $table->text('uber_flash_instrucoes')->nullable();
            $table->boolean('uber_flash_apenas_local')->default(true);
        });
    }

    public function down()
    {
        Schema::table('store_shipping_settings', function (Blueprint $table) {
            $table->dropColumn([
                'is_active_motoboy',
                'motoboy_nome_exibicao',
                'motoboy_descricao',
                'motoboy_valor_fixo',
                'motoboy_taxa_percentual',
                'motoboy_apenas_local',
                'is_active_uber_flash',
                'uber_flash_nome_exibicao',
                'uber_flash_instrucoes',
                'uber_flash_apenas_local'
            ]);
        });
    }
};