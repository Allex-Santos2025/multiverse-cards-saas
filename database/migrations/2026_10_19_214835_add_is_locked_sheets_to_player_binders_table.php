<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_binders', function (Blueprint $table) {
            $table->boolean('is_locked_sheets')->default(false)->after('current_sheets_count');
        });
    }

    public function down(): void
    {
        Schema::table('player_binders', function (Blueprint $table) {
            $table->dropColumn('is_locked_sheets');
        });
    }
};
