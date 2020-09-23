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

  private $categoryName = 'Music';
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    if ( env('APP_ENV') == 'testing'  ) {
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
      $inputFileName = base_path($this->categoryName.'.xlsx');
      $reader = IOFactory::createReader('Xlsx');
      $reader->setReadDataOnly(TRUE);
      $spreadsheet = $reader->load($inputFileName);
      
      foreach ($spreadsheet->getAllSheets() as $currentSheet  ) {
         
        $this->readWorkSheet($currentSheet);
        
      }
      
    }
  }

  private function readWorkSheet(Worksheet $workSheet){
   
    $category = Category::where('name', $this->categoryName)->first();
      
    for ($i = 2; $i < 100000; $i++) {
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
