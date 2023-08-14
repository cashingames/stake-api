<?php

namespace App\Repositories\Cashingames;

use App\Models\Cashdrop;
use Illuminate\Support\Facades\DB;

class CashdropRepository
{

    // public function getCashdropWinnerById(int $cashdropId): mixed
    // {
    //     return Boost::findOrFail($boostId);
    // }

    public function getCashdropData(): mixed
    {
        $cashdropRounds = DB::select(
            'SELECT cashdrops.name as cashdropName, cashdrops.id 
            as cashdropId, cashdrop_rounds.pooled_amount 
            as pooledAmount from cashdrops 
            inner join cashdrop_rounds on cashdrops.id = cashdrop_rounds.cashdrop_id 
            where cashdrop_rounds.dropped_at is null
            order by cashdrops.lower_pool_limit DESC',
        );

        $cashdropWinners = DB::select(
            'SELECT profiles.first_name, profiles.last_name, profiles.avatar, cashdrops.icon FROM profiles 
            LEFT JOIN cashdrop_users on cashdrop_users.user_id = profiles.user_id
            LEFT JOIN cashdrop_rounds on cashdrop_users.cashdrop_round_id = cashdrop_rounds.id
            LEFT JOIN cashdrops on cashdrops.id = cashdrop_rounds.cashdrop_id
            WHERE cashdrop_users.winner is true'
        );

        return [

            'cashdrops' =>  $cashdropRounds,
            'cashdropWinners' =>  $cashdropWinners

        ];
    }
}
