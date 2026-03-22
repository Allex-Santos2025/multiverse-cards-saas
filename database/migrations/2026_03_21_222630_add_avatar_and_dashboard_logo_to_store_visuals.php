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
        Schema::table('store_visuals', function (Blueprint $table) {
            $table->string('avatar_marketplace')->nullable()->after('logo_marketplace');
            $table->boolean('use_logo_dashboard')->default(true)->after('avatar_marketplace');
        });
    }

    public function down(): void
    {
        Schema::table('store_visuals', function (Blueprint $table) {
            $table->dropColumn(['avatar_marketplace', 'use_logo_dashboard']);
        });
    }
};
