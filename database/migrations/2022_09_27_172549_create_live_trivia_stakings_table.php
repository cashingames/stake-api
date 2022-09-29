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
        Schema::create('trivia_stakings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staking_id');
            $table->foreignId('game_session_id');
            $table->decimal('odds_applied')->default(1);
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
        Schema::dropIfExists('trivia_stakings');
    }
};
