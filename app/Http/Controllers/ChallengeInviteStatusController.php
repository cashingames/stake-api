<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\ChallengeDetailsResponse;
use App\Mail\RespondToChallengeInvite;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ChallengeInviteStatusController extends BaseController
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        if (!$request->has('challenge_id')) {
            return $this->sendError('Challenge info not found', 'Challenge info not found');
        }

        if (!$request->has('opponent')) {
            return $this->sendError('User not found', 'User not found');
        }

       if(! Challenge::hasOpponentData($request->challenge_id, $request->opponentId)){
            return $this->sendError('Failed to accept invite. Invite account doesn not match', 'Failed to accept invite. Invite account doesn not match');
       }

        $status = $request->status == 1 ? "ACCEPTED" : "DECLINED";
        $updatedChallenge = Challenge::changeChallengeStatus($status, $request->challenge_id);

        $player = User::find($updatedChallenge->user_id);

        Log::info("Challenge with ID: " . $request->challenge_id . "  has been " . $status . " by " . $this->user->username);

        Mail::send(new RespondToChallengeInvite($status, $player, $request->challenge_id));

        Log::info("Challenge $request->challenge_id  response has been sent from " . $this->user->username);

        return $this->sendResponse('Response email sent', 'Response email sent');
    }
}
