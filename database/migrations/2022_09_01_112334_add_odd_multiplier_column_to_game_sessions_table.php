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
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->decimal('odd_multiplier', $precision = 9, $scale = 2)->default(1);
            $table->string('odd_condition')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropColumn(['odd_multiplier']);
        });
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropColumn(['odd_condition']);
        });
    }
};
