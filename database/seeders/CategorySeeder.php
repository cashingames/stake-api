<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //modifications will be done on this after review of categories and game flow is done

        // DB::table('categories')->insert(
        //     [
        //         'name' => 'Movies',
        //         'category_id' => 0,
        //         'icon_name' => 'icons/world_music.jpg',
        //         'description' => 'Answer movie related questions',
        //         'primary_color' => '#EF8318'
        //     ]
        // );

        // DB::table('categories')->insert(
        //     [
        //         'name' => 'Nollywood',
        //         'description' => 'Nigerian movie industry',
        //         'category_id' => 1,
        //         'icon_name' => 'icons/world_music.jpg',
        //         'primary_color' => '#EF8318'
        //     ]
        // );

        // DB::table('categories')->insert(
        //     [
        //         'name' => 'Hollywood',
        //         'description' => 'Answer hollyood related questions',
        //         'category_id' => 1,
        //         'icon_name' => 'icons/world_music.jpg',
        //         'primary_color' => '#EF8318'
        //     ]
        // );


        DB::table('categories')->insert(
            [
                'id' => 101,
                'category_id' => 0,
                'name' => 'Football',
                'icon_name' => 'icons/soccer_ball.png',
                'description' => 'Football Questions',
                'primary_color' => '#9C3DB8'
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Premier League Clubs',
                'description' => 'Answer premier league related questions',
                'category_id' => 101,
                'icon_name' => 'icons/premier_league.png',
                'primary_color' => '#EF8318'
            ]
        );

        DB::table('categories')->insert(
            [
                'id' => 501,
                'category_id' => 0,
                'name' => 'Music',
                'description' => 'Answer Music questions',
                'icon_name' => 'icons/music_note.png',
                'primary_color' => '#9C3DB8'
            ],
        );

        DB::table('categories')->insert(
            [
                'name' => 'Naija Music',
                'description' => 'Answer Naija music questions',
                'category_id' => 501,
                'icon_name' => 'icons/naija_music.jpg',
                'primary_color' => '#EF8318'
            ],
        );

        DB::table('categories')->insert(
            [
                'name' => 'The Rest of The World',
                'description' => 'Answer world wide music questions',
                'category_id' => 501,
                'icon_name' => 'icons/world_music.jpg',
                'primary_color' => '#EF8318'
            ]
        );
    }
}
