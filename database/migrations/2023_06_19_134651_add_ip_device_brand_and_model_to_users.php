<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_ip_address')->nullable();
            $table->string('device_model')->nullable();
            $table->string('device_brand')->nullable();
            $table->string('device_token')->nullable();
            $table->string('login_ip_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['login_ip_address']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['device_token']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['device_brand']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['device_model']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['registration_ip_address']);
        });
       
    }
};
