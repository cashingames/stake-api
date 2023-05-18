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
        Schema::table('wallets', function (Blueprint $table) {
            $table->renameColumn('non_withdrawable_balance', 'non_withdrawable');
        });
        Schema::table('wallets', function (Blueprint $table) {
            $table->renameColumn('withdrawable_balance', 'withdrawable');
        });
        Schema::table('wallets', function (Blueprint $table) {
            $table->renameColumn('bonus_balance', 'bonus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->renameColumn('non_withdrawable', 'non_withdrawable_balance');
        });
        Schema::table('wallets', function (Blueprint $table) {
            $table->renameColumn('withdrawable', 'withdrawable_balance');
        });
        Schema::table('wallets', function (Blueprint $table) {
            $table->renameColumn('bonus','bonus_balance');
        });
    }
};
