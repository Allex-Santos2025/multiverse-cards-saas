<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mtg_prints', function (Blueprint $table) {

            // 1. Printed Name
            if (!Schema::hasColumn('mtg_prints', 'printed_name')) {
                $table->string('printed_name')->nullable()->comment('Nome impresso (ex: PT-BR)');
            }

            // 2. Printed Type Line
            if (!Schema::hasColumn('mtg_prints', 'printed_type_line')) {
                $table->string('printed_type_line')->nullable();
            }

            // 3. Printed Text
            if (!Schema::hasColumn('mtg_prints', 'printed_text')) {
                $table->text('printed_text')->nullable();
            }

            // 4. Flavor Name
            if (!Schema::hasColumn('mtg_prints', 'flavor_name')) {
                $table->string('flavor_name')->nullable();
            }

            // 5. Variation Of
            if (!Schema::hasColumn('mtg_prints', 'variation_of')) {
                $table->uuid('variation_of')->nullable()->comment('ID do Scryfall da carta base');
            }

            // 6. Purchase URIs
            if (!Schema::hasColumn('mtg_prints', 'purchase_uris')) {
                $table->json('purchase_uris')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('mtg_prints', function (Blueprint $table) {
            $columns = [
                'printed_name',
                'printed_type_line',
                'printed_text',
                'flavor_name',
                'variation_of',
                'purchase_uris'
            ];

            // Remove apenas se existir, para evitar erros no rollback
            foreach ($columns as $column) {
                if (Schema::hasColumn('mtg_prints', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
