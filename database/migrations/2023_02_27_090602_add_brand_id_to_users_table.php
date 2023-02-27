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
        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('brand_id')->default(1);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('source');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('source');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('brand_id');
        });
    }
};
