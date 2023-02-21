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
        Schema::create('user_achievement_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('achievement_badge_id');
            $table->integer('count');
            $table->integer('is_claimed');
            $table->integer('is_rewarded');
            $table->integer('is_notified')->default(0);
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
        Schema::dropIfExists('user_achievement_badges');
    }
};
