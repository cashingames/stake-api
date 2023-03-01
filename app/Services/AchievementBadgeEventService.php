<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserPoint;
use App\Models\User;
use App\Models\AchievementBadge;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;
use App\Models\Wallet;
use App\Enums\AchievementType;

class AchievementBadgeEventService{
    public $user;

    public function __construct($user)
    {
        //
        $this->user = $user;
    }

    public function boostBought($user, $payload){
        // change from id to name
        switch ($payload->name) {

            case AchievementType::BOOST_TIME_FREEZE :
                # code for time freeze...
                $this->badgeStateLogic($user, 24, 5);
                break;

            case AchievementType::BOOST_SKIP :
                # code for skip...
                $this->badgeStateLogic($user, 23, 5);
                break;

            default:
                # code...
                break;
        }
    }

    public function gameBought($user, $payload){
        // change from id to name
        switch ($payload->name) {
            case AchievementType::GAME_LEAST :
                # code for least p bought...
                $this->badgeStateLogic($user, 20, 10);
                break;

            case AchievementType::GAME_DOUBLE :
                # code for double o bought...
                $this->badgeStateLogic($user, 21, 10);
                break;

            case AchievementType::GAME_ULTIMATE :
                # code for ultimate bought...
                $this->badgeStateLogic($user, 22, 10);
                break;

            default:
                # code...
                break;
        }
    }

    public function challengeStarted($user){
        $this->badgeStateLogic($user, 17);
        $this->badgeStateLogic($user, 19, 20);
    }
    public function challengeAccepted($user){
        $this->badgeStateLogic($user, 18);
    }

    public function referralKing($user){
        $this->badgeStateLogic($user, 25, 30);
    }

    public function gamePlayed($user, $payload){

        # FOR GOOD STARTED
        $this->processFirstBadge($user, $payload);


        // get game information
        // game category
        $categoryPlaying = DB::table('categories')->where('id', $payload->category_id)->first();
        if($categoryPlaying->category_id != 0){
            // parent category
            $categoryPlaying = DB::table('categories')->where('id', $categoryPlaying->category_id)->first();
        }

        switch (strtolower($categoryPlaying->name)) {
            case AchievementType::GAME_PLAYED_FOOTBALL :
                # code relating to football...
                $this->numberofGamePlayedLogic($user, $payload, 4);
                $this->scoringWithinARangeLogic($user, $payload, 7);
                $this->scoringWithinARangeLogic($user, $payload, 10);
                $this->numberofGamePlayedLogic($user, $payload, 13);
                $this->scoringWithinARangeLogic($user, $payload, 16);
                break;

            case AchievementType::GAME_PLAYED_MUSIC :
                # code relating to music...
                $this->numberofGamePlayedLogic($user, $payload, 3);
                $this->scoringWithinARangeLogic($user, $payload, 6);
                $this->scoringWithinARangeLogic($user, $payload, 9);
                $this->numberofGamePlayedLogic($user, $payload, 12);
                $this->scoringWithinARangeLogic($user, $payload, 15);
                break;

            case AchievementType::GAME_PLAYED_GENERAL :
                # code relating to general...
                $this->numberofGamePlayedLogic($user, $payload, 2);
                $this->scoringWithinARangeLogic($user, $payload, 5);
                $this->scoringWithinARangeLogic($user, $payload, 8);
                $this->numberofGamePlayedLogic($user, $payload, 11);
                $this->scoringWithinARangeLogic($user, $payload, 14);
                break;

            default:
                # code...
                break;
        }

        # FOR SCHOLAR

    }

    # generic functions

    public function getSingleAchievement($achievement_id){
        $payload = (AchievementBadge::where('id', $achievement_id)->get());

        if(count($payload) != 0){

            return $payload[0];
        }else{
            return null;
        }
    }

    public function getSingleBadge($user_id, $achievement_badge_id){
        $payload = (DB::table('user_achievement_badges')->where('user_id', $user_id)->where('achievement_badge_id', $achievement_badge_id)->get());

        if(count($payload) != 0){

            return $payload[0];
        }else{
            return null;
        }
    }

    public function endSingleBadge($curGS, $count, $uid, $aid, $countAppend = 1){
        if(is_null($curGS)){
            // create and end

            $user = User::where('id', $uid)->first();
            $achievement = AchievementBadge::where('id', $aid)->first();

            $user->userAchievementBadges()->attach($achievement, [
                'count' => $count,
                'is_claimed' => 1,
                'is_rewarded' => 0,
            ]);


            // $badge = new UserAchievementBadge;
            // $badge->user_id = $uid;
            // $badge->achievement_badge_id = $aid;
            // $badge->count = $count;
            // $badge->is_claimed = 1;
            // $badge->is_rewarded = 0;

            // $badge->save();
        }else{
            // end
            DB::table('user_achievement_badges')->where('id', $curGS->id)->update(array(
                'count' => intval($curGS->count) + $countAppend,
                'is_claimed' => 1
            ));
        }


        // call reward
        $this->rewardBadge($uid, $aid);
        return null;
    }

