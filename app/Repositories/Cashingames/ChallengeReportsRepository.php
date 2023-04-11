<?php

namespace App\Repositories\Cashingames;

use App\Models\ChallengeRequest;

class ChallengeReportsRepository
{

    public function getTotalChallengeSessions($startDate, $endDate)
    {
        return ChallengeRequest::where('status', '=', 'COMPLETED')
            ->whereBetween('created_at', [$startDate->toDateString(), $endDate->toDateString()])
            ->count();
    }

    public function getTotalNmberOfUsersThatPlayed($startDate, $endDate)
    {
        return ChallengeRequest::whereBetween('created_at',  [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', '=', 'COMPLETED')
            ->where('user_id', '!=', 1)
            ->distinct('user_id')->count();
    }

    public function getTotalNmberOfUsersThatWon($startDate, $endDate)
    {
        return ChallengeRequest::whereBetween('created_at',  [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', '=', 'COMPLETED')
            ->where('user_id', '!=', 1)
            ->where('amount_won', '>', 0)
            ->distinct('user_id')->count();
    }

    public function getTotalNmberOfUsersThatLost($startDate, $endDate)
    {
        return ChallengeRequest::whereBetween('created_at',  [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', '=', 'COMPLETED')
            ->where('user_id', '!=', 1)
            ->where('amount_won', '=', 0)
            ->distinct('user_id')->count();
    }
    public function getTotalAmountWonByBot($startDate, $endDate)
    {
        return ChallengeRequest::whereBetween('created_at',  [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', '=', 'COMPLETED')
            ->where('user_id', '=', 1)
            ->where('amount_won', '>', 0)->sum('amount_won');
    }
    public function getTotalAmountWonByUsers($startDate, $endDate)
    {
        return ChallengeRequest::whereBetween('created_at',  [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', '=', 'COMPLETED')
            ->where('user_id', '>', 1)
            ->where('amount_won', '>', 0)->sum('amount_won');
    }

    public function getTotalNmberOfDraws($startDate, $endDate)
    {
        return ChallengeRequest::whereBetween('created_at',  [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', '=', 'COMPLETED')
            ->where('amount_won', '=', 0)
            ->distinct('session_token')->count();
    }
}
