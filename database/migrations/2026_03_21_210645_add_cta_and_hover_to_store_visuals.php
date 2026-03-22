<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('store_visuals', function (Blueprint $table) {
        $table->string('color_cta')->nullable()->after('color_primary');
        $table->string('color_menu_hover')->nullable()->after('color_menu_text');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_visuals', function (Blueprint $table) {
            //
        });
    }
};
