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
    Schema::create('plans', function (Blueprint $table) {
        $table->id();
        $table->string('name'); 
        $table->string('slug')->unique(); // O campo que o código busca no 'mount'
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2)->default(0);
        $table->string('billing_cycle')->default('monthly');
        $table->json('features')->nullable(); // Para os diferenciais do plano (MVP)
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
