<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('player_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_user_id')->constrained('player_users')->cascadeOnDelete();
            
            $table->string('title', 50); // Ex: Casa, Trabalho
            $table->string('receiver_name', 100); 
            $table->string('zip_code', 20);
            $table->string('street', 255);
            $table->string('number', 50);
            $table->string('complement', 100)->nullable();
            $table->string('neighborhood', 100);
            $table->string('city', 100);
            $table->string('state', 2);
            
            $table->boolean('is_official')->default(false);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('player_addresses');
    }
};