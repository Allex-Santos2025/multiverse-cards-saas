<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            
            // Referência ao estoque (nullable caso o item seja excluído do banco no futuro)
            $table->unsignedBigInteger('stock_item_id')->nullable(); 
            
            // "Fotografia" do item no momento da compra
            $table->string('item_name'); 
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};