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
        Schema::table('user_rewards', function (Blueprint $table) {
            $table->integer('reward_milestone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_rewards', function (Blueprint $table) {
            $table->dropColumn('reward_milestone');
        });
    }
};
