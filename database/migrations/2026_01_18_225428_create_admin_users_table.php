<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table) {
            $table->id();
            
            // Dados de Identidade (Baseados no Resource e Model)
            $table->string('name', 100); 
            $table->string('surname')->nullable(); // Previsto no Model fillable
            $table->string('login')->unique()->nullable(); // Previsto no Model fillable
            
            // Dados de Acesso (Baseados no Resource)
            $table->string('email', 100)->unique();
            $table->string('password');
            
            // Status (Baseado no Resource)
            $table->boolean('is_active')->default(true);
            
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
