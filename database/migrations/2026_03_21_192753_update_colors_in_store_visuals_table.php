<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('store_visuals', function (Blueprint $table) {
            // 1. Removemos as cores antigas que ficaram genéricas demais
            $table->dropColumn(['color_secondary', 'color_accent']);

            // 2. Adicionamos as novas Zonas de Cor (logo após a color_primary para manter organizado)
            $table->string('color_topbar_bg')->default('#1e293b')->after('color_primary');
            $table->string('color_header_bg')->default('#ffffff')->after('color_topbar_bg');
            $table->string('color_footer_bg')->default('#0f172a')->after('color_header_bg');
            
            // 3. Adicionamos o Override Manual do Menu (logo após o global_bg_color)
            $table->string('color_menu_text')->nullable()->after('global_bg_color');
        });
    }

    public function down(): void
    {
        Schema::table('store_visuals', function (Blueprint $table) {
            // Se der rollback, remove as colunas novas...
            $table->dropColumn([
                'color_topbar_bg',
                'color_header_bg',
                'color_footer_bg',
                'color_menu_text'
            ]);

            // ...e devolve as antigas
            $table->string('color_secondary')->default('#1e293b')->after('color_primary');
            $table->string('color_accent')->default('#f59e0b')->after('color_secondary');
        });
    }
};