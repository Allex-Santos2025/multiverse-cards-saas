<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_shipping_settings', function (Blueprint $table) {
            $table->id();
            // A chave mágica do SaaS: liga as regras à loja específica
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');

            // ==========================================
            // INTEGRAÇÕES GLOBAIS
            // ==========================================
            $table->boolean('is_active_melhor_envio')->default(false);
            $table->text('melhor_envio_token')->nullable();

            $table->boolean('is_active_frenet')->default(false);
            $table->text('frenet_token')->nullable();

            $table->boolean('is_active_correios')->default(false);
            $table->string('correios_codigo_adm')->nullable();
            $table->string('correios_cartao_postagem')->nullable();
            // Dica de segurança: No futuro usaremos a Facade Crypt para não deixar a senha em texto limpo no banco
            $table->string('correios_senha')->nullable(); 

            // ==========================================
            // SERVIÇOS E REGRAS
            // ==========================================
            
            // PAC / Correios Geral
            $table->boolean('correios_pac')->default(false);
            $table->string('correios_pac_nome_exibicao')->default('Correios PAC');
            $table->text('correios_pac_descricao')->nullable();
            $table->decimal('taxa_seguro_percentual', 5, 2)->default(2.00);
            $table->integer('prazo_manuseio_dias')->default(1);
            
            // Preparação para expansão
            $table->boolean('correios_sedex')->default(false);
            $table->boolean('correios_sedex10')->default(false);
            $table->boolean('correios_mini_envios')->default(false);

            // Carta Registrada / Impresso
            $table->boolean('is_active_carta_registrada')->default(false);
            $table->string('cr_nome_exibicao')->default('Impresso Nacional');
            $table->text('cr_descricao')->nullable();
            $table->decimal('cr_valor_fixo', 8, 2)->default(15.00);
            $table->decimal('cr_taxa_percentual', 5, 2)->default(0.00);
            $table->integer('cr_limite_cartas')->default(80);
            $table->integer('cr_prazo_dias')->default(7);
            $table->boolean('cr_apenas_singles')->default(true);

            // Retirada
            $table->boolean('is_active_retirada')->default(false);
            $table->string('retirada_nome_exibicao')->default('Entrega no Metrô (Agendar)');
            $table->text('retirada_instrucoes')->nullable();
            $table->boolean('retirada_apenas_local')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_shipping_settings');
    }
};