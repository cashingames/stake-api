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
        Schema::rename("odds_conditions_and_rules", "odds_rules");
        Schema::table('odds_rules', function(Blueprint $table){
            $table->enum('odds_operation', ['+', '-', '*']);
            $table->renameColumn('condition', 'display_name');
        });

        Schema::rename('standard_odds', 'staking_odds');
        Schema::table('staking_odds', function(Blueprint $table){
            $table->string('module')->nullable();
        });

        Schema::table('stakings', function(Blueprint $table){
            $table->renameColumn('amount', 'amount_staked');
            $table->decimal('amount_won', 15, 2)->default(0);
        });

        Schema::table('exhibition_stakings', function(Blueprint $table){
            $table->decimal('odds_applied')->after('game_session_id')->default(1);
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
            $table->dropColumn('odds_operation');
            $table->renameColumn('display_name', 'condition');
        });
        Schema::rename("odds_rules", "odds_conditions_and_rules");

        Schema::table('staking_odds', function(Blueprint $table){
            $table->dropColumn(['module']);
        });
        Schema::rename('staking_odds', 'standard_odds');

        Schema::table('stakings', function (Blueprint $table) {
            $table->renameColumn('amount_staked', 'amount');
            $table->dropColumn(['amount_won']);
        });

        Schema::table('exhibition_stakings', function (Blueprint $table) {
            $table->dropColumn(['odds_applied']);
        });
    }
};
