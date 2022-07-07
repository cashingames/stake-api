<?php

namespace App\Http\Controllers;

use App\Http\ResponseHelpers\ChallengeInviteResponse;
use App\Mail\RespondToChallengeInvite;
use App\Models\Challenge;
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
        $status = $request->status == 1 ? "ACCEPTED" : "DECLINED";
        $getChallengeInfo =  Challenge::find($request->challenge_id);
        $getChallengeInfo->status =  $status;
        $getChallengeInfo->save();

        Log::info("Challenge with ID: " .$request->challenge_id. "  has been " . $status. " by ". $this->user->username);

        Mail::send(new RespondToChallengeInvite($status, $getChallengeInfo->user_id, $getChallengeInfo->id ));

        Log::info("Challenge $request->challenge_id  response has been sent from ". $this->user->username );

        return (new ChallengeInviteResponse())->transform($getChallengeInfo);
    }
}
