<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameBonusAndCashColumnsOnWallets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wallets', function (Blueprint $table) {
            //
            $table->renameColumn('bonus', 'account1');
           
            
        });
        Schema::table('wallets', function (Blueprint $table) {
            //
          
            $table->renameColumn('cash', 'account2');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wallets', function (Blueprint $table) {
            //
            $table->renameColumn('account1', 'bonus');
            // $table->renameColumn('account2', 'cash');
            
        });
        Schema::table('wallets', function (Blueprint $table) {
            //
            // $table->renameColumn('account1', 'bonus');
            $table->renameColumn('account2', 'cash');
            
        });
    }
}
