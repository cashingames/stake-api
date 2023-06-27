<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('games')->insert(
            [
                'id' => 1,
                'name' => 'Trivia Hub',
                'background_image' => 'games/trivia_hub_bg.png',
                'icon' => 'games/trivia_hub_icon.png',
                'is_enabled' => true
            ]
        );

        DB::table('games')->insert(
            [
                'id' => 2,
                'name' => 'Bubble Blitz',
                'background_image' => 'games/bubble_blitz_bg.png',
                'icon' => 'games/bubble_blitz_icon.png',
                'is_enabled' => true
            ]
        );

        DB::table('games')->insert(
            [
                'id' => 3,
                'name' => 'Picture Trivia',
                'background_image' => 'games/picture_trivia_bg.png',
                'icon' => 'games/picture_trivia_icon.png',
                'is_enabled' => false
            ]
        );

        DB::table('games')->insert(
            [
                'id' => 4,
                'name' => 'Word Swap',
                'background_image' => 'games/word_swap_bg.png',
                'icon' => 'games/word_swap_icon.png',
                'is_enabled' => false
            ]
        );

        DB::table('games')->insert(
            [
                'id' => 5,
                'name' => 'Picture Jumbo',
                'background_image' => 'games/picture_jumbo_bg.png',
                'icon' => 'games/picture_jumbo_icon.png',
                'is_enabled' => false
            ]
        );
    }
}
