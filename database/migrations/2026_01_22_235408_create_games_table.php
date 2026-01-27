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
    Schema::create('games', function (Blueprint $table) {
        $table->id();

        // Identificação
        $table->string('name')->unique(); // Obrigatório e único
        $table->string('url_slug')->unique()->nullable(); // Essencial para rotas amigáveis
        $table->string('publisher')->nullable();

        // Configurações de API e Ingestão
        $table->string('api_url')->nullable();
        $table->string('ingestor_class')->nullable(); // Classe PHP que processa o jogo
        $table->integer('rate_limit_ms')->default(100)->nullable(); // Controle de taxa

        // Dados do Jogo
        $table->json('formats_list')->nullable(); // Lista de formatos (Commander, Standard, etc)

        // Status
        $table->boolean('is_active')->default(true);

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
