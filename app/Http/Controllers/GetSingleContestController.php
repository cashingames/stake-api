<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\GetContestDetailsResponse;
use App\Models\Contest;
use App\Services\ContestService;
use Illuminate\Http\Request;

class GetSingleContestController extends BaseController
{
    public function __invoke(Request $request, ContestService   $contestService)
    {
        $contest = $contestService->getSingleContest($request->id);

        if (is_null($contest)) {
            return $this->sendError('Invalid contest', 'Invalid contest');
        }

        return (new GetContestDetailsResponse())->singleTransform($contest);
        
    }
}
