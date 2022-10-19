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
        Schema::table('stakings', function(Blueprint $table){    
            $table->renameColumn('standard_odd', 'odd_applied_during_staking');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('odds_rules', function(Blueprint $table){ 
            $table->renameColumn('odd_applied_during_staking', 'standard_odd');
        });
    }
};
