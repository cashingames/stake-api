<?php

use App\Option;
use App\Question;
use Illuminate\Database\Seeder;

class QuestionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Question::class, 100)->create()->each(function ($question) {
            $options = factory(Option::class,4)->make();
            $option = $options->random();
            $optionIndex = $options->search($option);

            $option->is_correct = true;
            $options = $options->replace([$optionIndex => $option]);

            $question->options()->createMany( $options->toArray() );
          });
    }
}
