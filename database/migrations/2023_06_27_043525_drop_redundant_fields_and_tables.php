<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropColumn(['plan_id', 'trivia_id', 'coins_earned']);
        });
        Schema::dropIfExists('plans');
        Schema::dropIfExists('user_plans');
        Schema::dropIfExists('weekly_point_aggregates');
        Schema::dropIfExists('user_coin_transactions');
        Schema::dropIfExists('user_coins');
        Schema::dropIfExists('trivias');
        Schema::dropIfExists('trivia_stakings');
        Schema::dropIfExists('trivia_challenge_questions');
        Schema::dropIfExists('live_trivia_user_payments');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('challenge_game_sessions');
        Schema::dropIfExists('bot_profiles');
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};