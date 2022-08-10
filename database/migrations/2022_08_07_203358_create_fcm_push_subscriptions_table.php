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
        Schema::create('fcm_push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->text('device_token')->nullable();
            $table->string('topic')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('guest_id')->nullable();
            $table->boolean('valid')->default(true);
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
        Schema::dropIfExists('fcm_push_subscriptions');
    }
};
