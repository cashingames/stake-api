<?php

namespace App\Services;

use App\Http\ResponseHelpers\DailyObjectiveResponse;
use App\Models\DailyObjective;
use App\Models\GameSession;
use App\Models\Objective;
use App\Models\Profile;
use App\Models\User;
use App\Models\UserBoost;
use App\Models\UserCoinTransaction;
use App\Models\UserDailyObjective;
use Illuminate\Support\Facades\DB;

class DailyObjectiveService
{
    public function dailyObjective(User $user)
    {
        $userDailyObjectiveTotal = $this->getUserDailyObjectivesTotal($user);
        $todayDailyObjectives = $this->getTodayDailyObjectives();
        $objectiveReward = null;
        foreach ($todayDailyObjectives as $todayObjective) {
            if ($userDailyObjectiveTotal == 0) {
                $this->createUserDailyObjectiveRecord($user, $todayObjective);
            }
            //check which daily objective it is
            $objective = $todayObjective->objective;
            $objectiveReward = $objective->reward;

            if($objective->name == 'Boost Usage'){
                $this->checkBoostUsage($objective, $todayObjective);
            }

            if($objective->name == 'Game Scores'){
                $this->checkUserScore($objective->milestone_count, $todayObjective);
            }

            if($objective->name == 'Referral'){
                $this->checkReferralObjective($todayObjective);
            }
            
            if($objective->name == 'Coins Earned'){
                $this->checkCoinEarned($objective->milestone_count, $todayObjective);
            }
           
        }
        //if userDailyobj for today is achieved
        $dailyObjectivesAchieved = $this->getUserDailyObjectives($user)->where('is_achieved', 1)->count();
        if($dailyObjectivesAchieved >= 2){
            $this->awardCoins($user, $objectiveReward);
            return $this->showTodayObjectives(false);
        }
        return $this->showTodayObjectives(true);
    }

    private function todayDailyObjective()
    {
        $dailyObjectives = DailyObjective::whereDate('created_at', '>=', now()->startOfDay())->with('objective')->get();
        $response = new DailyObjectiveResponse();
        $data = [];
        foreach ($dailyObjectives as $item) {
            $data[] = 
            $response->transform($item);
        }
        return $data;
    }

    private function showTodayObjectives(bool $shouldShowDailyObjective){
        $data = $this->todayDailyObjective();
        return response()->json([
            "shouldShowDailyObjective" => $shouldShowDailyObjective,
            'daily objectives' => $data
        ], 200);
    }

    private function createUserDailyObjectiveRecord($user, $todayObjective)
    {
        UserDailyObjective::create([
            'user_id' => $user->id,
            'daily_objective_id' => $todayObjective->id,
            'count' => 0,
            'is_achieved' => 0,
            'created_at' => now(),
        ]);
    }

    private function getUserDailyObjectivesTotal($user)
    {
        $userDailyObjectiveCount = UserDailyObjective::where('user_id', $user->id)->whereDate('created_at', now()->startOfDay())->count();
        return $userDailyObjectiveCount;
    }

    private function getTodayDailyObjectives()
    {
        $todayDailyObjectives = DailyObjective::whereDate('created_at', now()->startOfDay())->get();
        return $todayDailyObjectives;
    }

    private function getUserDailyObjectives($user)
    {
        $userDailyObjectives = UserDailyObjective::where('user_id', $user->id)->whereDate('created_at', now()->startOfDay())->get();
        return $userDailyObjectives;
    }

    private function completeDailyObjective($dailyObjective)
    {
        $objective = UserDailyObjective::where('user_id', auth()->user()->id)->where('daily_objective_id', $dailyObjective->id)->first();
        $objective->is_achieved = true;
        $objective->save();
    }

    private function checkUserScore($milestone_count, $dailyObjective)
    {
        $userGameSessionsScoreCount = GameSession::where('user_id', auth()->user()->id)->whereDate('created_at', now()->startOfDay())->where('state', 'COMPLETED')->where('correct_count', '>=', $milestone_count)->count();

        if ($userGameSessionsScoreCount > 0) {
            $this->completeDailyObjective($dailyObjective);
        }
    }

    private function checkBoostUsage($objective, $dailyObjective)
    {
        $userBoostUsageForToday = UserBoost::whereDate('created_at', '>=', now()->startOfDay())->where('user_id', auth()->user()->id)->count();
        if ($userBoostUsageForToday >= $objective->milestone_count) {
            $this->completeDailyObjective($dailyObjective);
        }
    }

    private function checkReferralObjective($dailyObjective)
    {
        $referredUser =  Profile::where('referral_code', auth()->user()->username)->whereDate('created_at', '>=', now()->startOfDay())->first(); 
        if(!is_null($referredUser)){
            $this->completeDailyObjective($dailyObjective);
        }
    }

    private function checkCoinEarned($milestone_count, $dailyObjective)
    {
        $userGameSessionsCoinEarned = GameSession::where('user_id', auth()->user()->id)->whereDate('created_at', now()->startOfDay())->where('state', 'COMPLETED')->where('coin_earned', '>=', $milestone_count)->first();

        if (!is_null($userGameSessionsCoinEarned)) {
            $this->completeDailyObjective($dailyObjective);
        }
    }

    private function awardCoins($user, $reward)
    {
        DB::transaction(function () use ($user, $reward) {
            UserCoinTransaction::create([
                'user_id' => $user->id,
                'transaction_type' => 'CREDIT',
                'description' => 'Coins awarded from completing daily objective',
                'value' => $reward,
            ]);

            $currentUserCoin = $user->getUserCoins();
            $newUserCoin = $currentUserCoin + $reward;
            $userCoin = $user->userCoins()->firstOrNew();
            $userCoin->coins_value = $newUserCoin;
            $userCoin->user_id = $user->id;
            $userCoin->save();
        });
    }
}
