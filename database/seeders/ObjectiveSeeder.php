<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObjectiveSeeder extends Seeder
{
    /**a
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('objectives')->insert(
            [
                'id' => 1,
                'reward_type' => 'coins',
                'name' => 'Boost Usage',
                'reward' => 30,
                'milestone_count' => 1,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use a boost in a game.'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 2,
                'reward_type' => 'coins',
                'name' => 'Coins Earned',
                'reward' => 30,
                'milestone_count' => 20,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Earn 20 coins in a game'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 3,
                'reward_type' => 'coins',
                'name' => 'Boost Usage',
                'reward' => 30,
                'milestone_count' => 4,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use 4 boosts in a day'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 4,
                'reward_type' => 'coins',
                'name' => 'Referral',
                'reward' => 30,
                'milestone_count' => 1,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Invite a friend to play Trivia Quest'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 5,
                'reward_type' => 'coins',
                'name' => 'Game Scores',
                'reward' => 30,
                'milestone_count' => 5,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Score at least 5 points in a game.'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 6,
                'reward_type' => 'coins',
                'name' => 'Boost Usage',
                'reward' => 30,
                'milestone_count' => 2,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use 2 boosts'
            ]
        );


        DB::table('objectives')->insert(
            [
                'id' => 8,
                'reward_type' => 'coins',
                'name' => 'Game Scores',
                'reward' => 30,
                'milestone_count' => 10,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Get a perfect score'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 9,
                'reward_type' => 'coins',
                'name' => 'Boost Usage',
                'reward' => 30,
                'milestone_count' => 10,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use 10 boosts in a day.'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 10,
                'reward_type' => 'coins',
                'name' => 'Coins Earned',
                'reward' => 30,
                'milestone_count' => 50,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Earn 50 coins'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 11,
                'reward_type' => 'coins',
                'name' => 'Boost Usage',
                'reward' => 30,
                'milestone_count' => 6,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use 6 boosts in a day'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 12,
                'reward_type' => 'coins',
                'name' => 'Game Scores',
                'reward' => 30,
                'milestone_count' => 8,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Score at least 8 points in a game.'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 13,
                'reward_type' => 'coins',
                'name' => 'Boost Usage',
                'reward' => 30,
                'milestone_count' => 7,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use 7 boosts in a day.'
            ]
        );
    }
}
