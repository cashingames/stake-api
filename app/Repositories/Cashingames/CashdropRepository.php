<?php

namespace App\Repositories\Cashingames;

use App\Models\CashdropRound;
use Illuminate\Support\Facades\DB;

class CashdropRepository
{

    public function getCashdrops(): array
    {
        return [

            'cashdropRounds' =>  $this->getRunningCashdrops(),
            'cashdropWinners' =>  $this->getCashdropWinners()

        ];
    }

    public function getRunningCashdrops(): array
    {
        return DB::select(
            'SELECT cashdrops.name as cashdropName, cashdrops.id 
            as cashdropId, cashdrops.icon as cashdropIcon, cashdrop_rounds.pooled_amount 
            as pooledAmount, cashdrops.background_colour as backgroundColor from cashdrops 
            left join cashdrop_rounds on cashdrops.id = cashdrop_rounds.cashdrop_id 
            where cashdrop_rounds.dropped_at is null
            order by cashdrops.lower_pool_limit DESC',
        );
    }

    public function getCashdropWinners(): array
    {
        return DB::select(
            'SELECT profiles.first_name, profiles.last_name, profiles.avatar, cashdrops.icon , cashdrops.name as cashdropsName,
            cashdrops.background_colour as backgroundColor, cashdrop_rounds.id as cashdropRoundId, cashdrop_rounds.pooled_amount as pooledAmount FROM profiles 
            LEFT JOIN cashdrop_users on cashdrop_users.user_id = profiles.user_id
            LEFT JOIN cashdrop_rounds on cashdrop_users.cashdrop_round_id = cashdrop_rounds.id
            LEFT JOIN cashdrops on cashdrops.id = cashdrop_rounds.cashdrop_id
            WHERE cashdrop_users.winner is true'
        );
    }

    public function getActiveCashdrops()
    {
        return CashdropRound::whereNull('dropped_at')->get();
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
        $cashdropUsersData = [
            'amount' => DB::raw('amount + ' . $amount * $round->percentage_stake),
            'winner' => false
        ];

        $this->updateCashdropRound($cashdropRoundData);
        $this->updateCashdropUser($cashdropUsersconditions, $cashdropUsersData);
    }
}
