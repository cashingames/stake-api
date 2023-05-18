<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Challenge;
use Illuminate\Support\Str;
use App\Mail\ChallengeInvite;
use App\Models\ChallengeStaking;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Actions\SendPushNotification;
use App\Models\Category;
use App\Notifications\ChallengeReceivedNotification;

class ChallengeGameService
{

    /**
     * Create a new challenge and send invite notification to the opponent(s)
     *
     * @param App\User $creator
     *
     * @param mixed $opponent
     * $opponent can either be a single id, or an array of ids. If an array of ids is supplied, notification will be sent to all opponents involved
     *
     * @param int $categoryId
     */
    public function createChallenge(User $creator, $opponents, $categoryId)
    {

        if (Category::find($categoryId)->questions->count() < 10) {
            return null;
        }

        if (is_numeric($opponents)) {
            $opponents = [$opponents];
        }
        $createdChallenges = [];

        foreach ($opponents as $opponentId) {
            $challenge = Challenge::create([
                'status' => 'PENDING',
                'user_id' => $creator->id,
                'category_id' => $categoryId,
                'opponent_id' => $opponentId
            ]);

            $opponent = User::find($opponentId);

            //database notification
            $opponent->notify(new ChallengeReceivedNotification($challenge, $creator));

            //email notification
            Mail::to($opponent->email)->send(new ChallengeInvite($opponent, $challenge));

            //push notification
            dispatch(function () use ($creator, $opponent, $challenge) {
                $pushAction = new SendPushNotification();
                $pushAction->sendChallengeInviteNotification($creator, $opponent, $challenge);
            });

            Log::info("Challenge id : $challenge->id  invite from " . $creator->username . " sent to {$opponent->username}");
            array_push($createdChallenges, $challenge);
        }
        return $createdChallenges;
    }

    public function creditStakeWinner(Challenge $challenge)
    {
        if ($challenge->stakings()->count() < 1) {
            // this challenge is not a staking one
            return false;
        }
        if ($challenge->challengeGameSessions()->where('state', 'COMPLETED')->count() < 2) {
            // both users have not completed this game
            return false;
        }

        $gameSessions = $challenge->challengeGameSessions()->orderBy('correct_count', 'desc')->limit(2)->get();

        if ($gameSessions[0]->correct_count == $gameSessions[1]->correct_count) {
            // game ended in a draw, credit both participants back
            $challengeStakings = ChallengeStaking::where('challenge_id', $challenge->id)->get();

            foreach ($challengeStakings as $key => $cS) {
                $amountWon = $cS->staking->amount_staked;

                $cS->staking()->update(['amount_won' => $amountWon]);

                WalletTransaction::create([
                    'wallet_id' => User::find($cS->user_id)->wallet->id,
                    'transaction_type' => 'CREDIT',
                    'amount' => $amountWon,
                    'balance' => User::find($cS->user_id)->wallet->withdrawable,
                    'description' => 'Staking winning of ' . $amountWon . ' on challenge',
                    'reference' => Str::random(10),
                    'viable_date' => Carbon::now()->addHours(config('trivia.staking.hours_before_withdrawal'))
                ]);
            }
            return true;
        }

        $winningUser = User::find($gameSessions[0]->user_id);
        $challengeStaking = ChallengeStaking::where('user_id', $gameSessions[0]->user_id)
            ->where('challenge_id', $challenge->id)
            ->first();
        $amountWon = $challengeStaking->staking()->first()->amount_staked * 2;

        WalletTransaction::create([
            'wallet_id' => $winningUser->wallet->id,
            'transaction_type' => 'CREDIT',
            'amount' => $amountWon,
            'balance' => $winningUser->wallet->withdrawable,
            'description' => 'Staking winning of ' . $amountWon . ' on challenge',
            'reference' => Str::random(10),
            'viable_date' => Carbon::now()->addHours(config('trivia.staking.hours_before_withdrawal'))
        ]);
        $challengeStaking->staking()->update(['amount_won' => $amountWon]);

        return $challenge;
    }
}
