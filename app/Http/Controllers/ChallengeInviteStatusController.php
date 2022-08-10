<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Challenge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Actions\SendPushNotification;
use App\Mail\RespondToChallengeInvite;
use App\Http\ResponseHelpers\ChallengeDetailsResponse;

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
        $status = $request->status == 1 ? "ACCEPTED" : "DECLINED";
        $updatedChallenge = Challenge::changeChallengeStatus($status, $request->challenge_id);

        $player = User::find($updatedChallenge->user_id);

        Log::info("Challenge with ID: " . $request->challenge_id . "  has been " . $status . " by " . $this->user->username);

        Mail::send(new RespondToChallengeInvite($status, $player, $request->challenge_id));

        if (env('PUSH_ENABLED')){
            $pushAction = new SendPushNotification();
            $pushAction->sendChallengeStatusChangeNotification($this->user, $player, $updatedChallenge, $status);
        }

        Log::info("Challenge $request->challenge_id  response has been sent from " . $this->user->username);

        return $this->sendResponse('Response email sent', 'Response email sent');
    }
}
