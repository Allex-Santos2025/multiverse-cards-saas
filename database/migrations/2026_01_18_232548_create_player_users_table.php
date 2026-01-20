<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_users', function (Blueprint $table) {
            $table->id();
            
            // Dados de Identidade (Conforme Resource - Max 100)
            $table->string('name', 100);
            $table->string('surname', 100);
            $table->string('login', 100)->unique();
            $table->string('email', 100)->unique();
            
            // Segurança
            $table->string('password');
            
            // Documentação (Conforme Resource - Max 20)
            $table->string('document_number', 20)->nullable()->unique(); // CPF/CNPJ
            $table->string('id_document_number', 20)->nullable()->unique(); // RG/ID
            
            // Dados Pessoais e Fidelidade (Conforme Model e Resource)
            $table->string('phone_number', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->string('zip_code')->nullable(); // Previsto no Model fillable
            $table->string('preferred_language')->default('pt_BR'); // Previsto no Model fillable
            $table->integer('loyalty_points')->default(0); // Cast 'integer' no Model
            
            // Metadados e Status
            $table->json('data_json')->nullable(); // Cast 'array' no Model para preferências de TCG
            $table->boolean('is_active')->default(true);

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_users');
    }
};








