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
            $table->string('rarity')->nullable()->after('collector_number');
            $table->string('type_line')->nullable()->after('rarity');
            $table->decimal('cmc', 8, 2)->default(0)->after('type_line');
            $table->string('mana_cost')->nullable()->after('cmc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalog_prints', function (Blueprint $table) {
            //
        });
    }
};
