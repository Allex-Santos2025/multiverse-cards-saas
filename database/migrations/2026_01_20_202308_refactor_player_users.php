<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_users', function (Blueprint $table) {
            // 1. Senha opcional para quem entrar via Redes Sociais
            $table->string('password')->nullable()->change();

            // 2. Campos para sincronismo (Google, Discord, etc)
            $table->string('provider_name')->after('email')->nullable();
            $table->string('provider_id')->after('provider_name')->nullable();
            $table->string('avatar')->after('provider_id')->nullable();

            // 3. Campo para pular a verificação manual no login social
            $table->timestamp('email_verified_at')->after('avatar')->nullable();

            // 4. Mudança de 'login' para 'nickname' (Identidade Gamer)
            $table->renameColumn('login', 'nickname');
        });
    }

    public function down(): void
    {
        Schema::table('player_users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
            $table->dropColumn(['provider_name', 'provider_id', 'avatar', 'email_verified_at']);
            $table->renameColumn('nickname', 'login');
        });
    }
};