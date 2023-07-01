<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('bot_profiles');
        Schema::dropIfExists('challenges');
        Schema::dropIfExists('challenge_boosts');
        Schema::dropIfExists('challenge_game_sessions');
        Schema::dropIfExists('challenge_questions');
        Schema::dropIfExists('challenge_requests');
        Schema::dropIfExists('contests');
        Schema::dropIfExists('contest_prize_pools');
        Schema::dropIfExists('exhibition_stakings');
        Schema::dropIfExists('live_trivia_user_payments');
        Schema::dropIfExists('stakings');
        Schema::dropIfExists('staking_odds');
        Schema::dropIfExists('staking_odds_rules');
        Schema::dropIfExists('trivias');
        Schema::dropIfExists('trivia_challenge_questions');
        Schema::dropIfExists('trivia_questions');
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('weekly_point_aggregates');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('trivias', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id');
            $table->foreignId('game_mode_id');
            $table->foreignId('game_type_id');
            $table->bigInteger('point_eligibility');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->bigInteger('grand_price');
            $table->timestamps();
        });
        Schema::create('trivia_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trivia_id');
            $table->foreignId('question_id');
            $table->timestamps();
        });
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('category_id');
            $table->bigInteger('opponent_id');
            $table->enum('status',['PENDING','ACCEPTED','DECLINED']);
            $table->timestamps();
        });
        Schema::create('challenge_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id');
            $table->foreignId('challenge_game_session_id');
            $table->foreignId('challenge_id');
            $table->timestamps();
        });
        Schema::create('challenge_game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_type_id');
            $table->foreignId('category_id');
            $table->foreignId('user_id');
            $table->timestamp("start_time")->nullable();
            $table->timestamp("end_time")->nullable();
            $table->string("session_token")->unique();
            $table->enum('state', ['PENDING', 'ONGOING', 'PAUSED', 'COMPLETED']);
            $table->tinyInteger("correct_count")->default(0)->nullable();
            $table->tinyInteger("wrong_count")->default(0)->nullable();
            $table->tinyInteger("total_count")->default(0)->nullable();
            $table->tinyInteger("points_gained")->default(0)->nullable();
            $table->foreignId("challenge_id");
            $table->timestamps();
        });
        Schema::create('stakings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->decimal('amount', 10, 2);
            $table->tinyInteger('standard_odd')->default(1);
            $table->timestamps();
        });
        Schema::create('trivia_stakings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staking_id');
            $table->foreignId('game_session_id');
            $table->decimal('odds_applied')->default(1);
            $table->timestamps();
        });
        Schema::create('challenge_stakings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staking_id');
            $table->foreignId('challenge_id');
            // $table->decimal('staking_amount', 15, 2)->comment("The amount to be staked by each player")->default(0);
            // $table->decimal('amount_to_win', 15, 2)->comment("Final amount to be credited to the winner")->default(0);
            $table->decimal('platform_charge', 10, 2)->comment("Amount that goes to platform for moderating the stake")->default(0);
            $table->timestamps();
        });
        Schema::create('live_trivia_user_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trivia_id')->constrained('trivias');
            $table->foreignId('user_id');
            $table->timestamps();
        });
        Schema::create('contests', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_date')->default(Carbon::now());
            $table->dateTime('end_date')->default(Carbon::now());
            $table->string('name');
            $table->string('description');
            $table->string('display_name');
            $table->string('contest_type');
            $table->string('entry_mode');
            $table->timestamps();
        });
        Schema::create('contest_prize_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id');
            $table->smallInteger('rank_from');
            $table->smallInteger('rank_to');
            $table->string('prize');
            $table->string('prize_type');
            $table->timestamps();
        });
        Schema::create('bot_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->string('skill_level');
            $table->timestamps();
        });

        Schema::create('challenge_requests', function (Blueprint $table) {
            $table->id();
            $table->string('challenge_request_id');
            $table->foreignId('user_id');
            $table->string('username');
            $table->decimal('amount', 10, 2);
            $table->foreignId('category_id');
            $table->timestamps();
        });
        Schema::create('trivia_challenge_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('session_token')->index();
            $table->foreignId('challenge_request_id')->constrained('challenge_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('username');
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('category_name');
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('question_label');
            $table->foreignId('option_id')->nullable()->constrained('options')->cascadeOnDelete();
            $table->string('option_label')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
        Schema::create('challenge_boosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_request_id');
            $table->foreignId('boost_id');
            $table->timestamps();
        });
    }
};
