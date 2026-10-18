<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ygo_prints', function (Blueprint $table) {
            $table->string('language_code', 10)->default('en')->after('card_code');
            $table->json('prices')->nullable()->after('flavor_text');
        });
    }

    public function down(): void
    {
        Schema::table('ygo_prints', function (Blueprint $table) {
            $table->dropColumn(['language_code', 'prices']);
        });
    }
};
