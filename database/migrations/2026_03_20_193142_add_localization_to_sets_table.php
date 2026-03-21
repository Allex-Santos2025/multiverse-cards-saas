<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sets', function (Blueprint $table) {
            // Adiciona as colunas permitindo nulo, para não quebrar os registros antigos
            $table->string('name_pt')->nullable()->after('name');
            $table->json('translations')->nullable()->after('name_pt');
        });
    }

    public function down(): void
    {
        Schema::table('sets', function (Blueprint $table) {
            $table->dropColumn(['name_pt', 'translations']);
        });
    }
};