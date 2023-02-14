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
        Schema::create('achievement_badges', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('milestone_type');
            $table->integer('milestone');
            $table->string('description');
            $table->string('reward_type');
            $table->string('reward');
            $table->string('medal');
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
        Schema::dropIfExists('achievement_badges');
    }
};
