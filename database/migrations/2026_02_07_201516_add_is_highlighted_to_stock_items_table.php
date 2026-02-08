<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_items', function (Blueprint $table) {
            // Adiciona a coluna de destaque (coração)
            // O 'after' é opcional, mas ajuda a organizar no banco
            $table->boolean('is_highlighted')->default(false)->after('real_photos');
        });
    }

    public function down()
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropColumn('is_highlighted');
        });
    }
};