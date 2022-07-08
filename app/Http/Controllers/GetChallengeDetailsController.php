<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\ChallengeDetailsResponse;
use App\Models\Challenge;
use Illuminate\Http\Request;

class GetChallengeDetailsController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $challengeDetails = Challenge::challengeDetails($request->challengeId);
        
        return (new ChallengeDetailsResponse())->transform($challengeDetails);
    }
}
