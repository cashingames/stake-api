<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('plan_id');
            $table->string('session_token');
            $table->tinyInteger('correct_count');
            $table->tinyInteger('wrong_count');
            $table->enum('state', ['PENDING', 'ONGOING', 'COMPLETED']);
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->dateTime('expected_end_time');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('plan_id')->references('id')->on('plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('games');
    }
}
