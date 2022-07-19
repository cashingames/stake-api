<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\UserChallengeResponse;

class GetUserChallengeController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke()
    {
        $challenges = $this->user->userChallenges();
        return (new UserChallengeResponse())->transform($challenges);
    }
}
