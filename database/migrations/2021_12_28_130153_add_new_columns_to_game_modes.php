<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnsToGameModes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('game_modes', function (Blueprint $table) {
            //
            $table->string('icon')->nullable();
            $table->string('background_color')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('game_modes', function (Blueprint $table) {
            //
            $table->dropColumn('icon');
            $table->dropColumn('background_color');
        });
    }
}
