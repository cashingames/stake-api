<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsFreeAndUsedCountColumnsToPlansAndUserPlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('plans', function (Blueprint $table) {
            //
            $table->boolean('is_free')->nullable()->default(false);
        });

        Schema::table('user_plans', function (Blueprint $table) {
            //
            $table->tinyInteger('used_count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('plans', function (Blueprint $table) {
            //
            $table->dropColumn('is_free');
        });

        Schema::table('user_plans', function (Blueprint $table) {
            //
            $table->dropColumn('used_count');
        });
    }
}
