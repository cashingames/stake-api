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
        //
        Schema::table('trivias', function (Blueprint $table) {

            $table->bigInteger('game_duration')->default(60);
            $table->bigInteger('question_count')->default(10);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trivias', function (Blueprint $table) {
            $table->dropColumn('game_duration');
        });
        Schema::table('trivias', function (Blueprint $table) {
            $table->dropColumn('question_count');
        });
    }
};
