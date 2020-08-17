<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RenameEnumWalletType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('wallet_transactions', function (Blueprint $table) {
        //     //
        //     $table->enum('wallet_type', ['CREDITS', 'WINNINGS'])->change();
        DB::statement("ALTER TABLE wallet_transactions MODIFY wallet_type ENUM('WINNINGS' , 'CREDITS') NOT NULL");
        //  });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('wallet_transactions', function (Blueprint $table) {
        //     //
        //     $table->enum('wallet_type', ['BONUS', 'CASH'])->change();
        // });
        DB::statement("ALTER TABLE wallet_transactions MODIFY wallet_type ENUM('CASH' , 'BONUS') NOT NULL");
    }
}
