<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_visuals', function (Blueprint $table) {
            $table->id();
            // Trava de Multitenancy
            $table->foreignId('store_id')->constrained()->onDelete('cascade');

            // ==========================================
            // 1. O BÁSICO (Logos e Cores Principais)
            // ==========================================
            $table->string('color_primary')->default('#2563eb');
            $table->string('color_secondary')->default('#1e293b');
            $table->string('color_accent')->default('#f59e0b');
            
            $table->string('logo_main')->nullable();
            $table->string('logo_footer')->nullable();
            $table->string('logo_marketplace')->nullable();
            $table->string('favicon')->nullable();

            // ==========================================
            // 2. O PRO (Customização de Estilo e Temática)
            // ==========================================
            $table->string('font_family_base')->default('Inter, sans-serif'); 
            $table->string('border_radius_base')->default('0.375rem'); // Arredondamento
            $table->string('global_bg_color')->default('#ffffff'); // Para temas Dark ou de Halloween

            // ==========================================
            // 3. O PREMIUM (Imersão, Imagens e Código)
            // ==========================================
            $table->string('header_bg_image')->nullable(); // Fundo da barra do logo
            $table->string('global_bg_image')->nullable(); // Pôster global da loja
            $table->longText('custom_css')->nullable();    // Liberdade absoluta (Bordas foil, efeitos 3D, etc)
            
            // ==========================================
            // 4. O PAGE BUILDER (Motor de Blocos)
            // ==========================================
            // Salva a ordem, quantidade e visibilidade das vitrines e banners (Premium/Pro)
            $table->json('home_layout_config')->nullable(); 

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_visuals');
    }
};