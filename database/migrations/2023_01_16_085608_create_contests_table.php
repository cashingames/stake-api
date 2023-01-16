<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
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
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_date')->default(Carbon::now('Africa/Lagos'));
            $table->dateTime('end_date')->default(Carbon::now('Africa/Lagos'));
            $table->string('name');
            $table->string('description');
            $table->string('display_name');
            $table->enum('contest_type',['LIVE_TRIVIA','LEADERBOARD','CHALLENGE']);
            $table->enum('entry_mode',['FREE','PAY_WITH_POINTS','PAY_WITH_MONEY','MINIMUM_POINTS']);
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
        Schema::dropIfExists('contests');
    }
};
