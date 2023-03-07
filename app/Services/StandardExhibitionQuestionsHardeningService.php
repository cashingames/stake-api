<?php

namespace App\Services;

use App\Models\Category;
use App\Enums\ClientPlatform;
use Illuminate\Support\Collection;

/**
 * Determining Question Hardening odds of a user
 */

class StandardExhibitionQuestionsHardeningService implements QuestionsHardeningServiceInterface
{
    private ClientPlatform $clientPlatform;

    public function __construct(ClientPlatform $clientPlatform)
    {
        $this->clientPlatform = $clientPlatform;
    }

    public function determineQuestions(string $userId, string $categoryId, ?string $triviaId): Collection
    {
        return $this->getEasyQuestions($categoryId);
    }

    private function getEasyQuestions(string $categoryId): Collection
    {
        if($this->clientPlatform == ClientPlatform::GameArkMobile){

            $value = Category::find($categoryId)
                        ->questions()
                        ->easy()
                        ->inRandomOrder()
                        ->take(20)
                        ->get()
                        ->makeVisible('is_correct');
            return $value;
        }else{
            return Category::find($categoryId)
                ->questions()
                ->easy()
                ->inRandomOrder()->take(20)->get();
        }
    }

}
