<?php

use App\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // factory(Category::class, 5)->create();
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
    }
}
