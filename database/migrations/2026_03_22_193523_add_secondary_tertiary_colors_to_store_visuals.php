<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('store_visuals', function (Blueprint $table) {
            // Adicionamos as duas colunas logo após a cor primária para manter a organização no BD
            $table->string('color_secondary')->nullable()->after('color_primary');
            $table->string('color_tertiary')->nullable()->after('color_secondary');
        });
    }

    public function down()
    {
        Schema::table('store_visuals', function (Blueprint $table) {
            $table->dropColumn(['color_secondary', 'color_tertiary']);
        });
    }
};