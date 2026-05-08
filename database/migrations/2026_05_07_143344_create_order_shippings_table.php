<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_shippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            
            // Dados da entrega
            $table->string('shipping_method_key'); // ex: 'pac', 'carta_registrada', 'motoboy'
            $table->string('shipping_method_name'); // ex: 'Correios PAC', 'Entrega Expressa'
            $table->decimal('shipping_cost', 10, 2);
            $table->string('destination_zip_code', 20);
            
            // Rastreio e Status Logístico
            $table->string('tracking_code')->nullable();
            $table->string('shipping_status')->default('pending'); // pending, shipped, delivered, canceled
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_shippings');
    }
};