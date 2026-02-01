<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('changelogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');        // Título da novidade
            $table->string('slug')->unique(); // URL amigável
            $table->string('version')->nullable(); // Ex: v1.2.0
            $table->string('category');     // Ex: 'Recurso', 'Correção', 'Melhoria'
            
            // O que aparece na lista (estilo Log)
            $table->text('summary'); 
            
            // O conteúdo detalhado (Markdown)
            $table->longText('content'); 
            
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('changelogs');
    }
};
