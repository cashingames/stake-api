<?php

namespace App\Repositories\Cashingames;

use App\Enums\WalletBalanceType;
use App\Enums\WalletTransactionAction;
use App\Enums\WalletTransactionType;
use App\Jobs\SendCashdropDroppedNotification;
use App\Models\CashdropRound;
use App\Models\Cashdrop;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashdropRepository
{

    public function createCashdropRound(Cashdrop $cashdrop): CashdropRound
    {
        return CashdropRound::create([
            'cashdrop_id' => $cashdrop->id,
            'percentage_stake' => $cashdrop->percentage_stake,
            'dropped_at' => null
        ]);
    }

    public function getRunningCashdrops(): array
    {
        return DB::select(

            'SELECT
                cashdrops.name as cashdropName, cashdrops.id as cashdropId, cashdrops.icon as cashdropIcon,
                cashdrop_rounds.pooled_amount as pooledAmount, cashdrops.background_colour as backgroundColor
            FROM cashdrops
            LEFT JOIN cashdrop_rounds on cashdrops.id = cashdrop_rounds.cashdrop_id
            WHERE cashdrop_rounds.dropped_at IS NULL
            ORDER BY cashdrops.lower_pool_limit DESC;'
        );
    }

    public function getCashdropWinners(): array
    {
        return DB::select(
            'SELECT
                users.username, profiles.avatar,
                cashdrops.icon , cashdrops.name as cashdropsName,
                cashdrops.background_colour as backgroundColor, cashdrop_rounds.id as cashdropRoundId,
                cashdrop_rounds.dropped_at as winningDate,
               ROUND(cashdrop_rounds.pooled_amount * cashdrop_rounds.percentage_stake * 10, 2) as pooledAmount FROM profiles
            LEFT JOIN users on users.id = profiles.user_id
            LEFT JOIN cashdrop_users on cashdrop_users.user_id = profiles.user_id
            LEFT JOIN cashdrop_rounds on cashdrop_users.cashdrop_round_id = cashdrop_rounds.id
            LEFT JOIN cashdrops on cashdrops.id = cashdrop_rounds.cashdrop_id
            WHERE cashdrop_users.winner is true
            ORDER BY cashdrop_users.updated_at DESC
            LIMIT 20;'
        );
    }

    public function getActiveCashdrops()
    {
        return CashdropRound::whereNull('dropped_at')->get();
    }

    public function getUserCashdrops($roundId, $userId)
    {
        return  DB::table('cashdrop_users')->where('cashdrop_round_id', $roundId)
            ->where('user_id', $userId)->first();
    }
    public function updateCashdropRound($data)
    {
        CashdropRound::where('id', $data['cashdrop_round_id'])
            ->update(['pooled_amount' => $data['pooled_amount']]);
    }

    public function updateCashdropUser($conditions, $data)
    {
        DB::table('cashdrop_users')->updateOrInsert($conditions, $data);
    }

    public function updateUserCashdropRound(
        int $userId,
        float $amount,
        object $round
    ) {
        $cashdropRoundData = [
            'cashdrop_round_id' => $round->id,
            'pooled_amount' => $round->pooled_amount + $amount * $round->percentage_stake,
        ];
        $cashdropUsersconditions = [
            'cashdrop_round_id' => $round->id,
            'user_id' => $userId
        ];
        $newAmount = 0;
        $existingCashdropUserRecord = $this->getUserCashdrops($round->id, $userId);

        if (!is_null($existingCashdropUserRecord)) {
            $newAmount = $existingCashdropUserRecord->amount + $amount * $round->percentage_stake;
        }

        $cashdropUsersData = [
            'amount' => $newAmount,
            'winner' => false
        ];

        $this->updateCashdropRound($cashdropRoundData);
        $this->updateCashdropUser($cashdropUsersconditions, $cashdropUsersData);
    }

    public function creditWinner(WalletRepository $walletRepository, $cashdropRound)
    {
        $randomUserCashdrop = DB::table('cashdrop_users')
            ->where('cashdrop_round_id', $cashdropRound->id)
            ->inRandomOrder()->first();

        $winner = User::find($randomUserCashdrop->user_id);
        $walletRepository->addTransaction(
            new WalletTransactionDto(
                $winner->id,
                $cashdropRound->pooled_amount,
                'Cashdrop Lucky Winning',
                WalletBalanceType::WinningsBalance,
                WalletTransactionType::Credit,
                WalletTransactionAction::WinningsCredited,
            )
        );
        DB::update(
            'UPDATE cashdrop_users SET winner = ?, updated_at = ?
             WHERE id = ? ',
            [
                true,
                now(),
                $randomUserCashdrop->id,
            ]
        );
        DB::update(
            'UPDATE cashdrop_rounds SET dropped_at = ?
             WHERE id = ? ',
            [
                now(),
                $cashdropRound->id,
            ]
        );
        Log::info('cash dropped on: ' . $winner->username);
        SendCashdropDroppedNotification::dispatch(
            $winner->username,
            $cashdropRound
        );


        return $cashdropRound->cashdrop;
    }
}
