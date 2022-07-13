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
        Schema::create('challenge_game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_type_id');
            $table->foreignId('category_id');
            $table->foreignId('user_id');
            $table->timestamp("start_time")->nullable();
            $table->timestamp("end_time")->nullable();
            $table->string("session_token")->unique();
            $table->enum('state', ['PENDING', 'ONGOING', 'PAUSED', 'COMPLETED']);
            $table->tinyInteger("correct_count")->default(0)->nullable();
            $table->tinyInteger("wrong_count")->default(0)->nullable();
            $table->tinyInteger("total_count")->default(0)->nullable();
            $table->tinyInteger("points_gained")->default(0)->nullable();
            $table->foreignId("challenge_id");
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
        Schema::dropIfExists('challenge_game_sessions');
    }
};
