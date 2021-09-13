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
        
        DB::table('categories')->insert(
            [
                'name' => 'Movies',
                'description' => 'Answer movie related questions',
            ]
        );
        DB::table('categories')->insert(
            [
                'name' => 'Nollywood',
                'description' => 'Nigerian movie industry',
                'category_id' => 1,
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Hollywood',
                'description' => 'Answer hollyood related questions',
                'category_id' => 1,
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Sports',
                'description' => 'Sport Questions',
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Football',
                'description' => 'Football questions',
                'category_id' => 4,
                'icon_name'=> 'icons/soccer_ball.png',
                'primary_color'=> '#EF8318'
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Music',
                'description' => 'Answer Music questions',
                'icon_name'=> 'icons/music_note.png',
                'primary_color' => '#9C3DB8'
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Generic',
                'description' => 'General game categories',
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Premier League Clubs',
                'description' => 'Answer premier league related questions',
                'category_id' => 5,
                'icon_name'=> 'icons/premier_league.png',
                'primary_color'=> '#EF8318'
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'La Liga Clubs',
                'description' => 'Answer La liga questions',
                'category_id' => 5,
                'icon_name'=> 'icons/la_liga.jpg',
                'primary_color'=> '#EF8318'
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Naija Music',
                'description' => 'Answer Naija music questions',
                'category_id' => 6,
                'icon_name'=> 'icons/naija_music.jpg',
                'primary_color' => '#9C3DB8'
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'The Rest of The World',
                'description' => 'Answer world wide music questions',
                'category_id' => 6,
                'icon_name'=> 'icons/world_music.jpg',
                'primary_color' => '#9C3DB8'
            ]
        );

    }
}
