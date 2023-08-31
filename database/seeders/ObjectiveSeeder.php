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
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use a bomb boost in a game.'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 2,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Earn 20 coins'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 3,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use a time freeze boost in a game'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 4,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Invite a friend to play Trivia Quest'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 5,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Score at least 5 points in a game.'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 6,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use a skip boost in a game'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 7,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use a bomb boost in a game'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 8,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Get a perfect score'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 9,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use 2 time freeze boosts in a game.'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 10,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Earn 50 coins'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 11,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use 2 skip boosts in a game'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 12,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Score at least 8 points in a game.'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 13,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Use 3 bomb boosts in a game.'
            ]
        );

        DB::table('objectives')->insert(
            [
                'id' => 14,
                'reward_type' => 'coins',
                'reward' => 30,
                'icon' => 'icons/skip_icon.png',
                'description' => 'Get a perfect score in 2 levels.'
            ]
        );
    }
}
