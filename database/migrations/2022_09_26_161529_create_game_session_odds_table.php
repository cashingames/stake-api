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
        Schema::create('game_session_odds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_session_id');
            $table->unsignedBigInteger('odds_rule_id');
            $table->decimal('odds_benefit', 10, 2)->default(0)->comment("added so as to maintain historical value because odds_benefit from odds_rules table can change");

            $table->timestamps();
            $table->foreign('game_session_id')->references('id')->on('game_sessions');
            $table->foreign('odds_rule_id')->references('id')->on('odds_rules');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('game_session_odds');
    }
};
