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
        Schema::table('store_game_menus', function (Blueprint $table) {
            $table->string('name_updates')->nullable()->after('name_all_sets');
            $table->boolean('show_updates')->default(true)->after('show_all_sets');
        });
    }

    public function down(): void
    {
        Schema::table('store_game_menus', function (Blueprint $table) {
            $table->dropColumn(['name_updates', 'show_updates']);
        });
    }
};
