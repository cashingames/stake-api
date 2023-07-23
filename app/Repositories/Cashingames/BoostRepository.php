<?php

namespace App\Repositories\Cashingames;

use App\Models\Boost;

class BoostRepository
{

    public function getBoostById(int $boostId): mixed
    {
        return Boost::findOrFail($boostId);
    }

    public function addUserBoost(int $boostId, int $userId): mixed
    {
        $boost = $this->getBoostById($boostId);
        $userBoost = $boost->users()->where('user_id', $userId)->first();

        if ($userBoost) {
            $userBoost->pivot->boost_count += $boost->pack_count;
            $userBoost->pivot->save();
        } else {
            $boost->users()->attach($userId, [
                'boost_count' => $boost->pack_count,
                'used_count' => 0
            ]);
        }

        return $boost;
    }

}