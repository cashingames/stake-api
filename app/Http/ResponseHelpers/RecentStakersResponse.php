<?php

namespace App\Http\ResponseHelpers;

use App\Enums\FeatureFlags;
use App\Models\GameSession;
use App\Services\FeatureFlag;
use App\Models\ExhibitionStaking;
use App\Traits\Utils\AvatarUtils;

class RecentStakersResponse
{
    use AvatarUtils;

    public $id;
    public $username;
    public $avatar;
    public $correct_count;
    public $points_gained;
    public $amount_won;

    public function transform($gameSession)
    {
        $response = new RecentStakersResponse;
        $response->id = $gameSession->id;
        $response->username = $gameSession->user->username;
        $response->avatar = $this->getAvatarUrl($gameSession->user->profile->avatar ?? '');
        $response->correct_count = $gameSession->correct_count;
        $response->points_gained = $gameSession->points_gained;
        $response->amount_won = $gameSession->exhibitionStaking->staking->amount_won ?? $gameSession->amount_won  ;

        return $response;
    }
}
