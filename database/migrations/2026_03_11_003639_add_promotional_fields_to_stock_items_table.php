<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->decimal('discount_percent', 5, 2)->default(0)->after('price');
            $table->timestamp('discount_start')->nullable()->after('discount_percent');
            $table->timestamp('discount_end')->nullable()->after('discount_start');
            $table->boolean('is_promotion')->default(false)->index()->after('discount_end');
        });
    }

    public function down(): void
    {
        Schema::table('stock_items', function (Blueprint $table) {
            $table->dropColumn(['discount_percent', 'discount_start', 'discount_end', 'is_promotion']);
        });
    }
};