<?php

namespace Tests\Helpers;


use App\Models\User;
use App\Models\Wallet;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChallengeUtils extends TestCase
{
    public function seedOngoingMatch($url)
    {

        $category = Category::factory()->create();
        $questions = Question::factory()
            ->hasOptions(4)
            ->count(250)
            ->create();

        $data = [];

        foreach ($questions as $question) {
            $data[] = [
                'question_id' => $question->id,
                'category_id' => $category->id
            ];
        }

        DB::table('categories_questions')->insert($data);

        $this->prepareMatchRequest($url, $category, 500);
        $this->prepareMatchRequest($url, $category, 500);

    }

    public function prepareMatchRequest(string $url, $category, $amount): void
    {
        $user = User::factory()->create();
        Wallet::factory()
            ->for($user)
            ->create([
                'non_withdrawable' => 1000
            ]);

        $this->actingAs($user)
            ->post($url, [
                'category' => $category->id,
                'amount' => $amount
            ]);
    }
}