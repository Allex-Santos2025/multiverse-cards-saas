<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            
            // Identidade e URL (Conforme Resource e Model)
            $table->string('name');
            $table->string('url_slug', 50)->unique(); 
            $table->text('slogan')->nullable(); // Suporta os 500 caracteres do Resource

            // Margens Financeiras (Conforme Model casts decimal:3)
            $table->decimal('purchase_margin_cash', 8, 3)->default(0.400);
            $table->decimal('purchase_margin_credit', 8, 3)->default(0.300);
            $table->decimal('max_loyalty_discount', 8, 3)->default(0.200);
            $table->decimal('pix_discount_rate', 8, 3)->default(0.050);

            // Localização e Design (Conforme Model fillable)
            $table->string('store_zip_code')->nullable();
            $table->string('store_state_code', 2)->nullable();
            $table->string('logo_path')->nullable();
            $table->string('banner_path')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();

            // Status e Configurações
            $table->boolean('is_active')->default(true);
            $table->boolean('is_template')->default(false);

            // Relacionamentos (Chaves Estrangeiras)
            // A tabela 'store_users' deve ser migrada antes desta.
            $table->foreignId('owner_user_id')->constrained('store_users')->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};