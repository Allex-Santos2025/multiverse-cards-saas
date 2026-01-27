<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sets', function (Blueprint $table) {
            $table->id();

            // Vínculo com o Jogo (Obrigatório)
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();

            // Identificadores
            $table->string('name');
            $table->string('code')->index(); // Código visual (ex: DMU, SV1)

            // CAMPO AGNÓSTICO (O "Pulo do Gato"):
            // Para Magic = UUID do Scryfall
            // Para Pokémon = ID da API (ex: base1)
            $table->string('api_id')->nullable()->index(); 

            // Metadados Universais
            $table->date('released_at')->nullable();
            $table->string('set_type')->nullable(); // core, expansion, promo
            $table->integer('card_count')->default(0);
            $table->string('icon_svg_uri')->nullable(); // URL do ícone

            // Flags Universais
            $table->boolean('is_fanmade')->default(false);
            $table->boolean('digital')->default(false);
            $table->boolean('foil_only')->default(false);

            $table->timestamps();

            // Constraints de Integridade:
            // Um jogo não pode ter dois sets com o mesmo código
            $table->unique(['game_id', 'code']);
            // Um jogo não pode ter dois sets com o mesmo ID de API
            $table->unique(['game_id', 'api_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sets');
    }
};
