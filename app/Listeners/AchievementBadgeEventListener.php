<?php

namespace App\Listeners;

use App\Events\AchievementBadgeEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
// use App\Models\UserAchievementBadge;
use App\Models\UserPoint;
use App\Models\User;
use App\Models\AchievementBadge;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;
use App\Models\Wallet;

use App\Enums\FeatureFlags;
use App\Services\FeatureFlag;

class AchievementBadgeEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */

     public $user;
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AchievementBadgeEvent  $event
     * @return void
     */
    public function handle(AchievementBadgeEvent $event)
    {
        if(FeatureFlag::isEnabled(FeatureFlags::ACHIEVEMENT_BADGES)){
            $AchievementType = $event->AchievementType;

            $user = null;
            if(($AchievementType === "GAME_BOUGHT") || ($AchievementType === "BOOST_BOUGHT") ){
                $user = $event->request;
            }else{
                $user = $event->request->user();
            }
            $this->user = $user;
            // $user = $event->request;
            $payload = $event->payload;

            // switch to determine
            switch ($AchievementType) {
                case 'GAME_PLAYED':
                    # code...
                    $this->gamePlayed($user, $payload);
                    break;

                case 'GAME_BOUGHT':
                    # code...
                    $this->gameBought($user, $payload);
                    break;

                case 'BOOST_BOUGHT':
                    # code...
                    $this->boostBought($user, $payload);
                    break;

                case 'REFERRAL':
                    # code...
                    $this->referralKing($user);
                    break;

                case 'CHALLENGE_STARTED':
                    # code...
                    $this->challengeStarted($user);
                    break;

                case 'CHALLENGE_ACCEPTED':
                    # code...
                    $this->challengeAccepted($user);
                    break;

                default:
                    # code...
                    break;
            }
        }
    }

    public function boostBought($user, $payload){
        // change from id to name
        switch ($payload->name) {

            case 'Time Freeze':
                # code for time freeze...
                $this->BadgeStateLogic($user, 23, 5);
                break;

            case 'Skip':
                # code for skip...
                $this->BadgeStateLogic($user, 22, 5);
                break;

            default:
                # code...
                break;
        }
    }

    public function gameBought($user, $payload){
        // change from id to name
        switch ($payload->name) {
            case 'Least Plan':
                # code for least p bought...
                $this->BadgeStateLogic($user, 19, 10);
                break;

            case 'Double O':
                # code for double o bought...
                $this->BadgeStateLogic($user, 20, 10);
                break;

            case 'The Ultimate':
                # code for ultimate bought...
                $this->BadgeStateLogic($user, 21, 10);
                break;

            default:
                # code...
                break;
        }
    }

    public function challengeStarted($user){
        $this->BadgeStateLogic($user, 16);
        $this->BadgeStateLogic($user, 18, 20);
    }
    public function challengeAccepted($user){
        $this->BadgeStateLogic($user, 17);
    }

    public function referralKing($user){
        $this->BadgeStateLogic($user, 24, 30);
    }

    public function gamePlayed($user, $payload){

        # FOR GOOD STARTED
        $this->GoodStarted($user, $payload);


        // get game information
        // game category
        $categoryPlaying = DB::table('categories')->where('id', $payload->category_id)->first();
        if($categoryPlaying->category_id != 0){
            // parent category
            $categoryPlaying = DB::table('categories')->where('id', $categoryPlaying->category_id)->first();
        }

        switch (strtolower($categoryPlaying->name)) {
            case 'football':
                # code relating to football...
                $this->NumberofGamePlayedLogic($user, $payload, 4);
                $this->ScoringWithinARangeLogic($user, $payload, 6);
                $this->ScoringWithinARangeLogic($user, $payload, 9);
                $this->NumberofGamePlayedLogic($user, $payload, 12);
                $this->ScoringWithinARangeLogic($user, $payload, 15);
                break;

            case 'music':
                # code relating to music...
                $this->NumberofGamePlayedLogic($user, $payload, 3);
                $this->ScoringWithinARangeLogic($user, $payload, 5);
                $this->ScoringWithinARangeLogic($user, $payload, 8);
                $this->NumberofGamePlayedLogic($user, $payload, 11);
                $this->ScoringWithinARangeLogic($user, $payload, 14);
                break;

            case 'general':
                # code relating to general...
                $this->NumberofGamePlayedLogic($user, $payload, 2);
                $this->ScoringWithinARangeLogic($user, $payload, 4);
                $this->ScoringWithinARangeLogic($user, $payload, 7);
                $this->NumberofGamePlayedLogic($user, $payload, 10);
                $this->ScoringWithinARangeLogic($user, $payload, 13);
                break;

            default:
                # code...
                break;
        }

        # FOR SCHOLAR

    }

    # generic functions

    public function getSingleAchievement($achievement_id){
        $payload = (DB::table('achievement_badges')->where('id', $achievement_id)->get());

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


            Log::info($achievement);
            Log::info('aid'.$aid);


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
        $achievement = DB::table('achievement_badges')->where('id', $aid)->first();

        if(is_null($achievement)){
            return null;
        }

        switch ($achievement->reward_type) {
            case 'CASH':
                # code...
                $this->rewardByCash($badge, $achievement->reward);
                break;

            case 'POINTS':
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
        UserPoint::create([
            'user_id' => $badge->user_id,
            'value' => $reward,
            'description' => "Point rewarded from achievement",
            'point_flow_type' => 'POINTS_ADDED'
        ]);

        DB::table('user_achievement_badges')->where('id', $badge->id)->update(array(
            'is_rewarded' => 1
        ));
    }

    public function rewardByCash($badge, $reward){
        // code to reward the point to the user
        $wallet_id = $this->user->wallet->id;

        WalletTransaction::create([
            'wallet_id' => $wallet_id,
            'transaction_type' => 'CREDIT',
            'amount' => $reward,
            'balance' => $reward,
            'description' => 'Point rewarded from achievement',
            'reference' => Str::random(10),
        ]);

        DB::table('wallets')->where('id', $wallet_id)->update(array(
            'non_withdrawable_balance' => $this->user->wallet->non_withdrawable_balance + $reward
        ));


        DB::table('user_achievement_badges')->where('id', $badge->id)->update(array(
            'is_rewarded' => 1
        ));
    }


    # individual achievements
    public function GoodStarted($user, $payload){
        $gameCat = $user->getNextFreePlan();
        $curGS = $this->getSingleBadge($user->id, 1);

        if(!is_null($curGS)){
            if($curGS->is_claimed){
                return null;
            }
        }

        if(!is_null($gameCat)){
            $this->appendSingleBadge($curGS, $user->id, 1);
        }else{
            $this->endSingleBadge($curGS, 5, $user->id, 1);
        }
    }
    public function NumberofGamePlayedLogic($user, $payload, $aid = -1){
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

            if($curGS->count >= ($achievement->milestone * $achievement->milestone_count)){
                $this->endSingleBadge($curGS, 50, $user->id, $aid);
            }else{
                $this->appendSingleBadge($curGS, $user->id, $aid);
            }
        }
    }

    public function ScoringWithinARangeLogic($user, $payload, $aid = -1){
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

            if($curGS->count >= ($achievement->milestone * $achievement->milestone_count)){
                $this->endSingleBadge($curGS, 70, $user->id, $aid, $achievement->milestone);
            }else{
                $this->appendSingleBadge($curGS, $user->id, $aid, $achievement->milestone);
            }
        }
    }

    public function BadgeStateLogic($user, $aid = -1, $end = 10){
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

            if($curGS->count >= ($achievement->milestone * $achievement->milestone_count)){
                $this->endSingleBadge($curGS, $end, $user->id, $aid);
            }else{
                $this->appendSingleBadge($curGS, $user->id, $aid);
            }
        }
    }

}
