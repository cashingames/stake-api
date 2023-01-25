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
        Schema::create('contest_prize_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id');
            $table->smallInteger('rank_from');
            $table->smallInteger('rank_to');
            $table->string('prize');
            $table->string('prize_type');
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
        Schema::dropIfExists('contest_prize_pools');
    }
};
