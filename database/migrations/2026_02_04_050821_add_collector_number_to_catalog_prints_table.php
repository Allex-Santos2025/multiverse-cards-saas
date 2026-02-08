<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('catalog_prints', function (Blueprint $table) {
            // Criamos como string (varchar) pois existem cartas "15a", "RC1", "★", etc.
            $table->string('collector_number', 50)->nullable()->after('set_id');
            
            // Index para deixar a ordenação rápida
            $table->index(['set_id', 'collector_number']);
        });
    }

    public function down(): void
    {
        Schema::table('catalog_prints', function (Blueprint $table) {
            $table->dropColumn('collector_number');
        });
    }
};
