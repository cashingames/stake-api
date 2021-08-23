<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('is_accepted');
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->enum('status', ['PENDING', 'ACCEPTED', 'DECLINED'])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('challenges', function (Blueprint $table) {
           $table->boolean('is_accepted')->nullable();
        });
    }
}
