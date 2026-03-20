<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_menus', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com a Loja
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            
            // Para criar submenus (Dropdowns)
            $table->foreignId('parent_id')->nullable()->constrained('store_menus')->nullOnDelete();
            
            // Nome que aparece para o cliente
            $table->string('name');
            
            // Regra de negócio: 'game', 'edition', 'sealed', 'promo', 'link'
            $table->string('target_type');
            
            // O alvo em si (ex: 'pokemon', 'id_da_colecao', ou uma URL)
            $table->string('target_value')->nullable();
            
            // Ordem de exibição no menu
            $table->integer('sort_order')->default(0);
            
            // Status (Ativo/Inativo)
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_menus');
    }
};