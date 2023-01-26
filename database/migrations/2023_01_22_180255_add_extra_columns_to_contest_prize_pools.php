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
        Schema::table('contest_prize_pools', function (Blueprint $table) {
            $table->string('each_prize')->comment("what each person within the range gets")->default('0');
            $table->string('net_prize')->comment("sum of all the prizes given in the range")->default('0');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contest_prize_pools', function (Blueprint $table) {
            $table->dropColumn('each_prize');
            $table->dropColumn('net_prize');
        });
    }
};
