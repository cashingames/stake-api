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
        DB::table('categories')->insert(
            [
                'name' => 'Movies',
                'description' => 'Answer movie related questions',
                'instruction' => 'Movies',
            ]
        );
        DB::table('categories')->insert(
            [
                'name' => 'Nollywood',
                'description' => 'Nigerian movie industry',
                'instruction' => 'Answer nigerian movie questions',
                'category_id' => 1,
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Hollywood',
                'description' => 'Answer hollyood related questions',
                'instruction' => 'For hollywood guys',
                'category_id' => 1,
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Sports',
                'description' => 'Sport Questions',
                'instruction' => 'All ranges of sports',
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Football',
                'description' => 'Football questions',
                'instruction' => 'Football questions',
                'category_id' => 4,
            ]
        );


        DB::table('categories')->insert(
            [
                'name' => 'Entertainment',
                'description' => 'Answer hollyood related questions',
                'instruction' => 'For hollywood guys',
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Music',
                'description' => 'Answer hollyood related questions',
                'instruction' => 'For hollywood guys',
                'category_id' => 6,
            ]
        );

    }
}
