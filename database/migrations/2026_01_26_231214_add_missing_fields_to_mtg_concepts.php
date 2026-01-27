<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mtg_concepts', function (Blueprint $table) {
            $table->string('layout')->nullable()->after('oracle_id'); // normal, transform, etc.
            $table->string('defense')->nullable()->after('toughness'); // Para Battles
            $table->json('card_faces')->nullable()->after('legalities'); // Backup completo das faces
        });
    }

    public function down(): void
    {
        Schema::table('mtg_concepts', function (Blueprint $table) {
            $table->dropColumn(['layout', 'defense', 'card_faces']);
        });
    }
};