    public function appendSingleBadge($curGS, $uid, $aid, $count = 1){
        if(is_null($curGS)){
            // create

            $user = User::where('id', $uid)->first();
            $achievement = AchievementBadge::where('id', $aid)->first();

            $user->userAchievementBadges()->attach($achievement, [
                'count' => $count,
                'is_claimed' => 0,
                'is_rewarded' => 0,
            ]);

            // $badge->
            // $badge = new UserAchievementBadge;
            // $badge->user_id = $uid;
            // $badge->achievement_badge_id = $aid;
            // $badge->count = $count;
            // $badge->is_claimed = 0;
            // $badge->is_rewarded = 0;

            // $badge->save();
        }else{
            // update
            DB::table('user_achievement_badges')->where('id', $curGS->id)->update(array(
                'count' => intval($curGS->count) + $count,
                'is_claimed' => 0
            ));
        }
        return null;
    }

    public function rewardBadge($uid, $aid){
        $badge = $this->getSingleBadge($uid, $aid);

        // has badge being awarded
        if($badge->is_rewarded){
            return null;
        }

        // reward badge
        $achievement = AchievementBadge::where('id', $aid)->first();

        if(is_null($achievement)){
            return null;
        }

        switch ($achievement->reward_type) {
            case AchievementType::REWARD_CASH:
                # code...
                $this->rewardByCash($badge, $achievement->reward);
                break;

            case AchievementType::REWARD_POINT:
                # code...
                $this->rewardByPoint($badge, $achievement->reward);
                break;

            default:
                # code...
                break;
        }
    }

    public function rewardByPoint($badge, $reward){

        // code to reward the point to the user
        DB::transaction(function() use($badge, $reward){
            UserPoint::create([
                'user_id' => $badge->user_id,
                'value' => $reward,
                'description' => "Point rewarded from achievement",
                'point_flow_type' => 'POINTS_ADDED'
            ]);

            DB::table('user_achievement_badges')->where('id', $badge->id)->update(array(
                'is_rewarded' => 1
            ));
        });

    }

    public function rewardByCash($badge, $reward){
        // code to reward the point to the user
        $user = $this->user;

        DB::transaction(function() use ($user, $reward, $badge) {
            WalletTransaction::create([
                'wallet_id' => $user->wallet->id,
                'transaction_type' => 'CREDIT',
                'amount' => $reward,
                'balance' => $reward,
                'description' => 'Point rewarded from achievement',
                'reference' => Str::random(10),
            ]);

            DB::table('wallets')->where('id', $user->wallet->id)->update(array(
                'non_withdrawable_balance' => $user->wallet->non_withdrawable_balance + $reward
            ));


            DB::table('user_achievement_badges')->where('id', $badge->id)->update(array(
                'is_rewarded' => 1
            ));
        });

    }


    # individual achievements
    public function processFirstBadge($user, $payload){
        $gameCat = $user->getNextFreePlan();
        $curGS = $this->getSingleBadge($user->id, 1);

        if(!is_null($curGS) && $curGS->is_claimed){
            return null;
        }

        if(!is_null($gameCat)){
            $this->appendSingleBadge($curGS, $user->id, 1);
        }else{
            $this->endSingleBadge($curGS, 5, $user->id, 1);
        }
    }
    public function numberofGamePlayedLogic($user, $payload, $aid = -1){
        // $aid = 2;
        $curGS = $this->getSingleBadge($user->id, $aid);
        $achievement = $this->getSingleAchievement($aid);

        if(!is_null($curGS)){
            if($curGS->is_claimed){
                return null;
            }
        }

        if(is_null($curGS)){
            // doesn't have a game yet
            $this->appendSingleBadge($curGS, $user->id, $aid);
        }else{
            // check game count logic

            if(($curGS->count + 1) >= ($achievement->milestone * $achievement->milestone_count)){
                $this->endSingleBadge($curGS, 50, $user->id, $aid);
            }else{
                $this->appendSingleBadge($curGS, $user->id, $aid);
            }
        }
    }

    public function scoringWithinARangeLogic($user, $payload, $aid = -1){
        // $aid = 4;
        $curGS = $this->getSingleBadge($user->id, $aid);
        $achievement = $this->getSingleAchievement($aid);

        if(!is_null($curGS)){
            if($curGS->is_claimed){
                return null;
            }
        }

        // check if user scored aboved a threshold
        if($payload->correct_count < $achievement->milestone){
            return null;
        }

        if(is_null($curGS)){
            // doesn't have a game yet
            $this->appendSingleBadge($curGS, $user->id, $aid, $achievement->milestone);
        }else{
            // check game count logic

            if(($curGS->count + $achievement->milestone) >= ($achievement->milestone * $achievement->milestone_count)){
                $this->endSingleBadge($curGS, 70, $user->id, $aid, $achievement->milestone);
            }else{
                $this->appendSingleBadge($curGS, $user->id, $aid, $achievement->milestone);
            }
        }
    }

    public function badgeStateLogic($user, $aid = -1, $end = 10){
        // $aid = 2;
        $curGS = $this->getSingleBadge($user->id, $aid);
        $achievement = $this->getSingleAchievement($aid);

        if(!is_null($curGS)){
            if($curGS->is_claimed){
                return null;
            }
        }

        if(is_null($curGS)){
            // doesn't have a game yet
            $this->appendSingleBadge($curGS, $user->id, $aid);
        }else{
            // check game count logic

            if(($curGS->count + 1) >= ($achievement->milestone * $achievement->milestone_count)){
                $this->endSingleBadge($curGS, $end, $user->id, $aid);
            }else{
                $this->appendSingleBadge($curGS, $user->id, $aid);
            }
        }
    }
}
