<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            
            // Informações Básicas
            $table->string('title');
            $table->string('slug')->unique();
            
            // Categorização e Estilo
            $table->string('category')->nullable();
            $table->string('category_color')->default('orange'); // Para definir se o badge é orange-500, blue-600, etc.
            
            // Conteúdo Visual e Texto
            $table->string('image')->nullable();
            $table->text('excerpt')->nullable(); // O resumo do card principal
            $table->longText('content'); // O corpo da matéria
            
            // Lógica de Exibição
            $table->boolean('is_featured')->default(false); // Define se é o destaque grande
            $table->timestamp('published_at')->nullable(); // Para agendar postagens
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};