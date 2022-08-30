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
        Schema::create('stakings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('trivia_id')->nullable()->constrained('trivias');
            $table->decimal('amount', 10, 3);
            $table->bigInteger('standard_odd');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stakings');
    }
};
