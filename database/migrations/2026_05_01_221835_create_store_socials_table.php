<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('store_socials', function (Blueprint $table) {
            $table->id();
            // A ligação forte com a loja (se a loja for deletada, apaga os links)
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            
            $table->string('platform'); // Ex: 'instagram', 'youtube', 'tiktok', 'discord'
            $table->string('url');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_socials');
    }
};