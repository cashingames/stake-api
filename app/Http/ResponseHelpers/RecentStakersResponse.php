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
    public $correctCount;
    public $amountWon;
    public $amountStaked;

    public function transform($resultset): self
    {
        $this->id = $resultset->id;
        $this->username = $resultset->username;
        $this->avatar = $resultset->avatar ? $this->getAvatarUrl($resultset->avatar) : '';
        $this->correctCount = $resultset->correct_count;
        $this->amountWon = $resultset->amount_won;
        $this->amountStaked = $resultset->amount_staked;

        return $this;
    }
}
