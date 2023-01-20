<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\ContestDetailsResponse;
use App\Http\ResponseHelpers\GetContestDetailsResponse;
use App\Models\Contest;
use App\Services\ContestService;
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
    
        $contestService = new ContestService;

        $contests = $contestService->getContests();

        return (new GetContestDetailsResponse())->massTransform($contests);
    }
}
