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
        Schema::create('challenge_stakings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staking_id');
            $table->foreignId('challenge_id');
            // $table->decimal('staking_amount', 15, 2)->comment("The amount to be staked by each player")->default(0);
            // $table->decimal('amount_to_win', 15, 2)->comment("Final amount to be credited to the winner")->default(0);
            $table->decimal('platform_charge', 10, 2)->comment("Amount that goes to platform for moderating the stake")->default(0);
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
        Schema::dropIfExists('challenge_stakings');
    }
};
