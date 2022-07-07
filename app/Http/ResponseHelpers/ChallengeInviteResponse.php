<?php

namespace App\Http\ResponseHelpers;

use App\Enums\ChallengeStatus;
use App\Models\User;
use \Illuminate\Http\JsonResponse;
use App\Traits\Utils\AvatarUtils;


class ChallengeInviteResponse
{

    use AvatarUtils;

    public int $challengeId;
    public int $userId;
    public string $userUsername;
    public $userAvatar;
    public int $opponentId;
    public string $opponentUsername;
    public $opponentAvatar;
    public int $categoryId;
    public $status;


    public function transform($challengeDetails): JsonResponse
    {
       
        $presenter = new ChallengeInviteResponse;

        $presenter->challenegeId = $challengeDetails->id;
        $presenter->userId = $challengeDetails->user_id;
        $presenter->userUsername = $this->getUsername($challengeDetails->user_id);
        $presenter->userAvatar = $this->getAvatar($challengeDetails->user_id);
        $presenter->opponentId = $challengeDetails->opponent_id;
        $presenter->opponentUsername = $this->getUsername($challengeDetails->opponent_id);
        $presenter->opponentAvatar = $this->getAvatar($challengeDetails->opponent_id);
        $presenter->categoryId = $challengeDetails->category_id;
        $presenter->status = $challengeDetails->status;
    

        return response()->json($presenter);
    }

    private function getUsername($id){
        return User::find($id)->username;
    }

    private function getAvatar($id){
        $avatar = User::find($id)->profile->avatar;
        
        return $this->getAvatarUrl($avatar);
    }
}
