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
                'id' => 1,
                'title' => "Good Starter",
                'milestone' => 5,
                'milestone_count' => 1,
                'milestone_type' => "GAMES",
                'reward' => 50,
                'reward_type' => "CASH",
                'description' => "Exhaust all 5 daily free games at a go",
                'medal'=> 'achievements/1.png',
                'quality_image'=> 'quality_achievement_image/1.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ],

        );
        DB::table('achievement_badges')->insert(
            [
                'id' => 2,
                'title' => "Scholar",
                'milestone' => 50,
                'milestone_count' => 1,
                'milestone_type' => "GAMES",
                'reward' => 60,
                'reward_type' => "POINTS",
                'description' => "Play 50 games in general knowledge",
                'medal'=> 'achievements/2.png',
                'quality_image'=> 'quality_achievement_image/2.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 3,
                'title' => "Music pro",
                'milestone' => 50,
                'milestone_count' => 1,
                'milestone_type' => "GAMES",
                'reward' => 60,
                'reward_type' => "POINTS",
                'description' => "play 50 games in music",
                'medal'=> 'achievements/3.png',
                'quality_image'=> 'quality_achievement_image/3.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 4,
                'title' => "football geek",
                'milestone' => 50,
                'milestone_count' => 1,
                'milestone_type' => "GAMES",
                'reward' => 60,
                'reward_type' => "POINTS",
                'description' => "play 50 games in football",
                'medal'=> 'achievements/4.png',
                'quality_image'=> 'quality_achievement_image/4.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 5,
                'title' => "Nerd",
                'milestone' => 7,
                'milestone_count' => 10,
                'milestone_type' => "SCOREGAMES",
                'reward' => 25,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 10 consecutive games in general knowledge",
                'medal'=> 'achievements/5.png',
                'quality_image'=> 'quality_achievement_image/5.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 6,
                'title' => "Muse",
                'milestone' => 7,
                'milestone_count' => 10,
                'milestone_type' => "SCOREGAMES",
                'reward' => 25,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 10 consecutive games in music",
                'medal'=> 'achievements/6.png',
                'quality_image'=> 'quality_achievement_image/6.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 7,
                'title' => "Big Fan",
                'milestone' => 7,
                'milestone_count' => 10,
                'milestone_type' => "SCOREGAMES",
                'reward' => 25,
                'reward_type' => "POINTS",
                'description' => "score above 7 in 10 games in football",
                'medal'=> 'achievements/7.png',
                'quality_image'=> 'quality_achievement_image/7.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 8,
                'title' => "Geek",
                'milestone' => 7,
                'milestone_count' => 50,
                'milestone_type' => "SCOREGAMES",
                'reward' => 50,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 50 games in general knowledge",
                'medal'=> 'achievements/8.png',
                'quality_image'=> 'quality_achievement_image/8.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 9,
                'title' => "maestro",
                'milestone' => 7,
                'milestone_count' => 50,
                'milestone_type' => "SCOREGAMES",
                'reward' => 50,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 50 games in music",
                'medal'=> 'achievements/9.png',
                'quality_image'=> 'quality_achievement_image/9.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 10,
                'title' => "pro fan",
                'milestone' => 7,
                'milestone_count' => 50,
                'milestone_type' => "SCOREGAMES",
                'reward' => 100,
                'reward_type' => "POINTS",
                'description' => "Score above 7 in 50 games in football",
                'medal'=> 'achievements/10.png',
                'quality_image'=> 'quality_achievement_image/10.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 11,
                'title' => "professor",
                'milestone' => 100,
                'milestone_count' => 1,
                'milestone_type' => "SCOREGAMES",
                'reward' => 100,
                'reward_type' => "POINTS",
                'description' => "Play 100 games in general knowledge",
                'medal'=> 'achievements/11.png',
                'quality_image'=> 'quality_achievement_image/11.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 12,
                'title' => "music geek",
                'milestone' => 100,
                'milestone_count' => 1,
                'milestone_type' => "SCOREGAMES",
                'reward' => 100,
                'reward_type' => "POINTS",
                'description' => "Play 100 games in music",
                'medal'=> 'achievements/12.png',
                'quality_image'=> 'quality_achievement_image/12.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 13,
                'title' => "football champ",
                'milestone' => 100,
                'milestone_count' => 1,
                'milestone_type' => "SCOREGAMES",
                'reward' => 100,
                'reward_type' => "POINTS",
                'description' => "Play 100 games in football",
                'medal'=> 'achievements/13.png',
                'quality_image'=> 'quality_achievement_image/13.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 14,
                'title' => "sage",
                'milestone' => 7,
                'milestone_count' => 100,
                'milestone_type' => "SCOREGAMES",
                'reward' => 200,
                'reward_type' => "CASH",
                'description' => "Score above 7 in 100 games in general knowledge",
                'medal'=> 'achievements/14.png',
                'quality_image'=> 'quality_achievement_image/14.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 15,
                'title' => "music enthusiast",
                'milestone' => 7,
                'milestone_count' => 100,
                'milestone_type' => "SCOREGAMES",
                'reward' => 500,
                'reward_type' => "CASH",
                'description' => "Score above 7 in 100 games in music",
                'medal'=> 'achievements/15.png',
                'quality_image'=> 'quality_achievement_image/15.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 16,
                'title' => "ultimate fan",
                'milestone' => 7,
                'milestone_count' => 100,
                'milestone_type' => "SCOREGAMES",
                'reward' => 500,
                'reward_type' => "CASH",
                'description' => "Score above 7 in 100 games in football",
                'medal'=> 'achievements/16.png',
                'quality_image'=> 'quality_achievement_image/16.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 17,
                'title' => "connector",
                'milestone' => 10,
                'milestone_count' => 1,
                'milestone_type' => "CHALLENGE_STARTED",
                'reward' => 50,
                'reward_type' => "POINTS",
                'description' => "start 10 challenge games",
                'medal'=> 'achievements/17.png',
                'quality_image'=> 'quality_achievement_image/17.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 18,
                'title' => "engager",
                'milestone' => 10,
                'milestone_count' => 1,
                'milestone_type' => "CHALLENGE_ACCEPTED",
                'reward' => 50,
                'reward_type' => "POINTS",
                'description' => "accepted 10 challenges",
                'medal'=> 'achievements/18.png',
                'quality_image'=> 'quality_achievement_image/18.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 19,
                'title' => "pro connector",
                'milestone' => 20,
                'milestone_count' => 1,
                'milestone_type' => "CHALLENGE_STARTED",
                'reward' => 60,
                'reward_type' => "POINTS",
                'description' => "start 20 challenge games",
                'medal'=> 'achievements/19.png',
                'quality_image'=> 'quality_achievement_image/19.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 20,
                'title' => "good gamer",
                'milestone' => 10,
                'milestone_count' => 1,
                'milestone_type' => "GAME_BOUGHT",
                'reward' => 300,
                'reward_type' => "CASH",
                'description' => "buy 10 least game plan",
                'medal'=> 'achievements/20.png',
                'quality_image'=> 'quality_achievement_image/20.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 21,
                'title' => "pro gamer",
                'milestone' => 10,
                'milestone_count' => 1,
                'milestone_type' => "GAME_BOUGHT_DOUBLE",
                'reward' => 500,
                'reward_type' => "CASH",
                'description' => "buy 10 double o game  plan",
                'medal'=> 'achievements/21.png',
                'quality_image'=> 'quality_achievement_image/21.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 22,
                'title' => "Money spender",
                'milestone' => 10,
                'milestone_count' => 1,
                'milestone_type' => "GAME_BOUGHT_ULTIMATE",
                'reward' => 1000,
                'reward_type' => "CASH",
                'description' => "Buy 10 ultimate game plan",
                'medal'=> 'achievements/22.png',
                'quality_image'=> 'quality_achievement_image/22.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 23,
                'title' => "smart gamer",
                'milestone' => 5,
                'milestone_count' => 1,
                'milestone_type' => "SKIP_BOUGHT",
                'reward' => 250,
                'reward_type' => "CASH",
                'description' => "buy 5 skip",
                'medal'=> 'achievements/23.png',
                'quality_image'=> 'quality_achievement_image/23.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 24,
                'title' => "wise gamer",
                'milestone' => 5,
                'milestone_count' => 1,
                'milestone_type' => "TIME_FREEZE_BOUGHT",
                'reward' => 250,
                'reward_type' => "CASH",
                'description' => "buy 5 time freeze",
                'medal'=> 'achievements/24.png',
                'quality_image'=> 'quality_achievement_image/24.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );

        DB::table('achievement_badges')->insert(
            [
                'id' => 25,
                'title' => "referral king",
                'milestone' => 5,
                'milestone_count' => 1,
                'milestone_type' => "REFERRAL",
                'reward' => 300,
                'reward_type' => "CASH",
                'description' => "invite 30 friends",
                'medal'=> 'achievements/25.png',
                'quality_image'=> 'quality_achievement_image/25.png',
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now()
            ]
        );
    }
}
