<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ygo_prints', function (Blueprint $table) {
            if (Schema::hasColumn('ygo_prints', 'card_code')) {
                $table->renameColumn('card_code', 'api_id');
            }
            if (Schema::hasColumn('ygo_prints', 'printed_type')) {
                $table->renameColumn('printed_type', 'printed_type_line');
            }
        });

        Schema::table('ygo_prints', function (Blueprint $table) {
            if (!Schema::hasColumn('ygo_prints', 'collector_number')) {
                $table->string('collector_number')->nullable()->after('api_id');
            }
            if (!Schema::hasColumn('ygo_prints', 'language_code')) {
                $table->string('language_code', 10)->default('en')->after('collector_number');
            }
            if (!Schema::hasColumn('ygo_prints', 'printed_name')) {
                $table->string('printed_name')->nullable()->after('rarity_code');
            }
            if (!Schema::hasColumn('ygo_prints', 'prices')) {
                $table->json('prices')->nullable()->after('flavor_text');
            }
            if (!Schema::hasColumn('ygo_prints', 'cardmarket_id')) {
                $table->string('cardmarket_id')->nullable()->after('prices');
            }
            if (!Schema::hasColumn('ygo_prints', 'tcgplayer_id')) {
                $table->string('tcgplayer_id')->nullable()->after('cardmarket_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ygo_prints', function (Blueprint $table) {
            $table->dropColumn(['collector_number', 'language_code', 'printed_name', 'prices', 'cardmarket_id', 'tcgplayer_id']);
            $table->renameColumn('api_id', 'card_code');
            $table->renameColumn('printed_type_line', 'printed_type');
        });
    }
};
