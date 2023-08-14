<?php

namespace App\Repositories\Cashingames;

use App\Models\Cashdrop;
use Illuminate\Support\Facades\DB;

class CashdropRepository
{

    public function getCashdropWinnerById(int $cashdropId): mixed
    {
        return Boost::findOrFail($boostId);
    }

    public function getCashdropData(): mixed
    {
        $data = DB::select(
            'SELECT cashdrops.name as cashdropName, cashdrops.id 
            as cashdropId, cashdrop_rounds.pooled_amount 
            as pooledAmount from cashdrops 
            inner join cashdrop_rounds on cashdrops.id = cashdrop_rounds.cashdrop_id 
            order by cashdrop_rounds.lower_pool_limit where cashdrop_rounds.dropped_at is null',
        );
        return $data;
    }
}
