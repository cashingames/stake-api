<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FcmPushSubscription;

class RegisterPushDeviceTokenController extends BaseController
{
    public function __invoke(Request $request)
    {
        $request->validate($request, [
            'device_token'    => 'required',
            'topic'   => 'string',

        ]);
        $user = $this->user;

        $token = $request->device_token;
        $topic = $request->topic;

        FcmPushSubscription::query()->updateOrCreate([
            'user_id' => $user->id,
            'topic' => $topic
        ], [
            'device_token' => $token,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Push Subscription stored successfully"
        ]);
    }
}
