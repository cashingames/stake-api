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
        Schema::table('exhibition_stakings', function (Blueprint $table) {
            $table->renameColumn('trivia_id', 'game_session_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exhibition_stakings', function (Blueprint $table) {
            $table->renameColumn('game_session_id', 'trivia_id');
        });
    }
};
