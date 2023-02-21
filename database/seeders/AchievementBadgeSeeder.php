<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AchievementBadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // GAMES||POINTS||CASH||SCOREGAMES||CHALLENGE_STARTED||CHALLENGE_ACCEPTED||GAME_BOUGHT||GAME_BOUGHT_DOUBLE||GAME_BOUGHT_ULTIMATE||SKIP_BOUGHT||TIME_FREEZE_BOUGHT||REFERRAL
        // REWARD: GAMES||POINTS||CASH
        DB::table('achievement_badges')->insert(
            [
                'title' => "Good Starter",
                'milestone' => 5,
                'milestone_count' => 1,
                'milestone_type' => "GAMES",
                'reward' => 50,
                'reward_type' => "CASH",
                'description' => "Exhaust all 5 daily free games at a go",
                'medal'=>'achievements/1.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ],

        );
        DB::table('achievement_badges')->insert(
            [
                'title' => "Scholar",
                'milestone' => 50,
                'milestone_count' => 1,
                'milestone_type' => "GAMES",
                'reward' => 60,
                'reward_type' => "POINTS",
                'description' => "Play 50 games in general knowledge",
                'medal'=> 'achievements/2.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "Music pro",
                'milestone' => 50,
                'milestone_count' => 1,
                'milestone_type' => "GAMES",
                'reward' => 60,
                'reward_type' => "POINTS",
                'description' => "play 50 games in music",
                'medal'=> 'achievements/3.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "football geek",
                'milestone' => 50,
                'milestone_count' => 1,
                'milestone_type' => "GAMES",
                'reward' => 60,
                'reward_type' => "POINTS",
                'description' => "play 50 games in football",
                'medal'=> 'achievements/4.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "Nerd",
                'milestone' => 7,
                'milestone_count' => 10,
                'milestone_type' => "SCOREGAMES",
                'reward' => 25,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 10 consecutive games in music",
                'medal'=> 'achievements/5.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "Big Fan",
                'milestone' => 7,
                'milestone_count' => 10,
                'milestone_type' => "SCOREGAMES",
                'reward' => 25,
                'reward_type' => "POINTS",
                'description' => "score above 7 in 10 games in football",
                'medal'=> 'achievements/7.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "Geek",
                'milestone' => 7,
                'milestone_count' => 50,
                'milestone_type' => "SCOREGAMES",
                'reward' => 50,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 50 games in general knowledge",
                'medal'=> 'achievements/8.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "maestro",
                'milestone' => 7,
                'milestone_count' => 50,
                'milestone_type' => "SCOREGAMES",
                'reward' => 50,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 50 games in music",
                'medal'=> 'achievements/9.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "pro fan",
                'milestone' => 7,
                'milestone_count' => 50,
                'milestone_type' => "SCOREGAMES",
                'reward' => 100,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 50 games in football",
                'medal'=> 'achievements/10.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "professor",
                'milestone' => 100,
                'milestone_count' => 1,
                'milestone_type' => "SCOREGAMES",
                'reward' => 100,
                'reward_type' => "POINTS",
                'description' => "Play 100 games in general knowledge",
                'medal'=> 'achievements/11.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "music geek",
                'milestone' => 100,
                'milestone_count' => 1,
                'milestone_type' => "SCOREGAMES",
                'reward' => 100,
                'reward_type' => "POINTS",
                'description' => "Play 100 games in music",
                'medal'=> 'achievements/12.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "football champ",
                'milestone' => 100,
                'milestone_count' => 1,
                'milestone_type' => "SCOREGAMES",
                'reward' => 100,
                'reward_type' => "POINTS",
                'description' => "Play 100 games in football",
                'medal'=> 'achievements/13.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "sage",
                'milestone' => 7,
                'milestone_count' => 100,
                'milestone_type' => "SCOREGAMES",
                'reward' => 200,
                'reward_type' => "CASH",
                'description' => "Score above 7 in 100 games in general knowledge",
                'medal'=> 'achievements/14.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "music enthusiast",
                'milestone' => 7,
                'milestone_count' => 100,
                'milestone_type' => "SCOREGAMES",
                'reward' => 500,
                'reward_type' => "CASH",
                'description' => "Score above 7 in 100 games in music",
                'medal'=> 'achievements/15.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "ultimate fan",
                'milestone' => 7,
                'milestone_count' => 100,
                'milestone_type' => "SCOREGAMES",
                'reward' => 500,
                'reward_type' => "CASH",
                'description' => "Score above 7 in 100 games in football",
                'medal'=> 'achievements/16.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "connector",
                'milestone' => 10,
                'milestone_count' => 1,
                'milestone_type' => "CHALLENGE_STARTED",
                'reward' => 50,
                'reward_type' => "POINTS",
                'description' => "start 10 challenge games",
                'medal'=> 'achievements/17.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "engager",
                'milestone' => 10,
                'milestone_count' => 1,
                'milestone_type' => "CHALLENGE_ACCEPTED",
                'reward' => 50,
                'reward_type' => "POINTS",
                'description' => "accepted 10 challenges",
                'medal'=> 'achievements/18.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "pro connector",
                'milestone' => 20,
                'milestone_count' => 1,
                'milestone_type' => "CHALLENGE_STARTED",
                'reward' => 60,
                'reward_type' => "POINTS",
                'description' => "accepted 20 challenges",
                'medal'=> 'achievements/19.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "good gamer",
                'milestone' => 10,
                'milestone_count' => 1,
                'milestone_type' => "GAME_BOUGHT",
                'reward' => 300,
                'reward_type' => "CASH",
                'description' => "buy 10 least game plan",
                'medal'=> 'achievements/20.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "pro gamer",
                'milestone' => 10,
                'milestone_count' => 1,
                'milestone_type' => "GAME_BOUGHT_DOUBLE",
                'reward' => 500,
                'reward_type' => "CASH",
                'description' => "buy 10 least game plan",
                'medal'=> 'achievements/21.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "more spender",
                'milestone' => 10,
                'milestone_count' => 1,
                'milestone_type' => "GAME_BOUGHT_ULTIMATE",
                'reward' => 1000,
                'reward_type' => "CASH",
                'description' => "buy 10 least game plan",
                'medal'=> 'achievements/22.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "smart gamer",
                'milestone' => 5,
                'milestone_count' => 1,
                'milestone_type' => "SKIP_BOUGHT",
                'reward' => 250,
                'reward_type' => "CASH",
                'description' => "buy 5 skip",
                'medal'=> 'achievements/23.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "wise gamer",
                'milestone' => 5,
                'milestone_count' => 1,
                'milestone_type' => "24",
                'reward' => 250,
                'reward_type' => "CASH",
                'description' => "buy 5 time freeze",
                'medal'=> 'achievements/conqueror_medal.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'title' => "referral king",
                'milestone' => 5,
                'milestone_count' => 1,
                'milestone_type' => "REFERRAL",
                'reward' => 300,
                'reward_type' => "CASH",
                'description' => "invite 30 friends",
                'medal'=> 'achievements/25.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );
    }
}
