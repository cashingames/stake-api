<?php
use App\Models\Category;
use App\Models\Question;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Challenge extends TestCase
{
    public function seedOngoingMatch($API_URL)
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

        $this->prepareMatchRequest($API_URL, $category, 500);
        $this->prepareMatchRequest($API_URL, $category, 500);

    }

    public function prepareMatchRequest(string $API_URL, $category, $amount): void
    {
        $user = User::factory()->create();
        Wallet::factory()
            ->for($user)
            ->create([
                'non_withdrawable' => 1000
            ]);

        $this->actingAs($user)
            ->post($API_URL, [
                'category' => $category->id,
                'amount' => $amount
            ]);
    }
}
