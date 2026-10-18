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
        Schema::create('ygo_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('attribute')->nullable();
            $table->string('race')->nullable();
            $table->string('type_line')->nullable();
            $table->unsignedTinyInteger('level')->nullable();
            $table->unsignedTinyInteger('scale')->nullable();
            $table->string('atk')->nullable();
            $table->string('def')->nullable();
            $table->text('rules_text')->nullable();
            $table->text('pendulum_text')->nullable();
            $table->timestamps();
        });

        Schema::create('ygo_prints', function (Blueprint $table) {
            $table->id();
            $table->string('card_code')->nullable()->index();
            $table->string('rarity')->nullable();
            $table->string('rarity_code')->nullable();
            $table->string('frame_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ygo_prints');
        Schema::dropIfExists('ygo_concepts');
    }
};
