<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['instagram_url', 'facebook_url']);
        });
    }

    public function down()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('instagram_url')->nullable()->after('support_email');
            $table->string('facebook_url')->nullable()->after('instagram_url');
        });
    }
};