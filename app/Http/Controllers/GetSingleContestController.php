<?php

namespace App\Http\Controllers;

use App\Models\Contest;
use Illuminate\Http\Request;

class GetSingleContestController extends BaseController
{
    public function __invoke(Request $request)
    {

        $contest = Contest::select(
            'id',
            'name',
            'description',
            'display_name as displayName',
            'start_date as startDate',
            'end_date as endDate',
            'contest_type as contestType',
            'entry_mode as entryMode'
        )->where('id', $request->id)->first();

        if (is_null($contest)) {
            return $this->sendError('Invalid contest', 'Invalid contest');
        }

        return $this->sendResponse($contest, 'contest');
    }
}
