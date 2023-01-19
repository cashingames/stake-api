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
    public function __invoke()
    {   
        $contests = Contest::select(
            'id',
            'name',
            'description',
            'display_name as displayName',
            'start_date as startDate',
            'end_date as endDate',
            'contest_type as contestType',
            'entry_mode as entryMode'
        )->get();

        return $this->sendResponse($contests, 'contests');
    }
}
