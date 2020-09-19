<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Option;
use App\Models\Question;
use Hamcrest\Core\IsNull;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class QuestionSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    if ( env('APP_ENV') == 'testing' || env('APP_ENV') == 'local' ) {
      Question::factory()->times(50)->create()->each(function ($question) {
        $options = Option::factory()->times(4)->make();
        $option = $options->random();
        $optionIndex = $options->search($option);

        $option->is_correct = true;
        $options = $options->replace([$optionIndex => $option]);

        $question
          ->options()
          ->createMany( $options
            ->makeVisible('is_correct')
            ->makeVisible('created_at')
            ->makeVisible('updated_at')
            ->toArray()
        );
      });
    } else {
      $inputFileName = base_path('Music.xlsx');
      $reader = IOFactory::createReader('Xlsx');
      $reader->setReadDataOnly(TRUE);
      $spreadsheet = $reader->load($inputFileName);

      $workSheet  = $spreadsheet->getActiveSheet();

      $this->readWorkSheet($workSheet);
    }
  }

  private function readWorkSheet(Worksheet $workSheet)
  {
    $categoryName = $workSheet->getTitle();
    $category = Category::where('name', $categoryName)->first();

    if ($category == null) {
      return;
    }

    for ($i = 2; $i < 500; $i++) {
      $question = new Question;

      $level = $workSheet->getCellByColumnAndRow(1, $i)->getValue();
      if (!isset($level) || trim($level) === '') {
        continue;
      }

      $question->level = $level;
      $question->label = $workSheet->getCellByColumnAndRow(2, $i)->getValue();
      $question->category_id = $category->id;
      $question->save();

      for ($j = 3; $j <= 6; $j++) {
        $option = new  Option();
        $option->title = $workSheet->getCellByColumnAndRow($j, $i)->getValue();
        $option->question_id = $question->id;
        $option->is_correct = $option->title == $workSheet->getCellByColumnAndRow(7, $i)->getValue();
        $option->save();
      }

    }

  }
}
