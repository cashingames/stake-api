<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSelectedColumnsFromTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        //Category Table
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('primary_color', 'font_color');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('icon_name', 'icon');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('game_background_url', 'background_color');
        });

        //Game Session Questions Table
        Schema::table('game_session_questions', function (Blueprint $table) {
            $table->foreignId('question_id')->nullable();
            $table->foreignId('game_session_id')->nullable();
            $table->foreignId('option_id')->nullable();
        });

        //Game Mode Table
        Schema::rename('modes', 'game_modes');

        //Game Sessions Table
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->renameColumn('mode_id', 'game_mode_id');
        });
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->dropColumn(['user_won','opponent_id', 'opponent_correct_count', 
            'opponent_wrong_count','opponent_won','total_opponent_count','opponent_points_gained']);
        });
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->renameColumn('user_correct_count', 'correct_count');
        });
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->renameColumn('user_wrong_count', 'wrong_count');
        });
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->renameColumn('total_user_count', 'total_count');
        });
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->renameColumn('user_points_gained', 'points_gained');
        });

        //Game Types
        Schema::table('game_types', function (Blueprint $table) {
            $table->renameColumn('primary_color_1', 'background_color_1');
        });
        Schema::table('game_types', function (Blueprint $table) {
            $table->renameColumn('primary_color_2', 'background_color_2');
        });

        //Online Timelines
        Schema::dropIfExists('online_timelines');

        //Category Rankings
        Schema::dropIfExists('category_rankings');

        //User Points
        Schema::table('user_points', function (Blueprint $table) {
            $table->dropColumn('game_type_id');
        });
        
        //Users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['points','user_index_status']);
        });

        //Withdrawals
        Schema::dropIfExists('withdrawals');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        //Category Table
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('font_color', 'primary_color');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('icon', 'icon_name');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('background_color', 'game_background_url');
        });

        //Game Session Questions Table
        Schema::table('game_session_questions', function (Blueprint $table) {
            $table->dropColumn('question_id');
        });
        Schema::table('game_session_questions', function (Blueprint $table) {
            $table->dropColumn('game_session_id');
        });
        Schema::table('game_session_questions', function (Blueprint $table) {
            $table->dropColumn('option_id');
        });

        //Game Mode Table
        Schema::rename('game_modes', 'modes');

        //Game Sessions Table
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->renameColumn('game_mode_id','mode_id');
        });
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->renameColumn('correct_count', 'user_correct_count');
        });
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->renameColumn('wrong_count', 'user_wrong_count');
        });
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->renameColumn('total_count', 'total_user_count');
        });
        Schema::table('game_sessions', function (Blueprint $table) {
            $table->renameColumn('points_gained', 'user_points_gained');
        });        
        
        //Game Types
        Schema::table('game_types', function (Blueprint $table) {
            $table->renameColumn('background_color_1', 'primary_color_1');
        });
        Schema::table('game_types', function (Blueprint $table) {
            $table->renameColumn('background_color_2', 'primary_color_2');
        });

        //User Points
        Schema::table('user_points', function (Blueprint $table) {
            $table->foreignId('game_type_id')->nullable();
        });

        //Users
        Schema::table('user_points', function (Blueprint $table) {
            $table->bigInteger('points')->nullable();
            $table->enum('user_index_status',["CLIMBED", "DROPPED"])->nullable();
        });
    }
}
