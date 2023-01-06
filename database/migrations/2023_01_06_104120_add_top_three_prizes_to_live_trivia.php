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
        Schema::table('trivias', function (Blueprint $table) {
            $table->decimal('first_prize', 10, 2)->default(0);
            $table->decimal('second_prize', 10, 2)->default(0);
            $table->decimal('third_prize', 10, 2)->default(0);
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
            $table->dropColumn(['first_prize','second_prize','third_prize' ]);
        });
    }
};
