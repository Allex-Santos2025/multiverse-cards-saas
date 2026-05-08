<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('store_shipping_settings', function (Blueprint $table) {
            $table->boolean('correios_impresso_modico')->default(false)->after('correios_mini_envios');
        });
    }

    public function down()
    {
        Schema::table('store_shipping_settings', function (Blueprint $table) {
            $table->dropColumn('correios_impresso_modico');
        });
    }
};