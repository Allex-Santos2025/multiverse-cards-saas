<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_admin_users', function (Blueprint $table) {
            $table->id();
            
            // Vínculo com a Loja (FK)
            // Deve ser nullable conforme seu Resource (status "à deriva")
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('set null');

            // Dados de Identidade (Conforme Resource - Max 100)
            $table->string('name', 100);
            $table->string('surname', 100);
            $table->string('login', 100)->unique();
            $table->string('email', 100)->unique();
            
            // Segurança
            $table->string('password');
            
            // Gestão Interna e RH (Conforme Model e Resource)
            $table->string('phone_number')->nullable(); // Previsto no Model fillable
            $table->json('permissions_json')->nullable(); // Cast 'array' no Model
            $table->date('hired_date')->nullable();
            
            // Status
            $table->boolean('is_active')->default(true);

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_admin_users');
    }
};
