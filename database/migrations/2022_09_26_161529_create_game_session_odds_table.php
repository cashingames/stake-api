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
            $table->foreignId('game_session_id');
            $table->foreignId('odds_rule_id')->constrained('odds_rules');
            $table->decimal('odds_benefit', 10, 2)->default(0)->comment("added so as to maintain historical value because odds_benefit from odds_rules table can change");

            $table->timestamps();
           
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
