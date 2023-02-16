<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use App\Models\Category;
use App\Models\GameType;
use App\Models\Option;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionSeeder extends Seeder
{
    private $categoryName = 'La Liga';
    private $gameType = 'MULTIPLE_CHOICE';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // if (env('APP_ENV') == 'testing' ||  env('APP_ENV') == 'local') {
            Question::factory()
                ->hasOptions(4)
                ->count(150)
            ->create();
    }

}
