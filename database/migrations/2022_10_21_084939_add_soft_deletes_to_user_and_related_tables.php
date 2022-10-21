<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
