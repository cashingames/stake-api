<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\ContestDetailsResponse;
use App\Models\Contest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetContestDetailsController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $contests = Contest::all();

        // $query = 'SELECT c.id, c.name, c.description, c.display_name as displayName,
        // c.start_date as startDate, c.end_date as endDate , c.contest_type as contestType,
        // c.entry_mode as entryMode, pp.rank_from as positionFrom, pp.rank_to as positionTo, pp.prize, pp.prize_type as prizeType
        // FROM contests c
        // INNER JOIN contest_prize_pools pp ON pp.contest_id = c.id 
        // GROUP BY pp.id
        // ';

        // $contests = DB::select($query);

        return (new ContestDetailsResponse())->transform($contests);
        //return $this->sendResponse($contests, 'contests');
    }
}
