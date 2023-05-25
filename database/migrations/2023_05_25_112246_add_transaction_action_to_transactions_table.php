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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('transaction_action')->default('');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['viable_date']);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['settled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['transaction_action']);
        });
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('viable_date')->nullable();
        });
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('settled_at')->nullable();
        });
    }
};
