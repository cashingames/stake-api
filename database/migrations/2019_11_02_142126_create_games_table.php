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
            $table->unsignedBigInteger('category_id');
            $table->string('session_token');
            $table->timestamp('start_time');
            $table->timestamp('expected_end_time');
            $table->enum('state', ['PENDING', 'ONGOING', 'PAUSED', 'COMPLETED']);
            $table->timestamp('end_time')->nullable();
            $table->tinyInteger('correct_count')->nullable()->default(0);
            $table->tinyInteger('wrong_count')->nullable()->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('plan_id')->references('id')->on('plans');
            $table->foreign('category_id')->references('id')->on('categories');
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
