<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            // Domínio
            $table->boolean('use_custom_domain')->default(false)->after('url_slug');
            $table->string('domain')->nullable()->after('use_custom_domain');
            
            // Dados Fiscais
            $table->string('document')->nullable()->after('name'); 
            $table->string('corporate_name')->nullable()->after('document'); 
            $table->boolean('is_ie_exempt')->default(false)->after('corporate_name');
            $table->string('state_registration')->nullable()->after('is_ie_exempt'); 
            
            // Contato e Redes Sociais
            $table->string('phone')->nullable()->after('state_registration');
            $table->string('support_email')->nullable()->after('phone');
            $table->string('instagram_url')->nullable()->after('support_email');
            $table->string('facebook_url')->nullable()->after('instagram_url');
            
            // Endereço Logístico
            $table->string('street')->nullable()->after('store_state_code');
            $table->string('number')->nullable()->after('street');
            $table->string('complement')->nullable()->after('number');
            $table->string('neighborhood')->nullable()->after('complement');
            $table->string('city')->nullable()->after('neighborhood');
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'use_custom_domain', 'domain',
                'document', 'corporate_name', 'is_ie_exempt', 'state_registration', 
                'phone', 'support_email', 'instagram_url', 'facebook_url', 
                'street', 'number', 'complement', 'neighborhood', 'city'
            ]);
        });
    }
};