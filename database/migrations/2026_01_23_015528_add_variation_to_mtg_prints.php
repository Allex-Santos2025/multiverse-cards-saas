<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mtg_prints', function (Blueprint $table) {
            // Adiciona a coluna variation se ela não existir
            if (!Schema::hasColumn('mtg_prints', 'variation')) {
                $table->boolean('variation')->default(false)->after('reprint');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mtg_prints', function (Blueprint $table) {
            if (Schema::hasColumn('mtg_prints', 'variation')) {
                $table->dropColumn('variation');
            }
        });
    }
};
