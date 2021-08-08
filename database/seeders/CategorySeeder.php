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
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Music',
                'description' => 'Answer hollyood related questions',
            ]
        );

        DB::table('categories')->insert(
            [
                'name' => 'Generic',
                'description' => 'General game categories',
            ]
        );

    }
}
