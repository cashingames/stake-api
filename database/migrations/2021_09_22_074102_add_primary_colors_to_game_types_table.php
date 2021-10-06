<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPrimaryColorsToGameTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('game_types', function (Blueprint $table) {
            //
            $table->string('primary_color_1')->nullable();
            $table->string('primary_color_2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('game_types', function (Blueprint $table) {
            //
            $table->dropColumn(['primary_color_1', 'primary_color_2']);
        });
    }
}
