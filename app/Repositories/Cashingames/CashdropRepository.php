<?php

namespace App\Repositories\Cashingames;

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
            as cashdropId, cashdrop_rounds.pooled_amount 
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
}
