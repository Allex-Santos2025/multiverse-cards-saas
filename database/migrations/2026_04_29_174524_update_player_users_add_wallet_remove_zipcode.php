<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('player_users', function (Blueprint $table) {
            // Adiciona a Carteira
            $table->decimal('balance', 15, 2)->default(0.00)->after('preferred_language');
            $table->string('pix_key')->nullable()->after('balance');
            
            // Remove o cep antigo
            $table->dropColumn('zip_code');
        });
    }

    public function down()
    {
        Schema::table('player_users', function (Blueprint $table) {
            $table->dropColumn(['balance', 'pix_key']);
            $table->string('zip_code', 255)->nullable();
        });
    }
};