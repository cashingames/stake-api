<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('state')->nullable();
            $table->string('avatar')->nullable();
            $table->string('account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('referral_code');
            $table->string('referrer')->nullable();
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
        Schema::dropIfExists('profiles');
    }
}
