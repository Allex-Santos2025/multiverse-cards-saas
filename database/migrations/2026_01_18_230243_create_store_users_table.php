<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_users', function (Blueprint $table) {
            $table->id();
            
            // Dados de Identidade (Conforme Resource - Max 100)
            $table->string('name', 100);
            $table->string('surname', 100);
            $table->string('login', 100)->unique();
            $table->string('social_name')->nullable(); // Nome Social previsto no Model
            
            // Dados de Acesso e Verificação (Necessário para MustVerifyEmail)
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            // Documentação e Contato (Conforme Resource e Model)
            $table->string('document_number', 20)->nullable()->unique(); // CPF/CNPJ
            $table->string('id_document_number')->nullable(); // RG ou outro ID
            $table->string('phone_number', 20)->nullable();
            $table->string('company_phone')->nullable();
            
            // Status e Controle
            $table->boolean('is_active')->default(true);
            
            // Vínculos com Loja (Nullables para permitir a criação do usuário antes da loja)
            // 'store_id' é exigido pelo seu Resource; 'current_store_id' é exigido pelo seu Model.
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('current_store_id')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_users');
    }
};
