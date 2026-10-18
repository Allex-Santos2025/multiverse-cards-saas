<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ygo_prints', function (Blueprint $table) {
            $table->text('printed_text')->nullable()->after('frame_type');
            $table->string('printed_type')->nullable()->after('printed_text');
            $table->text('flavor_text')->nullable()->after('printed_type');
        });
    }

    public function down(): void
    {
        Schema::table('ygo_prints', function (Blueprint $table) {
            $table->dropColumn(['printed_text', 'printed_type', 'flavor_text']);
        });
    }
};
