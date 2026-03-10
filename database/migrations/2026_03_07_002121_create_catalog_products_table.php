<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_products', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com o Jogo (Nullable para Acessórios Globais como Shields genéricos)
            // Assumindo que sua tabela de jogos se chama 'games' ou 'tcg_games' (ajuste se necessário)
            $table->foreignId('game_id')->nullable()->constrained('games')->nullOnDelete();
            
            // Tipo do produto para facilitar filtros
            $table->enum('type', ['sealed', 'accessory'])->index();
            
            // Dados Básicos
            $table->string('name')->index(); // Indexado para busca rápida do lojista
            $table->text('description')->nullable();
            
            // Código de Barras / EAN / SKU para bipar no balcão no futuro
            $table->string('barcode')->nullable()->unique();
            
            // Imagem salva no nosso storage (ex: catalog/products/imagem.jpg)
            $table->string('image_path')->nullable();
            
            // Controle de Exibição
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes(); // Essencial para a ferramenta de unificação (mesclar)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_products');
    }
};
