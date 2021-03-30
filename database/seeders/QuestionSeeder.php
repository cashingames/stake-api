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
  private $categoryName = 'Football';
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    if ( env('APP_ENV') == 'testing' ) {
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
    if($category==null){
      echo "Category cannot be found \n";
      return;
    }
    
    $highestRow = $workSheet->getHighestRow(); // e.g. 10
    for ($i = 2; $i <= $highestRow; $i++) {

      $level = trim($workSheet->getCellByColumnAndRow(1, $i)->getValue());
      if ($level == '' || ctype_space($level)) {
        echo "Row ".$i." level is empty \n";
        continue;
      }

      $label = trim($workSheet->getCellByColumnAndRow(2, $i)->getValue());
      if ($label == '' || ctype_space($label)) {
        echo "Row ".$i." question is empty \n";
        continue;
      }

      $answer = trim($workSheet->getCellByColumnAndRow(7, $i)->getValue());
      if ($answer == '' || ctype_space($answer)) {
        echo "Row ".$i." answer is empty \n";
        continue;
      }

      $question = new Question;
      $question->level = strtolower($level);
      $question->label = $label;
      $question->category_id = $category->id;

      $options = [];
      $hasCorrectAnswer = false;

      for ($j = 3; $j <= 6; $j++) {
        $optionLabel= trim($workSheet->getCellByColumnAndRow($j, $i)->getValue());
        
        if( ctype_space($optionLabel) || $optionLabel=='' ){
          // echo "Option on R".$i."C".$j." is empty \n";
          continue;
        }

        $isCorrect = $optionLabel == $answer;
        if($isCorrect){
          $hasCorrectAnswer = true;
        }

        $option = new  Option();
        $option->title = $optionLabel;
        $option->is_correct = $isCorrect;

        if($option)

        array_push($options, $option);
      }

      if($hasCorrectAnswer){
        \DB::transaction(function () use ($question, $options) {
          $question->save();
          $question->options()->saveMany($options);
        });
      }else{
        echo "R".$i." does not have a correct answer \n";
        continue;
      }


    }    

  }
}
