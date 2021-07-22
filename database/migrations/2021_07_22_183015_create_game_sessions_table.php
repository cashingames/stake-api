<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGameSessionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mode_id')->constrained()->onDelete('cascade');
            $table->foreignId('game_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('challenge_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('opponent_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('session_token')->unique();
            $table->enum('state', ['PENDING', 'ONGOING', 'PAUSED', 'COMPLETED']);
            $table->tinyInteger('user_correct_count')->nullable()->default(0);
            $table->tinyInteger('opponent_correct_count')->nullable()->default(0);
            $table->tinyInteger('user_wrong_count')->nullable()->default(0);
            $table->tinyInteger('opponent_wrong_count')->nullable()->default(0);
            $table->tinyInteger('total_user_count')->nullable()->default(0);
            $table->tinyInteger('total_opponent_count')->nullable()->default(0);
            $table->tinyInteger('user_points_gained')->nullable()->default(0)->index();
            $table->tinyInteger('opponent_points_gained')->nullable()->default(0)->index();
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
        Schema::dropIfExists('game_sessions');
    }
}
