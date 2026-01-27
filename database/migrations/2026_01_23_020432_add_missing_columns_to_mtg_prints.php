<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mtg_prints', function (Blueprint $table) {

            // 1. JSONs Faltantes
            if (!Schema::hasColumn('mtg_prints', 'related_uris')) {
                $table->json('related_uris')->nullable()->after('prices');
            }
            if (!Schema::hasColumn('mtg_prints', 'purchase_uris')) {
                $table->json('purchase_uris')->nullable()->after('related_uris');
            }

            // 2. Flags Booleanas Faltantes
            $missingBooleans = ['has_foil', 'nonfoil', 'etched', 'oversized', 'highres_image'];
            foreach ($missingBooleans as $col) {
                if (!Schema::hasColumn('mtg_prints', $col)) {
                    $table->boolean($col)->default(false)->after('digital');
                }
            }

            // 3. IDs e Status
            if (!Schema::hasColumn('mtg_prints', 'image_status')) {
                $table->string('image_status')->nullable()->default('missing')->after('released_at');
            }
            if (!Schema::hasColumn('mtg_prints', 'illustration_id')) {
                $table->uuid('illustration_id')->nullable()->after('artist');
            }
            if (!Schema::hasColumn('mtg_prints', 'card_back_id')) {
                $table->uuid('card_back_id')->nullable()->after('illustration_id');
            }

            // 4. IDs Externos (Integers/BigInts)
            $externalIds = [
                'mtgo_id', 'mtgo_foil_id', 'arena_id', 
                'tcgplayer_id', 'tcgplayer_etched_id', 'cardmarket_id'
            ];
            foreach ($externalIds as $col) {
                if (!Schema::hasColumn('mtg_prints', $col)) {
                    $table->unsignedBigInteger($col)->nullable()->after('multiverse_ids');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('mtg_prints', function (Blueprint $table) {
            // Removemos apenas se existirem
            $cols = [
                'related_uris', 'purchase_uris', 'image_status', 
                'illustration_id', 'card_back_id',
                'has_foil', 'nonfoil', 'etched', 'oversized', 'highres_image',
                'mtgo_id', 'mtgo_foil_id', 'arena_id', 
                'tcgplayer_id', 'tcgplayer_etched_id', 'cardmarket_id'
            ];
            $table->dropColumn($cols);
        });
    }
};
