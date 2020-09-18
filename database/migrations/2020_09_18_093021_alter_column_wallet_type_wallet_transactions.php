<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterColumnWalletTypeWalletTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('wallet_transactions', function (Blueprint $table) {
        //
        $table->dropColumn('wallet_type')->nullable();
    
     });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('wallet_transactions', function (Blueprint $table) {
        //
        $table->enum('wallet_type', ['BONUS', 'CASH']);
      });
    }
}
