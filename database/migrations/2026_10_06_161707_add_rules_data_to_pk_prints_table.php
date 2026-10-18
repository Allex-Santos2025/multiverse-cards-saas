<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pk_prints', function (Blueprint $table) {
            $table->json('attacks')->nullable()->after('flavor_text');
            $table->json('abilities')->nullable()->after('attacks');
            $table->text('rules_text')->nullable()->after('abilities');
        });
    }

    public function down(): void
    {
        Schema::table('pk_prints', function (Blueprint $table) {
            $table->dropColumn(['attacks', 'abilities', 'rules_text']);
        });
    }
};
