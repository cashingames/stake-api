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
    private $categoryName = 'Music';
    private $gameType = 'Multi Choice';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        // if ( env('APP_ENV') == 'local' ) {
        //     Question::factory()
        //     ->hasOptions(4)
        //     ->count(500)
        //     ->create();
        // }

        if (env('APP_ENV') == 'testing' ||  env('APP_ENV') == 'local') {
            Question::factory()
                ->hasOptions(4)
                ->count(50)
                ->create();
        } else {
            $inputFileName = base_path($this->categoryName . '.xlsx');
            $reader = IOFactory::createReader('Xlsx');
            $reader->setReadDataOnly(TRUE);
            $spreadsheet = $reader->load($inputFileName);

            foreach ($spreadsheet->getAllSheets() as $currentSheet) {

                $this->readWorkSheet($currentSheet);
            }
        }
    }

    private function readWorkSheet(Worksheet $workSheet)
    {

        $category = Category::where('name', $this->categoryName)->first();
        $gameType = GameType::where('name', $this->gameType)->first();

        if ($category == null) {
            echo "Category cannot be found \n";
            return;
        }
        if ($gameType == null) {
            echo "Game Type does not exist \n";
            return;
        }

        $highestRow = $workSheet->getHighestRow(); // e.g. 10
        for ($i = 2; $i <= $highestRow; $i++) {

            $level = trim($workSheet->getCellByColumnAndRow(1, $i)->getValue());
            if ($level == '' || ctype_space($level)) {
                echo "Row " . $i . " level is empty \n";
                continue;
            }

            $label = trim($workSheet->getCellByColumnAndRow(2, $i)->getValue());
            if ($label == '' || ctype_space($label)) {
                echo "Row " . $i . " question is empty \n";
                continue;
            }

            $answer = trim($workSheet->getCellByColumnAndRow(7, $i)->getValue());
            if ($answer == '' || ctype_space($answer)) {
                echo "Row " . $i . " answer is empty \n";
                continue;
            }

            $question = new Question;
            $question->level = strtolower($level);
            $question->label = $label;
            $question->game_type_id = $gameType->id;
            $question->category_id = $category->id;

            $options = [];
            $hasCorrectAnswer = false;

            for ($j = 3; $j <= 6; $j++) {
                $optionLabel = trim($workSheet->getCellByColumnAndRow($j, $i)->getValue());

                if (ctype_space($optionLabel) || $optionLabel == '') {
                    // echo "Option on R".$i."C".$j." is empty \n";
                    continue;
                }

                $isCorrect = $optionLabel == $answer;
                if ($isCorrect) {
                    $hasCorrectAnswer = true;
                }

                $option = new  Option();
                $option->title = $optionLabel;
                $option->is_correct = $isCorrect;

                if ($option)

                    array_push($options, $option);
            }

            if ($hasCorrectAnswer) {
                \DB::transaction(function () use ($question, $options) {
                    $question->save();
                    $question->options()->saveMany($options);
                });
            } else {
                echo "R" . $i . " does not have a correct answer \n";
                continue;
            }
        }
    }
}
