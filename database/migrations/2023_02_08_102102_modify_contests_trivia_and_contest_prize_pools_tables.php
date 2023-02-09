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
        Schema::table('contests', function (Blueprint $table) {
            $table->string('prize_type')->default('POINTS');
        });

        Schema::table('trivias', function (Blueprint $table) {
            $table->tinyInteger('prize_multiplier')->comment("for computing how many prizes the user gets")->default(1);
        });

        Schema::table('contest_prize_pools', function (Blueprint $table) {
            $table->string('prize_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('prize_type');
        });

        Schema::table('trivias', function (Blueprint $table) {
            $table->dropColumn('prize_multiplier');
        });

        Schema::table('contest_prize_pools', function (Blueprint $table) {
            $table->string('prize_type')->nullable(false)->change();
        });
    }
};
